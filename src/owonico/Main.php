<?php

namespace owonico;

/*---------------------------------
basic core uses
---------------------------------*/

use owonico\provider\MySQL;
use owonico\command\{StatsCommand, RegionCommand, StaffchatCommand, CoreCommand, HubCommand, PingCommand, InfoCommand, NickCommand, ReportCommand, UnnickCommand};
use owonico\listeners\{LobbyListener, PlayerListener, ServerListener};
use owonico\manager\RankManager;
use owonico\task\{BroadcastTask, DayTask, QueryTask};
use owonico\skin\PersonaSkinAdapter;
use owonico\skin\libs\traits\RemovePluginDataDirTrait;

/*---------------------------------
basic libs uses
---------------------------------*/

use maipian\await\generator\Await;
use maipian\await\std\AwaitStd;
use maipian\scoreboard\Scoreboard;
use maipian\webhook\Webhook;
use maipian\webhook\Message;
use maipian\webhook\Embed;
use maipian\packet\interceptor\IPacketInterceptor;
use maipian\packet\SimplePacketHandler;
use owonico\egghunt\EggHuntManager;
use owonico\skin\ClothesManager;
use owonico\entity\EggHuntEntity;
use owonico\manager\TagManager;
/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\network\mcpe\convert\SkinAdapter;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\event\EventPriority;
use pocketmine\block\Block;
use pocketmine\block\tile\Spawnable;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\world\World;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackInfoEntry;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\world\BlockTransaction;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;

/*---------------------------------
basic php uses
---------------------------------*/

use function count;

class Main extends PluginBase implements Listener{
    use RemovePluginDataDirTrait;

    public static Config $settings;

    private ?SkinAdapter $originalAdaptor = null;

    public static Main $instance;

    public AwaitStd $std;
    
    public $cps;

    public $mutedPlayers = [];

    public array $chatcooldown;
    public array $encryptionKeys;
    
    public static array $bowCooldown;
    public static array $playerArena;
    public static array $pearlCooldown;
    public static array $playerOS;

    //public static array $cps;
    public static array $scoreboardEnabled;
    public static array $bffaplacedblock;
    public static array $bffablocktimer;

    private IPacketInterceptor $handler;

    private \Closure $handleBlockActorData;

    private \Closure $handleUpdateBlock;
    private ?Player $lastPlayer = null;

    private array $oldBlocksFullId = [];

    private array $oldTilesSerializedCompound = [];

    public DataConnector $database;
    public static array $userelo = [];
    public static array $userdata = [];
    public static array $usersettings = [];

    public static Config $cratesConfig;
    public static array $crateSetup = [];

    public static function getInstance(): Main
    {
        return self::$instance;
    }
    
    public function onLoad(): void{
        self::$instance = $this;
        MySQL::init();
    }

