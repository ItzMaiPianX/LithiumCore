<?php

namespace owonico\command;

use owonico\Main;
use owonico\manager\PlayerManager;
use owonico\manager\RankManager;
use owonico\Variables;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class NickCommand extends Command{

    public function __construct()
    {
        parent::__construct("nick", "Nick your name");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player){

            if($sender->hasPermission("core.nick")){
                
                if(isset($args[0])){

                    $name = $args[0];

                    if(strlen($name) <= 3){
                        $sender->sendMessage(Variables::Prefix . "§cAt least 4 characters needed for the name");
                        return;
                    }

                    if(in_array($name, PlayerManager::$blockedNick)){
                        $sender->sendMessage(Variables::Prefix . "§cThis name is not allowed! Please use another name.");
                        return;
                    }

                    PlayerManager::$nickedplayer[$sender->getName()] = true;
                    PlayerManager::$nickedname[$sender->getName()] = $name;
                    PlayerManager::$nickedRank[$sender->getName()] = RankManager::getPlayerRank($sender)->getName();
                    PlayerManager::$nickedPermissions[$sender->getName()] = RankManager::getPlayerRank($sender)->getPermissions();

                    $sender->sendMessage(Variables::Prefix . "§aNicked your name to " . $name);

                    RankManager::setPlayerRank($sender, "Player");
                    $sender->recalculatePermissions();
                    
                    foreach(PlayerManager::$nickedPermissions[$sender->getName()] as $permission){
                        $sender->addAttachment(Main::getInstance(), $permission, true);
                    }
                    
                } else {
                    $sender->sendMessage(Variables::Prefix . "§a/nick <Name>");
                }
            } else {
                $sender->sendMessage(Variables::Prefix . "§cSorry, but you need LTX+ rank to use this command");
            }
        }
    }
}