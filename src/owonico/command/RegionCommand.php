<?php

namespace owonico\command;

use owonico\{Main, Variables};
use owonico\manager\FormManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

class RegionCommand extends Command
{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("region", "Region menu");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player){
            $sender->sendForm(FormManager::getRegionMenu($sender));
        }
    }
}