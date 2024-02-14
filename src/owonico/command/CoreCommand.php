<?php

namespace owonico\command;

use owonico\{Main, Variables};
use maipian\webhook\Embed;
use maipian\webhook\Message;
use maipian\webhook\Webhook;
use owonico\egghunt\EggHuntManager;
use owonico\entity\EggHuntEntity;
use owonico\manager\PlayerManager;
use owonico\manager\RankManager;
use owonico\manager\TagManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Skin;
use pocketmine\lang\Translatable;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\network\mcpe\NetworkSession;

class CoreCommand extends Command{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("core", "QuanMC Main Command");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {

        //if (!$sender instanceof Player) return;
        if (!$sender->hasPermission("console", "quza.staff")){
            $sender->sendMessage(Variables::Prefix . "§cYou dont have permission to perform this command!");
            return;
        }

        if (isset($args[0])){
            switch ($args[0]){
                case "setrank":
                    if (!isset($args[1]) && !isset($args[2])){
                        $sender->sendMessage("/core setrank <Player> <Rank>");
                        return;
                    }
                    $playername = $args[1] ?? $sender->getName();
                    $rank = $args[2] ?? "Player";

                    $rankClass = RankManager::$ranks[strtolower($rank)] ?? null;
                    if ($rankClass === null){
                        $sender->sendMessage(Variables::Prefix . "§cRank with name {$rank} is not found!");
                        return;
                    }
                    $player = $this->plugin->getServer()->getPlayerExact($playername);

                    if($player == null){
                        $sender->sendMessage("§cThe selected player is not online!");
                        return;
                    }
                    
                    RankManager::setPlayerRank($player, $rank);
                    $sender->sendMessage(Variables::Prefix . "§aSuccessfully changed {$player->getName()}'s rank to {$rankClass->getDisplayFormat()}");
                    $player->sendMessage(Variables::Prefix . "§aYour rank has been changed to " . $rankClass->getDisplayFormat() . " by §6" . $sender->getName());
                    break;
            
                case "settag":
                    if (!isset($args[1]) && !isset($args[2])){
                        $sender->sendMessage("/core settag <Player> <Tag>");
                        return;
                    }
                    $playername = $args[1] ?? $sender->getName();
                    $tag = $args[2] ?? "None";

                    $tagClass = TagManager::$tags[strtolower($tag)] ?? null;
                    if ($tagClass === null){
                        $sender->sendMessage(Variables::Prefix . "§cTag with name {$tag} is not found!");
                        return;
                    }
                    $player = $this->plugin->getServer()->getPlayerExact($playername);

                    if($player == null){
                        $sender->sendMessage("§cThe selected player is not online!");
                        return;
                    }
                    
                    if(TagManager::ownedTag($player, $tag)){
                        TagManager::setPlayerCurrentTag($player, $tag);
                        $sender->sendMessage(Variables::Prefix . "§aSuccessfully changed {$player->getName()}'s rank to {$tagClass->getDisplayFormat()}");
                        $player->sendMessage(Variables::Prefix . "§aYour tag has been changed to " . $tagClass->getDisplayFormat() . " by §6" . $sender->getName());
                    } else {
                        $sender->sendMessage(Variables::Prefix . "§cThis player does not own the selected tag yet. Use /core addtag <player> <tag> to add it");
                    }
                    
                    break;
                case "addtag":
                    if (!isset($args[1]) && !isset($args[2])){
                        $sender->sendMessage("/core addtag <Player> <Tag>");
                        return;
                    }
                    $playername = $args[1] ?? $sender->getName();
                    $tag = $args[2] ?? "None";

                    $tagClass = TagManager::$tags[strtolower($tag)] ?? null;
                    if ($tagClass === null){
                        $sender->sendMessage(Variables::Prefix . "§cTag with name {$tag} is not found!");
                        return;
                    }
                    $player = $this->plugin->getServer()->getPlayerExact($playername);

                    if($player == null){
                        $sender->sendMessage("§cThe selected player is not online!");
                        return;
                    }
                    
                    if(!TagManager::ownedTag($player, $tag)){
                        TagManager::addOwnedTag($player, $tag);
                        $sender->sendMessage(Variables::Prefix . "§aAdded the selected tag");
                    } else {
                        $sender->sendMessage(Variables::Prefix . "§cThis player is already own the selected tag");
                    }
                    
                    break;    
                case "givecoin":
                    if (!isset($args[1]) && !isset($args[2])){
                        $sender->sendMessage("/core givecoin <Player> <Coin>");
                        return;
                    }
                    $playername = $args[1] ?? $sender->getName();
                    $coin = $args[2];

                    $player = $this->plugin->getServer()->getPlayerExact($playername);

                    if($player == null){
                        $sender->sendMessage("§cThe selected player is not online!");
                        return;
                    }

                    PlayerManager::addPlayerCoin($player, $coin);

                    $sender->sendMessage(Variables::Prefix . "§aSuccessfully give {$player->getName()} {$coin} coins"); 
                    break;
                case "newpassowner":
                    if (!isset($args[1])){
                        $sender->sendMessage("/core newpassowner <Player>");
                        return;
                    }
                    $playername = $args[1] ?? $sender->getName();

                    $player = $this->plugin->getServer()->getPlayerExact($playername);

                    if($player == null){
                        $sender->sendMessage("§cThe selected player is not online!");
                        return;
                    }

                    PlayerManager::addPlayerPass($player, 1);

                    $sender->sendMessage(Variables::Prefix . "§aSuccessfully gave {$player->getName()} ownership of the Lithium Pass!"); 
                    break;
                case "removepassowner":
                    if (!isset($args[1])){
                        $sender->sendMessage("/core removepassowner <Player>");
                        return;
                    }
                    $playername = $args[1] ?? $sender->getName();

                    $player = $this->plugin->getServer()->getPlayerExact($playername);

                    if($player == null){
                        $sender->sendMessage("§cThe selected player is not online!");
                        return;
                    }

                    PlayerManager::reducePlayerPass($player, 1);

                    $sender->sendMessage(Variables::Prefix . "§aSuccessfully took away {$player->getName()}'s ownership of the Lithium Pass!"); 
                    break;
                case "addhonour":
                    if (!isset($args[1]) && !isset($args[2])){
                        $sender->sendMessage("/core addhonour <Player> <Amount>");
                        return;
                    }
                    $playername = $args[1] ?? $sender->getName();
                    $honour = $args[2];

                    $player = $this->plugin->getServer()->getPlayerExact($playername);

                    if($player == null){
                        $sender->sendMessage("§cThe selected player is not online!");
                        return;
                    }

                    PlayerManager::addPlayerHonour($player, $honour);

                    $sender->sendMessage(Variables::Prefix . "§aSuccessfully give {$player->getName()} {$honour} honour"); 
                    break;    
                case "egghunt":
                    
                    if(!$sender instanceof Player){
                        $sender->sendMessage("You can only run this command in game");
                        return;
                    }

                    $egghuntdatacount = EggHuntManager::$eggHuntData->get("EggCount") + 1;

                    $entity = new EggHuntEntity($sender->getLocation(), $sender->getSkin());
                    $entity->setSkin(new Skin($sender->getSkin()->getSkinId(), EggHuntManager::$egghuntSkin, "", "geometry.unknown", EggHuntManager::$egghuntGeo));
                    $entity->setImmobile();
                    
                    $entity->setNameTagVisible(false);
                    $entity->setNameTag($egghuntdatacount);

                    EggHuntManager::$eggHuntData->set("EggCount", $egghuntdatacount);
                    EggHuntManager::$eggHuntData->save();
                    
                    $entity->spawnToAll();
                    break;
                case "removeegghunt":
                    
                    if(!$sender instanceof Player){
                        $sender->sendMessage("You can only run this command in game");
                        return;
                    }
                    foreach($sender->getWorld()->getEntities() as $entity){
                        if($entity instanceof EggHuntEntity){
                            $entity->kill();
                        }
                    }
                    EggHuntManager::$eggHuntData->set("EggCount", 0);
                    EggHuntManager::$eggHuntData->save();
                    break;
                case "setupcrate":
                    
                    if(!$sender instanceof Player){
                        $sender->sendMessage("You can only run this command in game");
                        return;
                    }

                    Main::$crateSetup[$sender->getName()] = $sender;
                    $sender->sendMessage("§bClick the CHEST or ENDER CHEST to setup crate");
                    break;
            }
        }