    public function onEnable(): void
    {

        if ($this->getConfig()->get("maintenance") === true){
            $this->getServer()->getNetwork()->setName(Variables::MotdMaintance);
        }

        if ($this->getConfig()->get("maintenance") === false){
            $this->getServer()->getNetwork()->setName(Variables::Motd);
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        DefaultPermissions::registerPermission(new Permission("mute.command"));
        DefaultPermissions::registerPermission(new Permission("unmute.command"));

        $w = $this->getConfig()->get("webhook");

        $r = $this->getConfig()->get("region");


        $webhook = new Webhook("{$w}");
        $embed = new Embed();
        $embed->setTitle("Server Status - {$r}");
        $embed->setDescription("LithiumNetwork is now online");
        $embed->setFooter("Made By Kakashi W");
        $embed->setTimestamp(new \DateTime("now"));
        $embed->setColor(0x4EFA03);
        $message = new Message();
        $message->addEmbed($embed);
        $webhook->send($message);

        $this->saveResource("config.yml");
        self::$settings = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        $this->std = AwaitStd::init($this);

        RankManager::init();
        EggHuntManager::init();
        ClothesManager::init();
        TagManager::init();

        $this->initDatabase();
        $this->initTask();
        $this->initCommand();
        $this->initFFA();
        $this->initListener();
        $this->initDataFolders();

        $this->originalAdaptor = SkinAdapterSingleton::get();
        SkinAdapterSingleton::set(new PersonaSkinAdapter());

        EntityFactory::getInstance()->register(EggHuntEntity::class, function (World $world, CompoundTag $nbt): EggHuntEntity{
            return new EggHuntEntity(EntityDataHelper::parseLocation($nbt, $world), EggHuntEntity::parseSkinNBT($nbt));
        }, ["egghuntentity"]);
        
        $this->getLogger()->info("Suscees detect extends libs !");

        foreach($this->getServer()->getResourcePackManager()->getResourceStack() as $resourcePack){
            $uuid = $resourcePack->getPackId();
            if($this->getConfig()->getNested("resource-packs.{$uuid}", "") !== ""){
                $encryptionKey = $this->getConfig()->getNested("resource-packs.{$uuid}");
                $this->encryptionKeys[$uuid] = $encryptionKey;
                $this->getLogger()->debug("Loaded encryption key for resource pack $uuid");
            }
        }
        $this->getServer()->getPluginManager()->registerEvent(DataPacketSendEvent::class, function(DataPacketSendEvent $event) : void{
            $packets = $event->getPackets();
            foreach($packets as $packet){
                if($packet instanceof ResourcePacksInfoPacket){
                    foreach($packet->resourcePackEntries as $index => $entry){
                        if(isset($this->encryptionKeys[$entry->getPackId()])){
                            $contentId = $this->encryptionKeys[$entry->getPackId()];
                            $packet->resourcePackEntries[$index] = new ResourcePackInfoEntry($entry->getPackId(), $entry->getVersion(), $entry->getSizeBytes(), $contentId, $entry->getSubPackName(), $entry->getPackId(), $entry->hasScripts(), $entry->isRtxCapable());
                        }
                    }
                }
            }
        }, EventPriority::HIGHEST, $this);

        $this->handler = SimplePacketHandler::createInterceptor($this, EventPriority::HIGHEST);

        $this->handleUpdateBlock = function(UpdateBlockPacket $packet, NetworkSession $target): bool{
            if($target->getPlayer() !== $this->lastPlayer){
                return true;
            }
            $blockHash = World::blockHash($packet->blockPosition->getX(), $packet->blockPosition->getY(), $packet->blockPosition->getZ());
            if(RuntimeBlockMapping::getInstance()->fromRuntimeId($packet->blockRuntimeId, RuntimeBlockMapping::getMappingProtocol($target->getProtocolId())) !== ($this->oldBlocksFullId[$blockHash] ?? null)){
                return true;
            }
            unset($this->oldBlocksFullId[$blockHash]);
            if(count($this->oldBlocksFullId) === 0){
                if(count($this->oldTilesSerializedCompound) === 0){
                    $this->lastPlayer = null;
                }
                $this->handler->unregisterOutgoingInterceptor($this->handleUpdateBlock);
            }
            return false;
        };
        $this->handleBlockActorData = function(BlockActorDataPacket $packet, NetworkSession $target): bool{
            if($target->getPlayer() !== $this->lastPlayer){
                return true;
            }
            $blockHash = World::blockHash($packet->blockPosition->getX(), $packet->blockPosition->getY(), $packet->blockPosition->getZ());
            if($packet->nbt !== ($this->oldTilesSerializedCompound[$blockHash] ?? null)){
                return true;
            }
            unset($this->oldTilesSerializedCompound[$blockHash]);
            if(count($this->oldTilesSerializedCompound) === 0){
                if(count($this->oldBlocksFullId) === 0){
                    $this->lastPlayer = null;
                }
                $this->handler->unregisterOutgoingInterceptor($this->handleBlockActorData);
            }
            return false;
        };
        $this->getServer()->getPluginManager()->registerEvent(PlayerInteractEvent::class, function(PlayerInteractEvent $event): void{
            $item = $event->getItem();
            if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK || $item->isNull() || !$item->canBePlaced() || $this->hasOtherEntityInside($event)){
                return;
            }

            $player = $event->getPlayer();
            $this->lastPlayer = $player;
            $clickedBlock = $event->getBlock();
            $replaceBlock = $clickedBlock->getSide($event->getFace());
            $this->oldBlocksFullId = [];
            $this->oldTilesSerializedCompound = [];
            $saveOldBlock = function(Block $block) use ($player): void{
                $pos = $block->getPosition();
                $posIndex = World::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
                $this->oldBlocksFullId[$posIndex] = $block->getFullId();
                $tile = $pos->getWorld()->getTileAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
                if($tile instanceof Spawnable){
                    $this->oldTilesSerializedCompound[$posIndex] = $tile->getSerializedSpawnCompound($player->getNetworkSession()->getProtocolId());
                }
            };
            foreach($clickedBlock->getAllSides() as $block){
                $saveOldBlock($block);
            }
            foreach($replaceBlock->getAllSides() as $block){
                $saveOldBlock($block);
            }
            $this->handler->interceptOutgoing($this->handleUpdateBlock);
            $this->handler->interceptOutgoing($this->handleBlockActorData);
        }, EventPriority::MONITOR, $this);
        $this->getServer()->getPluginManager()->registerEvent(BlockPlaceEvent::class, function(): void{
            $this->oldBlocksFullId = [];
            $this->oldTilesSerializedCompound = [];
            $this->lastPlayer = null;
            $this->handler->unregisterOutgoingInterceptor($this->handleUpdateBlock);
            $this->handler->unregisterOutgoingInterceptor($this->handleBlockActorData);
        }, EventPriority::MONITOR, $this, true);
    }
    
