<?php

namespace owonico\manager;

use owonico\Main;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class CosmeticsManager{


    public static function getCapeFormText(Player $player, string $capeName){
        $ownedPath = Main::getInstance()->getDataFolder() . "owned/";
        
        $bluecreeper = new Config($ownedPath . "BlueCreeperCape.yml", Config::YAML); //1500
        $enderman = new Config($ownedPath . "EndermanCape.yml", Config::YAML); //2000
        $energy = new Config($ownedPath . "EnergyCape.yml", Config::YAML); //2300
        $fire = new Config($ownedPath . "FireCape.yml", Config::YAML); //2500
        $redcreeper = new Config($ownedPath . "RedCreeperCape.yml", Config::YAML); //3000
        $turtle = new Config($ownedPath . "TurtleCape.yml", Config::YAML); // 3300

        switch($capeName){
            case "Blue Creeper":
                $status = $bluecreeper->exists($player->getXuid()) ? "" : "§7Common";

                return "Blue Creeper {$status}";
                break;
            case "Enderman":
                $status = $enderman->exists($player->getXuid()) ? "" : "§7Common";
    
                return "Enderman {$status}";
                break;    
            case "Energy":
                $status = $energy->exists($player->getXuid()) ? "" : "§7Common";
    
                return "Energy {$status}";
                break;    
            case "Fire":
                $status = $fire->exists($player->getXuid()) ? "" : "§7Common";
    
                return "Fire {$status}";
                break;
            case "Red Creeper":
                $status = $redcreeper->exists($player->getXuid()) ? "" : "§bRare";
    
                return "Red Creeper {$status}";
                break;
            case "Turtle":
                $status = $turtle->exists($player->getXuid()) ? "" : "§bRare";
    
                return "Turtle {$status}";
                break;    
                
            default: 
                return $capeName;
                break;    
        }

    }

    public static function ownedArtifact(Player $player, string $artifact){
        $ownedPath = Main::getInstance()->getDataFolder() . "owned/";

        $config = new Config($ownedPath . $artifact . ".yml", Config::YAML);

        return $config->exists($player->getXuid());
    }

    public static function addOwnedArtifact(Player $player, string $artifact){
        $ownedPath = Main::getInstance()->getDataFolder() . "owned/";

        $config = new Config($ownedPath . $artifact . ".yml", Config::YAML);

        $config->set($player->getXuid(), true);
        $config->save();
    }

    public static function ownedCape(Player $player, string $capeName){
        $ownedPath = Main::getInstance()->getDataFolder() . "owned/";
        
        $bluecreeper = new Config($ownedPath . "BlueCreeperCape.yml", Config::YAML); //1500
        $enderman = new Config($ownedPath . "EndermanCape.yml", Config::YAML); //2000
        $energy = new Config($ownedPath . "EnergyCape.yml", Config::YAML); //2300
        $fire = new Config($ownedPath . "FireCape.yml", Config::YAML); //2500
        $redcreeper = new Config($ownedPath . "RedCreeperCape.yml", Config::YAML); //3000
        $turtle = new Config($ownedPath . "TurtleCape.yml", Config::YAML); // 3300

        switch($capeName){
            case "Blue Creeper":
                return $bluecreeper->exists($player->getXuid());

                break;
            case "Enderman":
                return $enderman->exists($player->getXuid());
    
                break;    
            case "Energy":
                return $energy->exists($player->getXuid());
    
                break;    
            case "Fire":
                return $fire->exists($player->getXuid());
    
                break;
            case "Red Creeper":
                return $redcreeper->exists($player->getXuid());
    
                break;
            case "Turtle":
                return $turtle->exists($player->getXuid());
    
                break;    
            default: 
                return true;
                break;    
        }
    }

    public static function getConfigFromCapeName(string $capeName){
        $ownedPath = Main::getInstance()->getDataFolder() . "owned/";
        
        $bluecreeper = new Config($ownedPath . "BlueCreeperCape.yml", Config::YAML); //1500
        $enderman = new Config($ownedPath . "EndermanCape.yml", Config::YAML); //2000
        $energy = new Config($ownedPath . "EnergyCape.yml", Config::YAML); //2300
        $fire = new Config($ownedPath . "FireCape.yml", Config::YAML); //2500
        $redcreeper = new Config($ownedPath . "RedCreeperCape.yml", Config::YAML); //3000
        $turtle = new Config($ownedPath . "TurtleCape.yml", Config::YAML); // 3300

        switch($capeName){
            case "Blue Creeper":
                return $bluecreeper;

                break;
            case "Enderman":
                return $enderman;
    
                break;    
            case "Energy":
                return $energy;
    
                break;    
            case "Fire":
                return $fire;
    
                break;
            case "Red Creeper":
                return $redcreeper;
    
                break;
            case "Turtle":
                return $turtle;
    
                break;    
            default: 
                return null;
                break;    
        }
    }

    public static function getPriceFromCapeName(string $capeName){

        switch($capeName){
            case "Blue Creeper":
                return 1500;

                break;
            case "Enderman":
                return 2000;
    
                break;    
            case "Energy":
                return 2300;
    
                break;    
            case "Fire":
                return 2500;
    
                break;
            case "Red Creeper":
                return 3000;
    
                break;
            case "Turtle":
                return 3300;
    
                break;    
            default: 
                return 0;
                break;    
        }
    }

    public static function getPriceColor(Player $player, int $price){

        $coin = PlayerManager::getPlayerCoin($player);

        if($coin >= $price){
            return "§a";
        } else {
            return "§c";
        } 
    }
} 