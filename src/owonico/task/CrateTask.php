<?php

namespace owonico\task;

use owonico\crates\CratesManager;
use owonico\Variables;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ChestOpenSound;
use pocketmine\world\sound\XpLevelUpSound;

class CrateTask extends Task{

    public Player $player;
    public int $timer = 0;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function onRun(): void{

        $timer = $this->timer;
        $player = $this->player;
        
        $reward = [];

        if($timer == 1){
            $player->getWorld()->addSound($player->getLocation(), new ChestOpenSound());
            $player->getArmorInventory()->setHelmet(VanillaBlocks::PUMPKIN()->asItem());
            
            $reward = CratesManager::getCrateRewards($player);
        }

        $dot = ".";
        if($timer >= 2 && $timer <= 5){
            $player->sendTitle("§aLoading" . $dot);
            
            $dot .= ".";
        }

        if($timer == 6){
            $player->getArmorInventory()->clearAll();
            $player->getWorld()->addSound($player->getLocation(), new XpLevelUpSound(5));

            foreach($reward as $name => $rarity){
                $player->sendMessage(Variables::Prefix . "§aYou got " . $name . " in this crate! §5[Rarity: §b{$rarity}]"); //multiple reward supported
            }
        }

        if($timer == 7){
            $player->getWorld()->addSound($player->getLocation(), new ChestCloseSound());
            $this?->getHandler()?->cancel();
        }

        $this->timer++;
    }
}