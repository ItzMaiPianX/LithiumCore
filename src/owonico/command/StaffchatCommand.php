<?php

namespace owonico\command;

use owonico\{Main, Variables};
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class StaffchatCommand extends Command{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("staffchat", "Staffchat commmand");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player){
            if (!isset($args)){
                $sender->sendMessage("§cPls provide a message");
            }
            if ($sender->hasPermission("quza.staff")){
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $staffs){
                    $message = implode(" ", $args);
                    if ($staffs->hasPermission("quza.staff")) {
                        $staffs->sendMessage("§l§bSTAFFCHAT§r ({$sender->getName()})§l§8 : §r{$message}");
                    }
                }
            } else{
                $sender->sendMessage(Variables::Prefix . "§cYou dont have permission to perform this command!");
            }
        } else{
            $sender->sendMessage("Run this in game");
        }
    }
}
