<?php

namespace owonico\egghunt;

use owonico\entity\EggHuntEntity;
use owonico\Main;
use owonico\utils\Converter;
use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class EggHuntManager{

    public static Config $pointsData;
    public static Config $huntedData;
    public static Config $eggHuntData;

    public static array $points = [];
    public static array $hunted = [];

    public static string $egghuntSkin;
    public static string $egghuntGeo = "";

    public static function init(){
        self::$pointsData = new Config(Main::getInstance()->getDataFolder() . "egghunt/Points.yml", Config::YAML);
        self::$huntedData = new Config(Main::getInstance()->getDataFolder() . "egghunt/Hunted.yml", Config::YAML);
        self::$eggHuntData = new Config(Main::getInstance()->getDataFolder() . "egghunt/Data.yml", Config::YAML);

        self::$egghuntSkin = Converter::getPngSkin(Main::getInstance()->getDataFolder() . "egghunt/EggHunt.png");
        self::$egghuntGeo = file_get_contents(Main::getInstance()->getDataFolder() . "egghunt/EggHuntGeo.json");

        if(!self::$eggHuntData->exists("EggCount")){
            self::$eggHuntData->set("EggCount", 0);
            self::$eggHuntData->save();
        }

    }

    public static function initLogin(Player $player){
        
        if(!self::$pointsData->exists($player->getXuid())){
            self::$pointsData->set($player->getXuid(), 0);
            self::$pointsData->save();
        }

        if(!self::$huntedData->exists($player->getXuid())){
            self::$huntedData->set($player->getXuid(), serialize([]));
            self::$huntedData->save();
        }

        self::$points[$player->getName()] = self::$pointsData->get($player->getXuid());
        self::$hunted[$player->getName()] = unserialize(self::$huntedData->get($player->getXuid()));

        
        self::handleSpawnEggHunt($player);

    }

    public static function handleQuit(Player $player){
        if(!isset(self::$points[$player->getName()])) return;

        self::$pointsData->set($player->getXuid(), self::$points[$player->getName()]);
        self::$pointsData->save();

        self::$huntedData->set($player->getXuid(), serialize(self::$hunted[$player->getName()]));
        self::$huntedData->save();
    }

    public static function handleSpawnEggHunt(Player $player){
        foreach($player->getWorld()->getEntities() as $entity){
            if($entity instanceof EggHuntEntity){
                $id = (int)$entity->getNameTag();

                if(in_array($id, self::$hunted[$player->getName()])){
                    $entity->despawnFrom($player);
                }
            }
        }
    }
}