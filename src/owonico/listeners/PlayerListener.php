<?php

namespace owonico\listeners;

/*---------------------------------
basic core uses
---------------------------------*/

use owonico\{Main, query\LazyRegisterQuery, Variables};
use owonico\manager\{FormManager, PassManager, PlayerManager, RankManager, SettingsManager};
use owonico\task\{Base, BuildFFATask, BowTask, CombatTask, BuildCombatTask, PearlTask, ScoreboardTask};
use owonico\utils\Utils;
use owonico\manager\TagManager;
use owonico\bridgeduel\Duels;

/*---------------------------------
basic libs uses
---------------------------------*/

use maipian\webhook\Embed;
use maipian\webhook\Message;
use maipian\webhook\Webhook;
use maipian\await\generator\Await;
use owonico\egghunt\EggHuntManager;
use owonico\entity\EggHuntEntity;
use owonico\skin\skinStuff\saveSkin;
/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\entity\animation\HurtAnimation;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\entity\Skin;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\plugin\PluginEnableEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\GetServer;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Bow;
use pocketmine\item\EnderPearl;
use pocketmine\item\GoldenApple;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\sound\XpLevelUpSound;
use SpekledFrog\KillStreak\KillStreak;

class PlayerListener implements Listener{

    public $plugin;
    public $server;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        $this->server = Server::getInstance();
    }

    public function onLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();

        LazyRegisterQuery::registerData($player);
        
        Main::ensureData($player);


        Main::$playerArena[$player->getName()] = "Lobby";
        $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
        $player->teleport($location);


        //$rankCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Rank.yml", Config::YAML);
        //if (!$rankCfg->exists($player->getXuid())){
        //    RankManager::setPlayerRank($player, "Player");
        //}

        $player->recalculatePermissions();

        if(isset(PlayerManager::$nickedplayer[$player->getName()])){
            
            RankManager::setPlayerRank($player, "Player");

            foreach(PlayerManager::$nickedPermissions[$player->getName()] as $permission){
                $player->addAttachment(Main::getInstance(), $permission, true);
            }
        } else {
            foreach (RankManager::getPlayerRank($player)->getPermissions() as $permission){
                $player->addAttachment($this->plugin, $permission, true);
            }
        }
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        
        //LazyRegisterQuery::registerData($player);
        
        //Main::ensureData($player);

        $w = Main::getInstance()->getConfig()->get("webhook");

        $r = Main::getInstance()->getConfig()->get("region");


        KillStreak::getInstance()->getProvider()->resetKSPoints($player);


        $event->setJoinMessage("§8[§a+§8] §a" . $player->getDisplayName());

        $player->setGamemode(GameMode::ADVENTURE());

        $extraData = $player->getNetworkSession()->getPlayerInfo()->getExtraData();
        Main::$playerOS[$player->getName()] = $extraData["DeviceOS"];
        
        $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn()->add(0.5, 1.5, 0.5);
        $player->teleport($location);

        $player->sendMessage(Variables::Prefix . "§bLoading your data...");


        //$scoreboard = new Config(Main::getInstance()->getDataFolder() . "settings/Scoreboard.yml", Config::YAML);

        //if (SettingsManager::getScoreboardEnabled($player)){
            //$scoreboard->set($player->getXuid(), true);
            //$scoreboard->save();
         //   SettingsManager::setScoreboardEnabled($player, true);

         //   Main::$scoreboardEnabled[$player->getName()] = true;
        //}

        EggHuntManager::initLogin($player);

        Await::f2c(function () use ($player) {

            $w = Main::getInstance()->getConfig()->get("webhook");

            $r = Main::getInstance()->getConfig()->get("region");

            $player->getHungerManager()->setFood(20);
            $player->getHungerManager()->setEnabled(false);
            $player->setMaxHealth(20);
            $player->setHealth(20);
            $player->getInventory()->setHeldItemIndex(0);
            $player->getXpManager()->setXpLevel(0);
            $player->getXpManager()->setXpProgress(0);
            $player->getEffects()->clear();

            $webhook = new Webhook("{$w}");
            $embed = new Embed();
            $embed->setTitle("Player Status - {$r}");
            $embed->setDescription("[+] {$player->getName()}");
            $embed->setFooter("Made by Kakashi");
            $embed->setTimestamp(new \DateTime("now"));
            $embed->setColor(0x4EFA03);
            $message = new Message();
            $message->addEmbed($embed);
            $webhook->send($message);

            PlayerManager::sendLobbyKit($player);

            $this->plugin->getScheduler()->scheduleRepeatingTask(new Base($this->plugin, $player), 20);
            $this->plugin->getScheduler()->scheduleRepeatingTask(new ScoreboardTask($this->plugin, $player), 5);

            Main::$scoreboardEnabled[$player->getName()] = SettingsManager::getScoreboardEnabled($player);

            $player->sendMessage("\n\n§b Discord§f: https://dsc.gg/LithiumMC/\n§b YouTube§f: @LithiumNetwork\n§b Website§f: lithiummc.fun\n§b Store§f: lithiummc.fun/store\n");
            $player->sendMessage("§l§cThis Server is still under development there will be bugs!");

            $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(5));

            $playercape = new Config($this->plugin->getDataFolder() . "capes/data.yml", Config::YAML);
            if(file_exists($this->plugin->getDataFolder() . "capes/" . $playercape->get($player->getXuid()) . ".png")) {
                $oldSkin = $player->getSkin();
                $capeData = $this->plugin->createCape($playercape->get($player->getXuid()));
                $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());

                $player->setSkin($setCape);
                $player->sendSkin();
            } else {
                $playercape->remove($player->getXuid());
                $playercape->save();
            }

            yield from $this->plugin->std->sleep(20);
        });

        /**
         * HyperiumMC function code
         */

        if ($this->plugin->getConfig()->get("maintenance") === true && !$player->hasPermission("quza.staff")){
            $player->kick("Server Maintenance - {$r}", false);
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();

        if(isset(PlayerManager::$nickedplayer[$player->getName()])){
            RankManager::setPlayerRank($player, PlayerManager::$nickedRank[$player->getName()]);
        }
        
        KillStreak::getInstance()->getProvider()->resetKSPoints($player);

        $w = Main::getInstance()->getConfig()->get("webhook");
        $r = Main::getInstance()->getConfig()->get("region");

        $event->setQuitMessage("§8[§c-§8] §c" . $player->getDisplayName());

        $webhook = new Webhook("{$w}");
        $embed = new Embed();
        $embed->setTitle("Player Status - {$r}");
        $embed->setDescription("[-] {$player->getName()}");
        $embed->setFooter("Made By Kakashi");
        $embed->setTimestamp(new \DateTime("now"));
        $embed->setColor(0xFE0C0C);
        $message = new Message();
        $message->addEmbed($embed);
        $webhook->send($message);

        if (isset(Main::$playerArena[$player->getName()])){
            unset(Main::$playerArena[$player->getName()]);
        }
        if (isset(Main::$pearlCooldown[$player->getName()])){
            unset(Main::$pearlCooldown[$player->getName()]);
        }

        if (isset(PlayerManager::$isCombat[$player->getName()])){
            if (PlayerManager::getCombatOpponent($player) !== ""){
                $opponent = $this->server->getPlayerExact(PlayerManager::getCombatOpponent($player));
                $opponentname = $opponent->getName();
                $playername = $player->getName();
                $opponent->sendMessage("§f{$opponentname}§a killed §f{$playername}!");

                PlayerManager::addPlayerDeath($player);
                PlayerManager::addPlayerKill($opponent);

            }
        }

        EggHuntManager::handleQuit($player);
    }


    public function onDamage(EntityDamageEvent $event){

        if ($event->getCause() == $event::CAUSE_FALL){
            $event->cancel();
        }
    }

    public function onInventoryChange(InventoryTransactionEvent $event){
        $translation = $event->getTransaction();
        $actions = $translation->getActions();
        $source = $translation->getSource();

        if ($source->getWorld() === $this->server->getWorldManager()->getDefaultWorld()){
            foreach ($actions as $action){
                if (!$source->hasPermission("quza.staff")) {
                    if ($action instanceof SlotChangeAction && !isset(Duels::$editingKit[$source->getName()])) {
                        $event->cancel();
                    }
                }
            }
        }
    }

    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();

        if ($player->getWorld() == $this->server->getWorldManager()->getDefaultWorld() || $player->getWorld()->getFolderName() == Variables::Nodebuffffa || $player->getWorld()->getFolderName() == Variables::Sumoffa || $player->getWorld()->getFolderName() == Variables::Fistffa ||  $player->getWorld()->getFolderName() == Variables::Comboffa || $player->getWorld()->getFolderName() == Variables::Knockffa || $player->getWorld()->getFolderName() == Variables::Gappleffa || $player->getWorld()->getFolderName() == Variables::Resistanceffa || $player->getWorld()->getFolderName() == Variables::Midfightffa || $player->getWorld()->getFolderName() == Variables::Skywarsffa || $player->getWorld()->getFolderName() == Variables::Builduhcffa)  {

            if (!$player->hasPermission("quza.staff")) {
                $event->cancel();
            }
            if ($player->getGamemode() !== GameMode::CREATIVE()) {
                $event->cancel();
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event){
        $player = $event->getPlayer();

        if ($player->getWorld() === $this->server->getWorldManager()->getDefaultWorld()) {
            if (!$player->hasPermission("quza.staff")) {
                $event->cancel();
            }
            if ($player->getGamemode() !== GameMode::CREATIVE()) {
                $event->cancel();
            }
        }
    }

    public function onBlockClick(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if($player->getWorld() == $this->server->getWorldManager()->getDefaultWorld()){
            if ($event->getAction() == $event::RIGHT_CLICK_BLOCK){
                $event->cancel();
            }
        }
    }

    public function onDrop(PlayerDropItemEvent $event){
        $player = $event->getPlayer();
        $event->cancel();
    }

    public function onRegen(EntityRegainHealthEvent $event){
        $entity = $event->getEntity();
        if (!$entity instanceof Player) return;

        if ($event->getRegainReason() == $event::CAUSE_SATURATION){
            $event->cancel();
        }
    }

    public function onExhaust(PlayerExhaustEvent $event){
        //$player = $event->getPlayer();
        //$player->getHungerManager()->setFood(20);
        $event->cancel();
    }

    public function onProjectileHit(ProjectileHitBlockEvent $event){
        $projectile = $event->getEntity();
        $projectile->flagForDespawn();

        if ($projectile instanceof SplashPotion){
            $player = $projectile->getOwningEntity();

            if ($player === null)return;

            if ($player->isAlive()){
                if ($player instanceof Player){
                    if ($player->isConnected()){
                        if ($projectile->getLocation()->distance($player->getLocation()->asVector3()) <= 3){
                            $player->setHealth($player->getHealth() + 5.5);
                        }
                    }
                }
            }
        }
    }

    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        if ($player->getWorld() === Server::getInstance()->getWorldManager()->getDefaultWorld()) {
            if ($player->getLocation()->getY() < 5) {
                $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
                $player->teleport($location);
                $player->setGamemode(GameMode::ADVENTURE());
                $player->getHungerManager()->setFood(20);
                $player->getHungerManager()->setEnabled(false);
                $player->setMaxHealth(20);
                $player->setHealth(20);
                $player->getInventory()->setHeldItemIndex(0);
                $player->getEffects()->clear();
                PlayerManager::sendLobbyKit($player);
                Main::$playerArena[$player->getName()] = "Lobby";
            }
        } else {
            if ($player->getWorld()->getFolderName() === Variables::Nodebuffffa or
                $player->getWorld()->getFolderName() === Variables::Fistffa or
                $player->getWorld()->getFolderName() === Variables::Sumoffa or
                $player->getWorld()->getFolderName() === Variables::Comboffa or
                $player->getWorld()->getFolderName() === Variables::Gappleffa or
                $player->getWorld()->getFolderName() === Variables::Knockffa or
                $player->getWorld()->getFolderName() === Variables::Resistanceffa or
                $player->getWorld()->getFolderName() === Variables::Buildffa or
                $player->getWorld()->getFolderName() === Variables::Midfightffa or
                $player->getWorld()->getFolderName() === Variables::Skywarsffa or
                $player->getWorld()->getFolderName() === Variables::Builduhcffa) {

                if ($player->getLocation()->getY() < -10) {
                    $lastDmg = $player->getLastDamageCause();
                    if ($lastDmg instanceof EntityDamageByEntityEvent) {
                        $dmg = $lastDmg->getDamager();

                        if (!$dmg instanceof Player) return;

                        $player->setLastDamageCause(new EntityDamageEvent($player, 0, 0.0, []));
                        $dmg->setLastDamageCause(new EntityDamageEvent($dmg, 0, 0.0, []));
                        $dmgname = $dmg->getName();
                        $playername = $player->getName();

                        $dmg->sendMessage("§a{$dmgname} §7killed §c{$playername}");
                        $player->sendMessage("§a{$dmgname} §7killed §c{$playername}");

                        $dmg->sendMessage(Variables::Prefix . "§a+10 Coins!");
                        $dmg->sendMessage(Variables::Prefix . "§a+50 Honour!");

                        KillStreak::getInstance()->getProvider()->resetKSPoints($player);
                        KillStreak::getInstance()->getProvider()->addKSPoints($dmg, 1);
                        $oldstreak = KillStreak::getInstance()->getProvider()->getPlayerKSPoints($player);
                        $newstreak = KillStreak::getInstance()->getProvider()->getPlayerKSPoints($dmg);

                        if(is_int($newstreak / 5)){
                            Server::getInstance()->broadcastMessage(Variables::Prefix . "§a" . $dmg->getName() . "§7 is on " . $newstreak . " killstreak");
                            PlayerManager::addPlayerCoin($dmg, 50);
                            PlayerManager::addPlayerHonour($dmg, 150);
                            $dmg->sendMessage("\n\n§a {$newstreak} Killstreak +50 Coins!\n§a +150 Honour!\n");
                        }

                        PlayerManager::addPlayerCoin($dmg, 10);
                        PlayerManager::addPlayerHonour($dmg, 50);

                        PlayerManager::addPlayerKill($dmg);
                        PlayerManager::addPlayerDeath($player);

                        $dmg->setHealth(20);
                        if (!isset(Main::$playerArena[$dmg->getName()])) return;
                        switch (Main::$playerArena[$dmg->getName()]) {
                            case "NodebuffFFA":
                                PlayerManager::sendNodebuffKit($dmg);
                                break;
                            case "ComboFFA":
                                PlayerManager::sendComboKit($dmg);
                                break;
                            case "FistFFA":
                                PlayerManager::sendFistKit($dmg);
                                break;
                            case "SumoFFA":
                                PlayerManager::sendSumoKit($dmg);
                                break;
                            case "GappleFFA":
                                PlayerManager::sendGappleKit($dmg);
                                break;
                            case "KnockFFA":
                                PlayerManager::sendKnockKit($dmg);
                                break;
                            case "ResistanceFFA":
                                PlayerManager::sendResistanceKit($dmg);
                                break;
                            case "BuildFFA":
                                PlayerManager::sendBFFAKit($dmg);
                                break;
                            case "MidfightFFA":
                                PlayerManager::sendMidfightKit($dmg);
                                break;
                            case "SkywarsFFA":
                                PlayerManager::sendSkywarsKit($dmg);
                                break;
                            case "BuildUHCFFA":
                                PlayerManager::sendBuildUHCKit($dmg);
                                break;
                        }
                    } else {
                        $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
                        $player->teleport($location);
                        $player->setGamemode(GameMode::ADVENTURE());
                        $player->getHungerManager()->setFood(20);
                        $player->getHungerManager()->setEnabled(false);
                        $player->setMaxHealth(20);
                        $player->setHealth(20);
                        $player->getInventory()->setHeldItemIndex(0);
                        $player->getEffects()->clear();
                        PlayerManager::sendLobbyKit($player);
                        Main::$playerArena[$player->getName()] = "Lobby";
                    }
                }
            }
        }
    }

    public function onAttack(EntityDamageEvent $event){
        $entity = $event->getEntity();
        if (!$entity instanceof Player) return;

        if ($entity->getWorld() === $this->server->getWorldManager()->getDefaultWorld()){
            $event->cancel();
            return;
        }

        if ($entity->getWorld()->getFolderName() === Variables::Nodebuffffa or
            $entity->getWorld()->getFolderName() === Variables::Fistffa or
            $entity->getWorld()->getFolderName() === Variables::Sumoffa or
            $entity->getWorld()->getFolderName() === Variables::Comboffa or
            $entity->getWorld()->getFolderName() === Variables::Gappleffa or
            $entity->getWorld()->getFolderName() === Variables::Knockffa or
            $entity->getWorld()->getFolderName() === Variables::Resistanceffa or
            $entity->getWorld()->getFolderName() === Variables::Buildffa or
            $entity->getWorld()->getFolderName() === Variables::Midfightffa or
            $entity->getWorld()->getFolderName() === Variables::Skywarsffa or
            $entity->getWorld()->getFolderName() === Variables::Builduhcffa) {

            if ($event->getCause() == $event::CAUSE_VOID) {
                $lastDmg = $entity->getLastDamageCause();
                if ($lastDmg instanceof EntityDamageByEntityEvent) {
                    $dmg = $lastDmg->getDamager();

                    if (!$dmg instanceof Player) return;

                    $entity->setLastDamageCause($event);
                    $dmg->setLastDamageCause($event);

                    $dmgname = $dmg->getName();
                     $entityname = $entity->getName();

                        $entity->sendMessage("§a{$entityname} §7killed §c{$dmgname}");
                        $dmg->sendMessage("§a{$entityname} §7killed §c{$dmgname}");

                    KillStreak::getInstance()->getProvider()->resetKSPoints($dmg);
                    KillStreak::getInstance()->getProvider()->addKSPoints($entity, 1);
                    $oldstreak = KillStreak::getInstance()->getProvider()->getPlayerKSPoints($entity);
                    $newstreak = KillStreak::getInstance()->getProvider()->getPlayerKSPoints($dmg);

                    if(is_int($newstreak / 5)){
                        Server::getInstance()->broadcastMessage(Variables::Prefix . "§a" . $entity->getName() . "§7 is on " . $newstreak . " killstreak");
                        PlayerManager::addPlayerCoin($entity, 50);
                        PlayerManager::addPlayerHonour($entity, 150);
                        $entity->sendMessage("\n\n§a {$newstreak} Killstreak +50 Coins!\n§a +150 Honour!\n");
                    }

                    $entity->sendMessage(Variables::Prefix . "§a+10 Coins!");
                    $entity->sendMessage(Variables::Prefix . "§a+50 Honour!");

                    PlayerManager::addPlayerCoin($entity, 10);
                    PlayerManager::addPlayerHonour($entity, 50);
                    PlayerManager::addPlayerKill($dmg);
                    PlayerManager::addPlayerDeath($entity);

                    $dmg->setHealth(20);
                    if (!isset(Main::$playerArena[$dmg->getName()])) return;
                    switch (Main::$playerArena[$dmg->getName()]) {
                        case "NodebuffFFA":
                            PlayerManager::sendNodebuffKit($dmg);
                            break;
                        case "ComboFFA":
                            PlayerManager::sendComboKit($dmg);
                            break;
                        case "FistFFA":
                            PlayerManager::sendFistKit($dmg);
                            break;
                        case "SumoFFA":
                            PlayerManager::sendSumoKit($dmg);
                            break;
                        case "GappleFFA":
                            PlayerManager::sendGappleKit($dmg);
                            break;
                        case "KnockFFA":
                            PlayerManager::sendKnockKit($dmg);
                            break;
                        case "ResistanceFFA":
                            PlayerManager::sendResistanceKit($dmg);
                            break;
                        case "BuildFFA":
                            PlayerManager::sendBFFAKit($dmg);
                            break;
                        case "MidfightFFA":
                            PlayerManager::sendMidfightKit($dmg);
                            break;
                        case "SkywarsFFA":
                            PlayerManager::sendSkywarsKit($dmg);
                            break;
                        case "BuildUHCFFA":
                            PlayerManager::sendBuildUHCKit($dmg);
                            break;
                    }
                }
            }
        }
    }

    public function packetReceive(DataPacketReceiveEvent $e) : void{
        //$cpsPopup = new Config($this->plugin->getDataFolder() . "settings/CPSPopup.yml", Config::YAML);
        $pk = $e->getPacket();
        $player = $e->getOrigin()->getPlayer();

        if($e->getOrigin()->getPlayer() == null) return;

        if ($pk instanceof LoginPacket) {
            $data = JwtUtils::parse($pk->clientDataJwt);
            $name = $data[1]["ThirdPartyName"];
            if ($data[1]["PersonaSkin"]) {             
                if (!file_exists(Main::getInstance()->getDataFolder() . "saveskin")) {
                    mkdir(Main::getInstance()->getDataFolder() . "saveskin", 0777);
                }
                copy(Main::getInstance()->getDataFolder()."steve.png",Main::getInstance()->getDataFolder() . "saveskin/$name.png");
                return;
            }
            if ($data[1]["SkinImageHeight"] == 32) {           
            }
            $saveSkin = new saveSkin();
            $saveSkin->saveSkin(base64_decode($data[1]["SkinData"], true), $name);
        }

        if (SettingsManager::getCpsEnabled($e->getOrigin()->getPlayer())) {
            if (
                ($e->getPacket()::NETWORK_ID === InventoryTransactionPacket::NETWORK_ID && $e->getPacket()->trData instanceof UseItemOnEntityTransactionData) ||
                ($e->getPacket()::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID && $e->getPacket()->sound === LevelSoundEvent::ATTACK_NODAMAGE) ||
                ($e->getPacket()::NETWORK_ID === PlayerActionPacket::NETWORK_ID && $e->getPacket()->action === PlayerAction::START_BREAK)
            ) {
                $this->plugin->addCPS($e->getOrigin()->getPlayer());
                $e->getOrigin()->getPlayer()->sendTip("§b{$this->plugin->getCPS($e->getOrigin()->getPlayer())} CPS");
            }
        }

        if($pk instanceof InventoryTransactionPacket){
            if($pk->trData instanceof UseItemOnEntityTransactionData){
                if($pk->trData->getActionType() == UseItemOnEntityTransactionData::ACTION_ATTACK){
                    if($player->isSpectator() || $player->getGamemode()->equals(GameMode::SPECTATOR())){
                        $e->cancel();
                    }
                }
            }
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event){
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();

        if($player === null) return;

        switch ($packet->pid()){
            case PlayerAuthInputPacket::NETWORK_ID:
                //$autoSprint = new Config($this->plugin->getDataFolder() . "settings/AutoSprint.yml", Config::YAML);

                if (SettingsManager::getAutoSprintEnabled($player)){
                    if($player->isSprinting() && $packet->hasFlag(PlayerAuthInputFlags::DOWN)){
                        $player->setSprinting(false);
                    }elseif(!$player->isSprinting() && $packet->hasFlag(PlayerAuthInputFlags::UP)){
                        $player->setSprinting();
                    }
                }
                break;
            case InventoryTransactionPacket::NETWORK_ID:
                if($packet->trData->getTypeId() == InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
                    $trData = $packet->trData;
                    if ($trData->getActionType() == UseItemOnEntityTransactionData::ACTION_ATTACK){
                        $entityId = $trData->getActorRuntimeId();
                        //$hitParticles = new Config($this->plugin->getDataFolder() . "settings/HitParticles.yml", Config::YAML);

                        if (SettingsManager::getHitEffectEnabled($player)){
                            $player->getServer()->broadcastPackets([$player], [AnimatePacket::create($entityId, AnimatePacket::ACTION_CRITICAL_HIT)]);
                        }
                    }
                }
                break;
        }
    }

    public function onWorldTp(EntityTeleportEvent $event){
        $entity = $event->getEntity();

        if (!$entity instanceof Player) return;

        if ($event->getFrom()->getWorld() !== $event->getTo()->getWorld()){
            if ($event->getTo()->getWorld()->getFolderName() != Variables::Nodebuffffa or $event->getTo()->getWorld()->getFolderName() != Variables::Comboffa or $event->getTo()->getWorld()->getFolderName() != Variables::Fistffa or $event->getTo()->getWorld()->getFolderName() != Variables::Sumoffa or $event->getTo()->getWorld()->getFolderName() != Variables::Gappleffa or $event->getTo()->getWorld()->getFolderName() != Variables::Knockffa or $event->getTo()->getWorld()->getFolderName() != Variables::Buildffa or $event->getTo()->getWorld()->getFolderName() != Variables::Midfightffa or $event->getTo()->getWorld()->getFolderName() !=Variables::Skywarsffa or $event->getTo()->getWorld()->getFolderName() != Variables::Builduhcffa) {
                unset(Main::$playerArena[$entity->getName()]);
            }
        }
    }

    public function onKnockback(EntityDamageByEntityEvent $event)
    {
        $damager = $event->getDamager();
        $entity = $event->getEntity();

        if($entity instanceof EggHuntEntity && $damager instanceof Player){
            $id = (int)$entity->getNameTag();
            
            $huntedData = EggHuntManager::$hunted[$damager->getName()];
            
            $huntedData[] = $id;
            $entity->despawnFrom($damager);

            EggHuntManager::$points[$damager->getName()]++;

            $damager->sendMessage("§aYou've found an egg! (" . EggHuntManager::$points[$damager->getName()] . "/20)");

            return;
        }

        if (!$damager instanceof Player && !$entity instanceof Player) return;

        if ($damager->getWorld()->getFolderName() === Variables::Nodebuffffa or
            $damager->getWorld()->getFolderName() === Variables::Fistffa or
            $damager->getWorld()->getFolderName() === Variables::Sumoffa or
            $damager->getWorld()->getFolderName() === Variables::Comboffa or
            $damager->getWorld()->getFolderName() === Variables::Gappleffa or
            $damager->getWorld()->getFolderName() === Variables::Knockffa or
            $damager->getWorld()->getFolderName() === Variables::Resistanceffa or
            $damager->getWorld()->getFolderName() === Variables::Buildffa or
            $damager->getWorld()->getFolderName() === Variables::Midfightffa or
            $damager->getWorld()->getFolderName() === Variables::Skywarsffa or
            $damager->getWorld()->getFolderName() === Variables::Builduhcffa) {
            if ($entity->getHealth() <= $event->getFinalDamage() + 1) {
                $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
                $entity->teleport($location);
                $entity->setGamemode(GameMode::ADVENTURE());
                $entity->getHungerManager()->setFood(20);
                $entity->getHungerManager()->setEnabled(false);
                $entity->setMaxHealth(20);
                $entity->setHealth(20);
                $entity->getInventory()->setHeldItemIndex(0);
                $entity->getEffects()->clear();
                PlayerManager::sendLobbyKit($entity);
                Main::$playerArena[$entity->getName()] = "Lobby";
                
                 $dmgname = $damager->getName();
                 $entityname = $entity->getName();

                  $entity->sendMessage("§a{$dmgname} §7killed §c{$entityname}");
                  $damager->sendMessage("§a{$dmgname} §7killed §c{$entityname}");
                $damager->sendMessage(Variables::Prefix . "§a+10 Coins!");
                $damager->sendMessage(Variables::Prefix . "§a+50 Honour!");

                PlayerManager::addPlayerCoin($damager, 10);
                PlayerManager::addPlayerHonour($damager, 50);

                PlayerManager::addPlayerKill($damager);
                PlayerManager::addPlayerDeath($entity);

                KillStreak::getInstance()->getProvider()->resetKSPoints($entity);
                KillStreak::getInstance()->getProvider()->addKSPoints($damager, 1);
                $oldstreak = KillStreak::getInstance()->getProvider()->getPlayerKSPoints($entity);
                $newstreak = KillStreak::getInstance()->getProvider()->getPlayerKSPoints($damager);

                if(is_int($newstreak / 5)){
                    Server::getInstance()->broadcastMessage(Variables::Prefix . "§a" . $damager->getName() . "§7 is on " . $newstreak . " killstreak");
                    PlayerManager::addPlayerCoin($damager, 50);
                    PlayerManager::addPlayerHonour($damager, 150);
                    $damager->sendMessage("\n\n§a {$newstreak} Killstreak +50 Coins!\n§a +150 Honour!\n");
                }

                $damager->setHealth(20);
                if (!isset(Main::$playerArena[$damager->getName()])) return;
                switch (Main::$playerArena[$damager->getName()]) {
                    case "NodebuffFFA":
                        PlayerManager::sendNodebuffKit($damager);
                        break;
                    case "ComboFFA":
                        PlayerManager::sendComboKit($damager);
                        break;
                    case "FistFFA":
                        PlayerManager::sendFistKit($damager);
                        break;
                    case "SumoFFA":
                        PlayerManager::sendSumoKit($damager);
                        break;
                    case "GappleFFA":
                        PlayerManager::sendGappleKit($damager);
                        break;
                    case "KnockFFA":
                        PlayerManager::sendKnockKit($damager);
                        break;
                    case "ResistanceFFA":
                        PlayerManager::sendResistanceKit($damager);
                        break;
                    case "BuildFFA":
                        PlayerManager::sendBFFAKit($damager);
                        break;
                    case "MidfightFFA":
                        PlayerManager::sendMidfightKit($damager);
                        break;
                    case "SkywarsFFA":
                        PlayerManager::sendSkywarsKit($damager);
                        break;
                    case "BuildUHCFFA":
                        PlayerManager::sendBuildUHCKit($damager);
                        break;
                }
            }
        }

        /**
        if (isset(Main::$playerArena[$entity->getName()])) {
            switch (Main::$playerArena[$entity->getName()]) {
                case "NodebuffFFA":
                case "GappleFFA":
                    $event->setKnockBack(0.39);
                    $event->setAttackCooldown(9);
                    break;
                case "SumoFFA":
                    //$event->setKnockBack(0.4);
                    //$event->setAttackCooldown(7);
                    break;
                case "FistFFA":
                    $event->setKnockBack(0.38);
                    $event->setAttackCooldown(7);
                    break;
                case "ResistanceFFA":
                    //$event->setKnockBack(0.5);
                    //$event->setAttackCooldown(9);
                    break;
                case "ComboFFA":
                    $event->setKnockBack(0.28);
                    $event->setAttackCooldown(2);
                    break;
                default:
                    $event->setKnockBack(0.38);
                    $event->setAttackCooldown(8);
                    break;
            }
        }
        */

        if ($damager->getWorld()->getFolderName() === Variables::Nodebuffffa or
            $damager->getWorld()->getFolderName() === Variables::Fistffa or
            $damager->getWorld()->getFolderName() === Variables::Sumoffa or
            $damager->getWorld()->getFolderName() === Variables::Comboffa or
            $damager->getWorld()->getFolderName() === Variables::Gappleffa or
            $damager->getWorld()->getFolderName() === Variables::Resistanceffa or
            $damager->getWorld()->getFolderName() === Variables::Knockffa or
            $damager->getWorld()->getFolderName() === Variables::Midfightffa or

            $damager->getWorld()->getFolderName() === Variables::Builduhcffa) {
            if (!isset(PlayerManager::$combatOpponent[$entity->getName()]) && !isset(PlayerManager::$combatOpponent[$damager->getName()])) {
                PlayerManager::setCombatOpponent($entity, $damager);
                PlayerManager::setCombatTimer($entity, $damager);

                if (!$entity->isConnected() || !$damager->isConnected()) {
                    return;
                }
                $entity->sendMessage(Variables::Prefix . "§aYou are now in combat with §f" . $damager->getDisplayName());
                $damager->sendMessage(Variables::Prefix . "§aYou are now in combat with §f" . $entity->getDisplayName());

                $this->plugin->getScheduler()->scheduleRepeatingTask(new CombatTask($this->plugin, $entity, $damager), 20);

                foreach ($this->server->getOnlinePlayers() as $player) {
                    if ($player !== $damager) {
                        $entity->hidePlayer($player);
                    }
                    if ($player !== $entity) {
                        $damager->hidePlayer($player);
                    }
                }
            } elseif (isset(PlayerManager::$combatOpponent[$entity->getName()]) && !isset(PlayerManager::$combatOpponent[$damager->getName()])) {
                $event->cancel();
                $damager->sendMessage(Variables::Prefix . "§cInterrupting is not allowed!");
            } elseif (!isset(PlayerManager::$combatOpponent[$entity->getName()]) && isset(PlayerManager::$combatOpponent[$damager->getName()])) {
                $event->cancel();
                $damager->sendMessage(Variables::Prefix . "§cYour Enemy is §e" . PlayerManager::getCombatOpponent($damager));
            } elseif (isset(PlayerManager::$combatOpponent[$entity->getName()])) {
                if (PlayerManager::$combatOpponent[$entity->getName()] !== $damager->getName()) {
                    $event->cancel();
                    $damager->sendMessage(Variables::Prefix . "§cInterrupting is not allowed!");
                } else {
                    PlayerManager::setCombatTimer($entity, $damager);
                }
            }
        }

        if ($damager->getWorld()->getFolderName() === Variables::Skywarsffa){
            if (!isset(PlayerManager::$combatOpponent[$entity->getName()]) and !isset(PlayerManager::$combatOpponent[$damager->getName()]) and !$event->isCancelled()) {
                PlayerManager::setCombatOpponent($entity, $damager);
                PlayerManager::setCombatTimer($entity, $damager);

                if (!$entity->isConnected() || !$damager->isConnected()) {
                    return;
                }

                $this->plugin->getScheduler()->scheduleRepeatingTask(new BuildCombatTask($this->plugin, $entity, $damager), 20);
            } else {
                PlayerManager::setCombatOpponent($entity, $damager);
                $this->plugin->getScheduler()->scheduleRepeatingTask(new BuildCombatTask($this->plugin, $entity, $damager), 20);

            }
        }
    }

    public function onItemUse(PlayerItemUseEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();
        $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
        
        if ($item->hasCustomName() && $player->getWorld() === $this->server->getWorldManager()->getDefaultWorld()) {
            switch ($item->getId()) {
                case VanillaItems::BOWL()->getId():
                    $player->sendForm(FormManager::getFFAForm());
                    break;
                case VanillaItems::IRON_SWORD()->getId():
                    $player->sendForm(FormManager::getDuelForm());
                    break;
                case VanillaItems::CLOCK()->getId():
                    $player->sendForm(FormManager::getSpectateForm($player));
                    break;
                case VanillaItems::BOOK()->getId():
                    $player->sendForm(FormManager::getStatsForm($player));
                    break;
                case VanillaItems::DIAMOND()->getId():
                    $player->sendForm(FormManager::getCapeForm());
                    break;
                case VanillaItems::COAL()->getId():
                    $player->sendForm(FormManager::getSettingsForm($player));
                    break;
            }
        }

        if ($player->getWorld()->getFolderName() === Variables::Builduhcffa) return;
        if ($item instanceof EnderPearl){
            if (isset(Main::$pearlCooldown[$player->getName()])){
                $event->cancel();

                Await::f2c(function () use ($player){
                    $player->getInventory()->removeItem(VanillaItems::ENDER_PEARL());
                    $player->getInventory()->addItem(VanillaItems::ENDER_PEARL()); //to fix ghost item bug

                    yield from $this->plugin->std->sleep(3);
                });
            } else{
                Main::$pearlCooldown[$player->getName()] = 4;
                $this->plugin->getScheduler()->scheduleRepeatingTask(new PearlTask($player), 8);
            }
        }
    } 
    public function onSpecUse(PlayerItemHeldEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();
        $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
        
         if ($item->hasCustomName() && $player->getWorld()->getFolderName() == Variables::Nodebuffffa || $player->getWorld()->getFolderName() == Variables::Sumoffa || $player->getWorld()->getFolderName() == Variables::Fistffa ||  $player->getWorld()->getFolderName() == Variables::Comboffa || $player->getWorld()->getFolderName() == Variables::Knockffa || $player->getWorld()->getFolderName() == Variables::Gappleffa || $player->getWorld()->getFolderName() == Variables::Resistanceffa || $player->getWorld()->getFolderName() == Variables::Midfightffa || $player->getWorld()->getFolderName() == Variables::Skywarsffa || $player->getWorld()->getFolderName() == Variables::Builduhcffa) {
            switch ($item->getId()) {
                case VanillaItems::RED_DYE()->getId():
                      $player->getServer()->dispatchCommand($player, "hub");
                    break;
            }
         }         
    }
   /* public function onSpecHit(EntityDamageByEntityEvent $event){
        $player = $event->getPlayer(); 
         if ($player->getWorld() == Variables::Nodebuffffa || $player->getWorld()->getFolderName() == Variables::Sumoffa || $player->getWorld()->getFolderName() == Variables::Fistffa ||  $player->getWorld()->getFolderName() == Variables::Comboffa || $player->getWorld()->getFolderName() == Variables::Knockffa || $player->getWorld()->getFolderName() == Variables::Gappleffa || $player->getWorld()->getFolderName() == Variables::Resistanceffa || $player->getWorld()->getFolderName() == Variables::Midfightffa || $player->getWorld()->getFolderName() == Variables::Skywarsffa || $player->getWorld()->getFolderName() == Variables::Builduhcffa)  {
         }
          if ($player->getGamemode() !== GameMode::ADVENTURE()) { 
            $event->cancel();       
          }
    }*/
  /*  public function flyEnable(PlayerItemUseEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();
        $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
        
         if ($item->hasCustomName() && $player->getWorld() == $this->server->getWorldManager()->getDefaultWorld() || $player->getWorld()->getFolderName() == Variables::Nodebuffffa || $player->getWorld()->getFolderName() == Variables::Sumoffa || $player->getWorld()->getFolderName() == Variables::Fistffa ||  $player->getWorld()->getFolderName() == Variables::Comboffa || $player->getWorld()->getFolderName() == Variables::Knockffa || $player->getWorld()->getFolderName() == Variables::Gappleffa || $player->getWorld()->getFolderName() == Variables::Resistanceffa || $player->getWorld()->getFolderName() == Variables::Midfightffa || $player->getWorld()->getFolderName() == Variables::Skywarsffa || $player->getWorld()->getFolderName() == Variables::Builduhcffa)  {
         }         
            switch ($item->getId()) {
                case VanillaItems::EMERALD()->getId():
                    $player->setAllowFlight(true);
                    break;
            }
    }
    public function flyDisable(PlayerItemUseEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();
        $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
        
         if ($item->hasCustomName() && $player->getWorld() == $this->server->getWorldManager()->getDefaultWorld() || $player->getWorld()->getFolderName() == Variables::Nodebuffffa || $player->getWorld()->getFolderName() == Variables::Sumoffa || $player->getWorld()->getFolderName() == Variables::Fistffa ||  $player->getWorld()->getFolderName() == Variables::Comboffa || $player->getWorld()->getFolderName() == Variables::Knockffa || $player->getWorld()->getFolderName() == Variables::Gappleffa || $player->getWorld()->getFolderName() == Variables::Resistanceffa || $player->getWorld()->getFolderName() == Variables::Midfightffa || $player->getWorld()->getFolderName() == Variables::Skywarsffa || $player->getWorld()->getFolderName() == Variables::Builduhcffa)  {
         }         
            switch ($item->getId()) {
                case VanillaItems::REDSTONE_DUST()->getId():
                    $player->setAllowFlight(false);
                    break;
            }                  
    }
    public function combatVanish(PlayerMoveEvent $event){
        $player = $event->getPlayer();
     //   $online = count($this->plugin->getServer()->getOnlinePlayers());
         if ($player->getWorld()->getFolderName() == Variables::Nodebuffffa || $player->getWorld()->getFolderName() == Variables::Sumoffa || $player->getWorld()->getFolderName() == Variables::Fistffa ||  $player->getWorld()->getFolderName() == Variables::Comboffa || $player->getWorld()->getFolderName() == Variables::Knockffa || $player->getWorld()->getFolderName() == Variables::Gappleffa || $player->getWorld()->getFolderName() == Variables::Resistanceffa || $player->getWorld()->getFolderName() == Variables::Midfightffa || $player->getWorld()->getFolderName() == Variables::Skywarsffa || $player->getWorld()->getFolderName() == Variables::Builduhcffa)  {
             if ($player->getGamemode() !== GameMode::ADVENTURE()) { 
               //   $online->hidePlayer($player);
                  PlayerManager::$isCombat[$player->getName()] = false;
             }
         }
    }             */
    public function onBowUse(PlayerItemUseEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();
        $location = $this->server->getWorldManager()->getWorldByName("lobby")->getSafeSpawn();
        
        if ($item->hasCustomName() && $player->getWorld() === $this->server->getWorldManager()->getDefaultWorld()) {
            switch ($item->getId()) {
                case VanillaItems::BOWL()->getId():
                    $player->sendForm(FormManager::getFFAForm());
                    break;
                case VanillaItems::IRON_SWORD()->getId():
                    $player->sendForm(FormManager::getDuelForm());
                    break;
                case VanillaItems::CLOCK()->getId():
                    $player->sendForm(FormManager::getSpectateForm());
                    break;
                case VanillaItems::BOOK()->getId():
                    $player->sendForm(FormManager::getStatsForm($player));
                    break;
                case VanillaItems::DIAMOND()->getId():
                    $player->sendForm(FormManager::getCapeForm());
                    break;
                case VanillaItems::COAL()->getId():
                    $player->sendForm(FormManager::getSettingsForm($player));
                    break;
            }
        }

        if ($player->getWorld()->getFolderName() === Variables::Buildffa) return;
        
    }
    public function bowcooldown(EntityShootBowEvent $event){
        $player = $event->getEntity();

        if (!$player instanceof Player) return;
        
            if (isset(Main::$bowCooldown[$player->getName()])){
                $event->cancel();

                Await::f2c(function () use ($player){

                    yield from $this->plugin->std->sleep(3);
                });
            } else{
                Main::$bowCooldown[$player->getName()] = 4;
                $this->plugin->getScheduler()->scheduleRepeatingTask(new BowTask($player), 20);
            }
        }
    
    /**
     * HyperiumMC function code
     */

    public function isJohnnyWai(Player $player){
        if (strtolower($player->getName()) == "johnnywai666"){
            return true;
        }
        return false;
    }

    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();

        $w = Main::getInstance()->getConfig()->get("webhook");
        $r = Main::getInstance()->getConfig()->get("region");

        //$event->setFormat(RankManager::getPlayerRank($player)->getDisplayFormat() . $player->getDisplayName() . "§8 : §f" . $event->getMessage());

        $chatFormat = RankManager::getPlayerRank($player)->getChatFormat();
        $chatFormat = str_replace("{rank}", RankManager::getPlayerRank($player)->getDisplayFormat(), $chatFormat);
        $chatFormat = str_replace("{player}", $player->getDisplayName(), $chatFormat);
        $chatFormat = str_replace("{message}", $event->getMessage(), $chatFormat);

        if(TagManager::getPlayerCurrentTag($player) != null){
            $chatFormat = str_replace("{tag}", " " . TagManager::getPlayerCurrentTag($player)->getDisplayFormat(), $chatFormat);
        } else {
            $chatFormat = str_replace("{tag}", "", $chatFormat);
        }
        $chatFormat = str_replace("{tier}", PassManager::getPassColour($player) . PassManager::getTierDisplay($player) . "§r", $chatFormat);
        
        $event->setFormat($chatFormat);

        /**
         * HyperiumMC function code
         */

        if ($this->isJohnnyWai($player)){
            $event->setMessage("§c已封锁刷屏仔的说话权! 你真可悲");
            $player->sendMessage("§e你觉得你有在这里说话的资格吗?? 垃圾");

            return;
        }

        if (isset($this->plugin->chatcooldown[$player->getName()])){
            $event->cancel();
            $player->sendMessage(Variables::Prefix . "§cYou must wait {$this->plugin->chatcooldown[$player->getName()]}s to send message again !");
        }

        if (!isset($this->plugin->chatcooldown[$player->getName()]) || $this->plugin->chatcooldown[$player->getName()] == 0){
            $this->plugin->chatcooldown[$player->getName()] = 3;
            $webhook = new Webhook("{$w}");
            $embed = new Embed();
            $embed->setTitle("Player Chats - {$r}");
            $embed->setDescription("[" . RankManager::getPlayerRank($player)->getName() . "] " . $player->getName(). " : " . $event->getMessage());
            $embed->setFooter("Made By Kakashi");
            $embed->setTimestamp(new \DateTime("now"));
            $embed->setColor(0x02F3D6);
            $message = new Message();
            $message->addEmbed($embed);
            $webhook->send($message);
        }
    }

    public function onBowBoost(EntityShootBowEvent $event)
    {
        $entity = $event->getEntity();
        $arrow = $event->getProjectile();
        $power = $event->getForce();

        if ($entity instanceof Player and $arrow instanceof Arrow) {
            if ($entity->getWorld() === $this->server->getWorldManager()->getWorldByName(Variables::Knockffa)) {
                if ($power <= 0.8 and $entity->getMovementSpeed() !== 0.0) {
                    $entity->setMotion($entity->getDirectionVector()->multiply(1.2));
                    $entity->broadcastAnimation(new HurtAnimation($entity));
                    $arrow->kill();
                    if ($entity->getHealth() > 1.0) {
                        $entity->setHealth($entity->getHealth() - 1.0);
                    }
                } elseif (($power <= 0.8) and $entity->getMovementSpeed() !== 0.0) {
                    //$arrow->kill();
                }
            }
        }
    }

    public function onBFFAPlace(BlockPlaceEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($player->getWorld()->getFolderName() == Variables::Buildffa or
            $player->getWorld()->getFolderName() == Variables::Knockffa or
            $player->getWorld()->getFolderName() == Variables::Skywarsffa or
            $player->getWorld()->getFolderName() == Variables::Builduhcffa){
            Main::$bffaplacedblock[Utils::vectorToString($block->getPosition()->asVector3())] = $block->getPosition()->asVector3();

            $this->plugin->getScheduler()->scheduleRepeatingTask(new BuildFFATask($block->getPosition()->asVector3(), $block->getPosition()->getWorld()), 20);

        }
    }

    public function onBFFABreak(BlockBreakEvent $event){
        $player = $event->getPlayer();

        if ($player->getWorld()->getFolderName() == Variables::Buildffa or
            $player->getWorld()->getFolderName() == Variables::Knockffa or
            $player->getWorld()->getFolderName() == Variables::Skywarsffa or
            $player->getWorld()->getFolderName() == Variables::Builduhcffa){
            if(isset(Main::$bffaplacedblock[Utils::vectorToString($event->getBlock()->getPosition()->asVector3())])){
                unset(Main::$bffaplacedblock[Utils::vectorToString($event->getBlock()->getPosition()->asVector3())]);
            } else {
                $event->cancel();
            }
        }
    }

    public function onItemConsume(PlayerItemConsumeEvent $event)
        
    {

        $player = $event->getPlayer();
        $item = $event->getItem();

            if ($player->getWorld() === $this->server->getWorldManager() ->getWorldByName(Variables::Sumoffa)) {
            if ($item = ItemIds::GOLDEN_APPLE){
              $player->setMaxHealth(20);
              $player->setHealth(20);
         
            }
        } 
    }
}        