    public static function getPlayerOS(Player $player){
        if (!isset(self::$playerOS[$player->getName()])){
            return "Unknown";
        }
        switch (self::$playerOS[$player->getName()]){
            case 1: {
                return ""; //android
            }
            case 2: {
                return ""; //ios
            }
            case 7: {
                return ""; //windows 10
            }
            case 8: {
                return ""; //windows 11
            }
            case 12: {
                return ""; //switch
            }
        }
        return "Unknown";
    }

    private function hasOtherEntityInside(PlayerInteractEvent $event): bool
    {
        $item = $event->getItem();
        $face = $event->getFace();
        $blockClicked = $event->getBlock();
        $blockReplace = $blockClicked->getSide($event->getFace());
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $clickVector = $event->getTouchVector();

        $hand = $item->getBlock($face);
        // @phpstan-ignore-next-line
        $hand->position($world, $blockReplace->getPosition()->x, $blockReplace->getPosition()->y, $blockReplace->getPosition()->z);

        if($hand->canBePlacedAt($blockClicked, $clickVector, $face, true)) {
            $blockReplace = $blockClicked;
            //TODO: while this mimics the vanilla behaviour with replaceable blocks, we should really pass some other
            //value like NULL and let place() deal with it. This will look like a bug to anyone who doesn't know about
            //the vanilla behaviour.
            $face = Facing::UP;
            // @phpstan-ignore-next-line
            $hand->position($world, $blockReplace->getPosition()->x, $blockReplace->getPosition()->y, $blockReplace->getPosition()->z);
        }

        $tx = new BlockTransaction($world);
        if(!$hand->place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
            return false; // just in case
        }

        // TODO: this is a hack to prevent block placement when another entity is inside, since this caused ghost blocks
        foreach($tx->getBlocks() as [$x, $y, $z, $block]){
            $block->position($world, $x, $y, $z);
            foreach($block->getCollisionBoxes() as $collisionBox){
                if(count($collidingEntities = $world->getCollidingEntities($collisionBox)) > 0){
                    if ($collidingEntities !== [$player]){
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function initDatabase(){

        MySQL::getDatabase()->query("CREATE TABLE IF NOT EXISTS UData(Name VARCHAR(255) NOT NULL PRIMARY KEY, Kills INT NOT NULL, Death INT NOT NULL, Coin INT NOT NULL, Pass INT NOT NULL, Honour INT NOT NULL, Elo INT NOT NULL, Rank VARCHAR(255) NOT NULL);");
        MySQL::getDatabase()->query("CREATE TABLE IF NOT EXISTS USettings(Name VARCHAR(255) NOT NULL PRIMARY KEY, CpsCount TINYINT(1), HitEffect TINYINT(1), AutoSprint TINYINT(1), Scoreboard TINYINT(1), ArenaRespawn TINYINT(1));");
        MySQL::getDatabase()->query("CREATE TABLE IF NOT EXISTS Tags(Name VARCHAR(255) NOT NULL PRIMARY KEY, CurrentTag VARCHAR(255) NOT NULL, OwnedTag VARCHAR(255) NOT NULL)");
        MySQL::getDatabase()->query("CREATE TABLE IF NOT EXISTS Elo(Name VARCHAR(255) NOT NULL PRIMARY KEY, Boxing INT NOT NULL, Bedfight INT NOT NULL, Bridge INT NOT NULL, Battlerush INT NOT NULL, BuildUHC INT NOT NULL, Midfight INT NOT NULL, Fist INT NOT NULL, Sumo INT NOT NULL, Nodebuff INT NOT NULL);");
    }

    public function initTask(){
        $this->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this), 1000);
        $this->getScheduler()->scheduleRepeatingTask(new DayTask($this), 1);
        $this->getScheduler()->scheduleRepeatingTask(new QueryTask($this), 600);

        $this->getLogger()->info("Loaded tasks...");
    }

    public function initDataFolders(){
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "rank");
        @mkdir($this->getDataFolder() . "settings");
        @mkdir($this->getDataFolder() . "stats");
        @mkdir($this->getDataFolder() . "capes");
        @mkdir($this->getDataFolder() . "shop");
        @mkdir($this->getDataFolder() . "owned");
        @mkdir($this->getDataFolder() . "egghunt");

        $this->saveDefaultConfig();

        self::$cratesConfig = new Config($this->getDataFolder() . "Crates.yml", Config::YAML);


        //cape
        foreach (["Red Creeper.png"] as $cape){
            $this->saveResource("capes/" . $cape);
        }

        $this->getLogger()->info("Loaded Data Folders...");
    }

    public function initListener(){
        $this->getServer()->getPluginManager()->registerEvents(new LobbyListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new ServerListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);

        $this->getLogger()->info("Loaded Listeners...");
        $this->getLogger()->info("Loaded Managers...");
    }

    public function initCommand(): void{
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("about"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("clear"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("tell"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("kill"));
       // $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("list"));
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("listperms"));
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("checkperm"));
      //  $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("me"));
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("ban-ip"));
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("unban-ip"));
       // $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("seed"));;
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("title"));
       // $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("time"));
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("timings"));
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("status"));
        $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("transferserver"));
       // $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("save-on"));
      //  $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("save-all"));
       // $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("save-off"));
       // $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("particle"));
      //  $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("pardon"));
        //$this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("difficulty"));
      //  $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("effect"));
       // $this->getServer()->getCommandMap()->unregister($this->getServer()->getCommandMap()->getCommand("enchant"));

        $this->getServer()->getCommandMap()->register("hub", new HubCommand($this));
        $this->getServer()->getCommandMap()->register("core", new CoreCommand($this));
        $this->getServer()->getCommandMap()->register("ping", new PingCommand($this));
        $this->getServer()->getCommandMap()->register("info", new InfoCommand($this));
        $this->getServer()->getCommandMap()->register("report", new ReportCommand($this));
        $this->getServer()->getCommandMap()->register("staffchat", new StaffchatCommand($this));
        $this->getServer()->getCommandMap()->register("stats", new StatsCommand($this));
        //$this->getServer()->getCommandMap()->register("server", new ServerCommand($this));
        $this->getServer()->getCommandMap()->register("region", new RegionCommand($this));

        $this->getServer()->getCommandMap()->register("nick", new NickCommand());
        $this->getServer()->getCommandMap()->register("unnick", new UnnickCommand());

        $this->getLogger()->info("Loaded Commands...");

    }

    public function initFFA(){
        $this->loadWorld(Variables::Nodebuffffa);
        $this->loadWorld(Variables::Comboffa);
        $this->loadWorld(Variables::Fistffa);
        $this->loadWorld(Variables::Sumoffa);
        $this->loadWorld(Variables::Gappleffa);
        $this->loadWorld(Variables::Buildffa);
        $this->loadWorld(Variables::Knockffa);
        $this->loadWorld(Variables::Resistanceffa);
        $this->loadWorld(Variables::Midfightffa);
        $this->loadWorld(Variables::Skywarsffa);
        $this->loadWorld(Variables::Builduhcffa);
        
        $this->getLogger()->info("Loaded Free For All worlds...");
    }

    public function loadWorld(string $world){
        Await::f2c(function () use ($world) {
            $wrmgr = $this->getServer()->getWorldManager();

            $wrmgr->loadWorld($world, true);
            $wrd = $wrmgr->getWorldByName($world);

            yield from $this->std->sleep(15);
        });
    }

    public function createCape($capeName) {
        $path = $this->getDataFolder() . "capes/$capeName.png";
        $img = imagecreatefrompng($path);
        $bytes = "";
        try {
            for ($y = 0; $y < imagesy($img); $y++) {
                for ($x = 0; $x < imagesx($img); $x++) {
                    $rgba = @imagecolorat($img, $x, $y);
                    $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                    $r = ($rgba >> 16) & 0xff;
                    $g = ($rgba >> 8) & 0xff;
                    $b = $rgba & 0xff;
                    $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
                }
            }
            @imagedestroy($img);
        }catch (\Exception $exception){
            $this->getLogger()->info("Broken srgb profile");
        }

        return $bytes;
    }

    public function getAllCapes() {
        $list = array();

        foreach(array_diff(scandir($this->getDataFolder() . "capes/"), ["..", "."]) as $data) {
            $dat = explode(".", $data);

            if($dat[1] == "png") {
                array_push($list, $dat[0]);
            }
        }

        return $list;
    }

    public function addCPS(Player $player): void{
        $time = microtime(true);
        $this->cps[$player->getName()][] = $time;
    }

    public function getCPS(Player $player): int{
        $time = microtime(true);
        return count(array_filter($this->cps[$player->getName()] ?? [], static function(float $t) use ($time):bool{
            return ($time - $t) <= 1;
        }));
    }

    public static function getRuleContent() : string{
        $content = [
            TextFormat::GRAY . "By joining in our server, you have agreed to follow our rules and we have all the rights to give Punishments"
            , ""
            , TextFormat::GRAY . "- Minimum of 10ms debounce time"
            , TextFormat::GRAY . "- If your mouse double clicks"
            , TextFormat::GRAY . "  be sure to use DC Prevent while playing"
            , TextFormat::GRAY . "- No hacking or any unfair advantages"
            , TextFormat::GRAY . "- No macros or firekeys"
            , TextFormat::GRAY . "- No hate Speech (Racism, Death Threats, etc.)"
            , TextFormat::GRAY . "- No using any clients that provide advantages (Toolbox)"
            , TextFormat::GRAY . "- No using 'No Hurt Cam'"
            , TextFormat::GRAY . "- No abusing bugs or glitches"
            , ""
            , TextFormat::GRAY . "If you happen to cheat on other servers"
            , TextFormat::GRAY . "Make sure you restart your pc when logging on to LithiumNetwork"
        ];
        return implode("\n", $content);
    }
    
    public static function getPassesContent() : string{
        $content = [
            TextFormat::GRAY . "Honour Pass"
            , TextFormat::GREEN . "OWNED"
            , ""
            , TextFormat::GRAY . "$honourpassr1"
            , TextFormat::GRAY . "$honourpassr2"
            , TextFormat::GRAY . "$honourpassr3"
            , TextFormat::GRAY . "$honourpassr4"
            , TextFormat::GRAY . "$honourpassr5"
            , TextFormat::GRAY . "Elite Pass"
            , TextFormat::GREEN . "$elitepassr1"
            , TextFormat::GRAY . "$elitepassr2"
            , TextFormat::GRAY . "$elitepassr3"
            , TextFormat::GRAY . "$elitepassr4"
            , TextFormat::GRAY . "$elitepassr5"
        ];
        return implode("\n", $content);
    }

    public static function getWorldCount(string $world): int{
        $world = Server::getInstance()->getWorldManager()->getWorldByName($world);

        if ($world == null){
            return 0;
        }
        if ($world->getPlayers() == null){
            return 0;
        }
        return count($world->getPlayers());
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        switch ($command->getName()){

            case "mute":
                if(!isset($args[0])){
                    $sender->sendMessage(Variables::Prefix . "§cYou must specify the player you want to mute!");
                    return false;
                }
                $player = $this->getServer()->getPlayerExact($args[0]);
                if(!$player){
                    $sender->sendMessage(Variables::Prefix . "§cThe specified player does not exist!");
                    return false;
                }
                if(in_array($args[0], $this->mutedPlayers)){
                    $sender->sendMessage(Variables::Prefix . "§cThis player is already muted!");
                    return false;
                }
                $this->mutedPlayers[] = $args[0];
                $sender->sendMessage(Variables::Prefix . "§aYou have muted " . $args[0] . "§a!");
                break;

            case "unmute":
                if(!isset($args[0])){
                    $sender->sendMessage(Variables::Prefix . "§cYou must specify the player you wish to unmute!");
                    return false;
                }
                $player = $this->getServer()->getPlayerExact($args[0]);
                if(!$player){
                    $sender->sendMessage(Variables::Prefix . "§cThe specified player does not exist!");
                    return false;
                }
                if(!in_array($args[0], $this->mutedPlayers)){
                    $sender->sendMessage(Variables::Prefix . "§cThis player is already unmuted!");
                    return false;
                }
                unset($this->mutedPlayers[array_search($args[0], $this->mutedPlayers)]);
                $sender->sendMessage(Variables::Prefix . "§aYou have unmuted " . $args[0] . "§a!");
                break;
        }
        return true;
    }

    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        if(in_array($player->getName(), $this->mutedPlayers)){
            $player->sendMessage(Variables::Prefix . "§cYou are muted!");
            $event->cancel();
        }
    }
    
    public static function ensureData(Player $player) {
        $database = MySQL::getDatabase();
        $name = $database->real_escape_string($player->getName());


        $utQuery = "SELECT * FROM Tags WHERE Name='$name'";
        $utResult = $database->query($utQuery);

        if($utResult instanceof \mysqli_result) {
            while ($currentRow = $utResult->fetch_assoc()) {
                
                Main::getInstance()->getLogger()->info($currentRow["CurrentTag"] . ", " . $currentRow["OwnedTag"]); //testing usage
                
                TagManager::$currentTag[$player->getName()] = $currentRow["CurrentTag"];
                TagManager::$ownedTags[$player->getName()] = (array)unserialize(base64_decode($currentRow["OwnedTag"]));

            }
            $utResult->free();
        }

        // Retrieve data from UData table
        $udQuery = "SELECT * FROM UData WHERE Name='$name'";
        $udResult = $database->query($udQuery);

        if ($udResult instanceof \mysqli_result) {
            while ($currentRow = $udResult->fetch_assoc()) {
                Main::getInstance()->getLogger()->info($currentRow["Kills"] . ", " . $currentRow["Death"] . ", " . $currentRow["Rank"]);
     
                self::$userdata[$player->getName()] = [
                    "Kill" => $currentRow["Kills"],
                    "Death" => $currentRow["Death"],
                    "Coin" => $currentRow["Coin"],
                    "Pass" => $currentRow["Pass"],
                    "Elo" => $currentRow["Elo"],
                    "Rank" => $currentRow["Rank"],
                    "Honour" => $currentRow["Honour"],
                ];
            }
            $udResult->free();
        }
        
        $ueQuery = "SELECT * FROM Elo WHERE Name='$name'";
        $ueResult = $database->query($ueQuery);

        if ($ueResult instanceof \mysqli_result) {
            while ($currentRow = $ueResult->fetch_assoc()) {
     
                self::$userelo[$player->getName()] = [
                    "Boxing" => $currentRow["Boxing"],
                    "Bedfight" => $currentRow["Bedfight"],
                    "Bridge" => $currentRow["Bridge"],
                    "Battlerush" => $currentRow["Battlerush"],
                    "BuildUHC" => $currentRow["BuildUHC"],
                    "Midfight" => $currentRow["Midfight"],
                    "Fist" => $currentRow["Fist"],
                    "Sumo" => $currentRow["Sumo"],
                    "Nodebuff" => $currentRow["Nodebuff"],
                ];
            }
            $ueResult->free();
        }

        // Retrieve data from USettings table
        $ustQuery = "SELECT * FROM USettings WHERE Name='$name'";
        $ustResult = $database->query($ustQuery);

        if ($ustResult instanceof \mysqli_result) {
            while ($currentRow1 = $ustResult->fetch_assoc()) {
                
                self::$usersettings[$player->getName()] = [
                    "Cps" => $currentRow1["CpsCount"],
                    "AutoSprint" => $currentRow1["AutoSprint"],
                    "Scoreboard" => $currentRow1["Scoreboard"],
                    "HitEffect" => $currentRow1["HitEffect"],
                    "ArenaRespawn" => $currentRow1["ArenaRespawn"],
                ];

            }
            $ustResult->free();
        }
    }


    public function onDisable(): void
    {
        if($this->originalAdaptor !== null){
            SkinAdapterSingleton::set($this->originalAdaptor);
        }

        $w = $this->getConfig()->get("webhook");

        $r = $this->getConfig()->get("region");

        $webhook = new Webhook("{$w}");
        $embed = new Embed();
        $embed->setTitle("Server Status  - {$r}");
        $embed->setDescription("LithiumNetwork is now offline");
        $embed->setFooter("Made by Kakashi");
        $embed->setTimestamp(new \DateTime("now"));
        $embed->setColor(0xFE0C0C);
        $message = new Message();
        $message->addEmbed($embed);
        $webhook->send($message);
    }
}