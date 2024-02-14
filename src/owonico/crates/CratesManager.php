<?php 

namespace owonico\crates;

use owonico\Main;
use owonico\manager\CosmeticsManager;
use owonico\utils\Utils;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class CratesManager{

    public static array $reward = [

        "cape", "artifacts"

    ];

    public static function getCrateRewards(Player $player){

        $reward = [];

        $num = rand(1, 1000);

        $type = self::$reward[array_rand(self::$reward)];

        if($num >= 1000){
            if($type == "cape"){
                if(!CosmeticsManager::ownedCape($player, "Turtle")){
                    $config = CosmeticsManager::getConfigFromCapeName("Turtle");

                    $config->set($player->getXuid(), true);
                    $config->save();

                    $reward = [
                        "Turtle Cape" => "Rare"
                    ];
                } else {
                    self::getCrateRewards($player);
                    return;
                }
            } elseif($type == "artifacts"){
                if(!CosmeticsManager::ownedArtifact($player, "Angel_1")){
                    CosmeticsManager::addOwnedArtifact($player, "Angel_1");

                    $reward = [
                        "Angel artifact" => "Rare"
                    ];
                } else {
                    self::getCrateRewards($player);
                    return;
                }
            }
        } elseif($num >= 500){
            if($type == "cape"){
                if(!CosmeticsManager::ownedCape($player, "Red Creeper")){
                    $config = CosmeticsManager::getConfigFromCapeName("Red Creeper");

                    $config->set($player->getXuid(), true);
                    $config->save();

                    $reward = [
                        "Red Creeper Cape" => "Rare"
                    ];
                } else {
                    self::getCrateRewards($player);
                    return;
                }
            } elseif($type == "artifacts"){
                if(!CosmeticsManager::ownedArtifact($player, "collar")){
                    CosmeticsManager::addOwnedArtifact($player, "collar");

                    $reward = [
                        "Collar artifact" => "Rare"
                    ];
                } else {
                    self::getCrateRewards($player);
                    return;
                }
            }
        } elseif($num >= 200){
            if($type == "cape"){
                if(!CosmeticsManager::ownedCape($player, "Fire")){
                    $config = CosmeticsManager::getConfigFromCapeName("Fire");

                    $config->set($player->getXuid(), true);
                    $config->save();

                    $reward = [
                        "Fire Cape" => "Common"
                    ];
                } else {
                    self::getCrateRewards($player);
                    return;
                }
            } elseif($type == "artifacts"){
                if(!CosmeticsManager::ownedArtifact($player, "Jetpack")){
                    CosmeticsManager::addOwnedArtifact($player, "Jetpack");

                    $reward = [
                        "Jetpack" => "Rare"
                    ];
                } else {
                    self::getCrateRewards($player);
                    return;
                }
            }
        } elseif($num >= 0){

            $capearr = ["Energy", "Enderman", "Blue Creeper"];
            $cape = $capearr[array_rand($capearr)];

            if($type == "cape"){
                if(!CosmeticsManager::ownedCape($player, $cape)){
                    $config = CosmeticsManager::getConfigFromCapeName($cape);

                    $config->set($player->getXuid(), true);
                    $config->save();

                    $reward = [
                        $cape => "Common"
                    ];
                } else {
                    self::getCrateRewards($player);
                    return;
                }
            }
        }

        return $reward;
    }

    public static function getCrateLocation(): Vector3{
        $crateConfig = Main::$cratesConfig;

        $locationData = $crateConfig->get("location");

        return Utils::stringToVector(":", $locationData);
    }

    public static function setCrateLocation(Vector3 $location){
        $crateConfig = Main::$cratesConfig;

        $crateConfig->set("location", Utils::vectorToString($location));
        $crateConfig->save();    
    }
}