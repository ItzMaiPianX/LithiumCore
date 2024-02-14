<?php

namespace owonico\manager;

use owonico\Main;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class PassManager{

    public static function getPassColour(Player $player){

        $honour = PlayerManager::getPlayerHonour($player);
    
        $color = "";
        if($honour >= 0){
            $color = "§r";
        }
        
        if($honour >= 10000){
            $color = "§r";
        }
        if($honour >= 25000){
            $color = "§r";
        }
        if($honour >= 50000){
            $color = "§r";
        }
        if($honour >= 75000){
            $color = "§r";
        }
        if($honour >= 100000){
            $color = "§r";
        }
        return $color;
        
    } //tree wtf is this

    public static function getTierDisplay(Player $player){
        $honour = PlayerManager::getPlayerHonour($player);

        // if（bought elite）
        // return "Elite"
        
        $tier = "";
        if($honour >= 0){
            $tier = "[§7Bronze§r]";
        }
        if($honour >= 10000){
            $tier = "[§6Gold§r]";
        }
        if($honour >= 25000){
            $tier = "[§eCHAMPION§r]";
        }
        if($honour >= 50000){
            $tier = "[§cMASTER§r]";
        }
        if($honour >= 75000){
            $tier = "[§4Grandmaster§r]";
        }
        if($honour >= 100000){
            $tier = "[§4Heroic§r]";
        }
        
        return $tier;
    }
    public static function getColour(Player $player){
        $honour = PlayerManager::getPlayerHonour($player);
        
        $colour = "";
        if($honour >= 10000){
            $tier = "§a";
        }
        if($honour >= 25000){
            $tier = "§a";
        }
        if($honour >= 50000){
            $tier = "§a";
        }
        if($honour >= 75000){
            $tier = "§a";
        }
        if($honour >= 100000){
            $tier = "§a";
        }
        return $tier;
    }
    public static function getPassCoin(Player $player){
        $honour = PlayerManager::getPlayerHonour($player);
        
        if($honour >= 1000){
            PlayerManager::addPlayerCoin($player, 150);
        }
        if($honour >= 2500){
            PlayerManager::addPlayerCoin($player, 500);
        }
        if($honour >= 7500){
            PlayerManager::addPlayerCoin($player, 1500);
        }
        if($honour >= 12500){
            PlayerManager::addPlayerCoin($player, 3000);
        }
        if($honour >= 17500){
            PlayerManager::addPlayerCoin($player, 5000);
        }
        if($honour >= 20000){
            PlayerManager::addPlayerCoin($player, 10000);
        }
        if($honour >= 30000){
            PlayerHonour::addPlayerCoin($player, 12500);
        }
        if($honour >= 40000){
            PlayerManager::addPlayerCoin($player, 15000);
        }
        if($honour >= 60000){
            PlayerManager::addPlayerCoin($player, 20000);
        }
        if($honour >= 80000){
            PlayerManager::addPlayerCoin($player, 35000);
        }
    }
}

