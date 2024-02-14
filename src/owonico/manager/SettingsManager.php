<?php

namespace owonico\manager;

use owonico\Main;
use owonico\query\async\AsyncQuery;
use owonico\utils\Utils;
use pocketmine\player\Player;
use pocketmine\Server;
use owonico\provider\MySQL;

class SettingsManager{


    public static function setCpsEnabled(Player $player, bool $value){

        if ($value){
            $enabled = 1;
        } else{
            $enabled = 0;
        }

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UserSettings SET CpsCount=". $enabled . " WHERE Name='" . $player->getName() . "';"));
        MySQL::getDatabase()->query("UPDATE USettings SET CpsCount=". $enabled . " WHERE Name='" . $player->getName() . "';");

        Main::$usersettings[$player->getName()]["Cps"] = $enabled;

    }

    public static function getCpsEnabled(Player $player){

        
        return Utils::getBoolean(Main::$usersettings[$player->getName()]["Cps"]);

    }

    public static function setAutoSprintEnabled(Player $player, bool $value){

        if ($value){
            $enabled = 1;
        } else{
            $enabled = 0;
        }

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UserSettings SET AutoSprint=". $enabled . " WHERE Name='" . $player->getName() . "';"));
        MySQL::getDatabase()->query("UPDATE USettings SET AutoSprint=". $enabled . " WHERE Name='" . $player->getName() . "';");

        Main::$usersettings[$player->getName()]["AutoSprint"] = $enabled;

    }

    public static function getAutoSprintEnabled(Player $player){
        
        return Utils::getBoolean(Main::$usersettings[$player->getName()]["AutoSprint"]);

    }

    public static function setScoreboardEnabled(Player $player, bool $value){

        if ($value){
            $enabled = 1;
        } else{
            $enabled = 0;
        }

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UserSettings SET Scoreboard=". $enabled . " WHERE Name='" . $player->getName() . "';"));
        MySQL::getDatabase()->query("UPDATE USettings SET Scoreboard=". $enabled . " WHERE Name='" . $player->getName() . "';");

        Main::$usersettings[$player->getName()]["Scoreboard"] = $enabled;
    }

    public static function getScoreboardEnabled(Player $player){
        
        return Utils::getBoolean(Main::$usersettings[$player->getName()]["Scoreboard"]);

    }

    public static function setHitEffectEnabled(Player $player, bool $value){

        if ($value){
            $enabled = 1;
        } else{
            $enabled = 0;
        }

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UserSettings SET HitEffect=". $enabled . " WHERE Name='" . $player->getName() . "';"));
        MySQL::getDatabase()->query("UPDATE USettings SET HitEffect=". $enabled . " WHERE Name='" . $player->getName() . "';");

        Main::$usersettings[$player->getName()]["HitEffect"] = $enabled;
    }
    
     public static function getHitEffectEnabled(Player $player){
        
        return Utils::getBoolean(Main::$usersettings[$player->getName()]["HitEffect"]);
     }    
    
    public static function setArenaRespawnEnabled(Player $player, bool $value){

       if ($value){
           $enabled = 1;
       } else{
           $enabled = 0;
       }

          //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UserSettings SET ArenaRespawn=". $enabled . " WHERE Name='" . $player->getName() . "';"));
      MySQL::getDatabase()->query("UPDATE USettings SET ArenaRespawn=". $enabled . " WHERE Name='" . $player->getName() . "';");
    
        Main::$usersettings[$player->getName()]["ArenaRespawn"] = $enabled;
    }
    
    public static function getArenaRespawnEnabled(Player $player){
        
        return Utils::getBoolean(Main::$usersettings[$player->getName()]["ArenaRespawn"]);

    }
}
