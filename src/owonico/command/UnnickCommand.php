<?php

namespace owonico\command;

use owonico\manager\PlayerManager;
use owonico\manager\RankManager;
use owonico\Variables;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class UnnickCommand extends Command {

    public function __construct()
    {
        parent::__construct("unnick", "Reset the nick to the default");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player){

            if($sender->hasPermission("core.nick")){
                
                if(isset(PlayerManager::$nickedplayer[$sender->getName()])){

                    RankManager::setPlayerRank($sender, PlayerManager::$nickedRank[$sender->getName()]);

                    unset(PlayerManager::$nickedname[$sender->getName()]);
                    unset(PlayerManager::$nickedplayer[$sender->getName()]);
                    unset(PlayerManager::$nickedRank[$sender->getName()]);
                    unset(PlayerManager::$nickedPermissions[$sender->getName()]);

                    $sender->setDisplayName($sender->getName());

                    $sender->sendMessage(Variables::Prefix . "§aResetted your nickname");
                } else {
                    $sender->sendMessage(Variables::Prefix . "§cYou are not nicked yet");
                }
                
            } else {
                $sender->sendMessage(Variables::Prefix . "§cSorry, but you need LTX+ rank to use this command");
            }
        }
    }
}