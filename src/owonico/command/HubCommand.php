<?php

namespace owonico\command;

use owonico\{Main, manager\FormManager, Variables};
use owonico\manager\PlayerManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

class HubCommand extends Command
{

    public $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("hub", "Back to hub");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player){
            $combat = PlayerManager::$combatOpponent[$sender->getName()] ?? false;
            if(!$combat){

                $location = $this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
                $sender->teleport($location);
                $sender->setGamemode(GameMode::ADVENTURE());
                $sender->getHungerManager()->setFood(20);
                $sender->getHungerManager()->setEnabled(false);
                $sender->setMaxHealth(20);
                $sender->setHealth(20);
                $sender->getInventory()->setHeldItemIndex(0);
                $sender->getEffects()->clear();
                $sender->sendMessage(Variables::Prefix . "§aYou have been teleport to hub!");

                Main::$playerArena[$sender->getName()] = "Lobby";

                PlayerManager::sendLobbyKit($sender);
            } else {
                $sender->sendMessage(Variables::Prefix . "§cYou can't go to lobby when combat!");
            }
        }
    }
}