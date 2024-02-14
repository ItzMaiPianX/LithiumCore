<?php

namespace owonico\command;

use owonico\{Main, Variables};
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;

class PingCommand extends Command{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct('ping', "Check your ping");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) return;

        $sender->sendMessage(Variables::Prefix . "§bYour Ping:§f " . $sender->getNetworkSession()->getPing() . "ms");
    }
}