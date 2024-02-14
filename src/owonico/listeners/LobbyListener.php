<?php

namespace owonico\listeners;

/*---------------------------------
basic pocketmine uses
---------------------------------*/

use owonico\crates\CratesManager;
use owonico\Main;
use owonico\manager\FormManager;
use pocketmine\block\Chest;
use pocketmine\block\EnderChest;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class LobbyListener implements Listener {

    public function onBlockSpread(BlockSpreadEvent $event){
        $event->cancel();
    }

    /*public function onExplode(ExplosionPrimeEvent $event){
        $event->cancel();
    }*/

    public function onClickCrates(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if(isset(Main::$crateSetup[$player->getName()])){

            if(!$block instanceof Chest && !$block instanceof EnderChest){
                $player->sendMessage("§cClick CHEST or ENDER CHEST to setup crate");
                return;
            }
            
            CratesManager::setCrateLocation($block->getPosition());
            unset(Main::$crateSetup[$player->getName()]);
            return;
        }
        
        if($block->getPosition()->equals(CratesManager::getCrateLocation())){
            if($block instanceof Chest || $block instanceof EnderChest){
                $event->cancel();
                $player->sendForm(FormManager::getCrateForm());
            }
        }
    }

}
