<?php

namespace owonico\command;

use owonico\{Main, Variables};
use owonico\manager\FormManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ReportCommand extends Command{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("report", "Report player");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) return;

        $sender->sendForm(FormManager::getReportForm($sender));
    }
}