        if (isset($args[0])){
            switch($args[0]){
                case "mm":
                    switch (strtolower($args[1])) {
                        case "on":

                            $w = Main::getInstance()->getConfig()->get("webhook");

                            $r = Main::getInstance()->getConfig()->get("region");

                            $this->plugin->getConfig()->set("maintenance", true);
                            $this->plugin->getConfig()->save();
                            $sender->sendMessage(Variables::Prefix . "§aYou have turned on maintenance");
                            $this->plugin->getServer()->getNetwork()->setName(Variables::MotdMaintance);

                            $webhook = new Webhook("{$w}");
                            $embed = new Embed();
                            $embed->setTitle("Server Status - {$r}");
                            $embed->setDescription("QuzaNetwork is now turned on maintenance");
                            $embed->setFooter("QuadriumMC & QuanMC");
                            $embed->setTimestamp(new \DateTime("now"));
                            $embed->setColor(0xFE0C0C);
                            $message = new Message();
                            $message->addEmbed($embed);
                            $webhook->send($message);

                            break;
                        case "off":

                            $w = Main::getInstance()->getConfig()->get("webhook");

                            $r = Main::getInstance()->getConfig()->get("region");

                            $this->plugin->getConfig()->set("maintenance", false);
                            $this->plugin->getConfig()->save();
                            $sender->sendMessage(Variables::Prefix . "§aYou have turned off maintenance");

                            $this->plugin->getServer()->getNetwork()->setName(Variables::Motd);

                            $webhook = new Webhook("{$w}");
                            $embed = new Embed();
                            $embed->setTitle("Server Status - {$r}");
                            $embed->setDescription("QuzaNetwork is now turned off maintenance");
                            $embed->setFooter("QuadriumMC & QuanMC");
                            $embed->setTimestamp(new \DateTime("now"));
                            $embed->setColor(0x4EFA03);
                            $message = new Message();
                            $message->addEmbed($embed);
                            $webhook->send($message);

                            break;

    {
               //if (!$sender instanceof Player) return;
        if (!$sender->hasPermission("console", "quza.staff")){
            $sender->sendMessage(Variables::Prefix . "§cYou dont have permission to perform this command!");
            return;
        }

        if (isset($args[0])){
            switch ($args[0]){
                case "setkit":
                    if (!isset($args[1]) && !isset($args[2])) {
                        $sender->sendMessage("/core setkit <Player> <Kit>");
                        return;
                       }

                $sender->setGamemode(GameMode::SURVIVAL());
                $sender->getHungerManager()->setFood(20);
                $sender->getHungerManager()->setEnabled(false);
                $sender->setMaxHealth(20);
                $sender->setHealth(20);
                $sender->getInventory()->setHeldItemIndex(0);
                $sender->getEffects()->clear();
                $sender->sendMessage(Variables::Prefix . "§aYour Kit has been changed to ‘L’");
            
                           PlayerManager::sendLKit($sender);
                    $sender->sendMessage(Variables::Prefix . "§aSuccessfully changed {$player->getName()}'s kit to the ‘L’ kit");
                       
                    break;
            }
        }
    }
}
                    if (isset($args[0])){
            switch($args[0]){
                case "help":
                    $sender->sendMessage("- /core setrank <Name> <Rank>\n- /core mm off/on");
                    $sender->sendMessage("- /core givecoin <Name> <Coin>");
                    $sender->sendMessage("- /core setkit <Name> <Kit>");
           }
                    }
            }
        }

                 
           }
        }
     
  
