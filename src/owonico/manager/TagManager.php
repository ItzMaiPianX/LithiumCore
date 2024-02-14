<?php

namespace owonico\manager;

/*---------------------------------
basic core uses
---------------------------------*/

use owonico\Main;
use owonico\query\async\AsyncQuery;
use owonico\provider\MySQL;
use owonico\rank\Rank;
use owonico\tag\Tag;
/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class TagManager {

    /** @var Rank[] */
     public static $tags = [];

     public static array $ownedTags;
     public static array $currentTag;

     public static function init() {
        $tags = [
            new Tag("E-Boy", "§r[§bE-Boy§r]"),
            new Tag("E-Girl", "§r[§dE-Girl§r]"),
            new Tag("#1", "§r[§1#1§r]"),
            new Tag("Vampire", "§r[§4Vampire!§r]"),
            new Tag("Pookie", "§r[§5Pookie§r]"),
            new Tag("Mommy", "§r[§dMOMMY§r]"),
            new Tag("Daddy", "§r[§2DADDY§r]"),
            new Tag("W", "§8[§4W§8]")
          ];
        
        foreach ($tags as $tag) {
            self::$tags[strtolower($tag->getName())] = $tag;
        }
    }
    
    public static function addOwnedTag(Player $player, string $tag){
        $tagClass = self::$tags[strtolower($tag)] ?? null;
        if($tagClass === null) {
            $player->kick("Invalid tag ($tag)");
            Main::getInstance()->getLogger()->info("§cReceived invalid rank ($tag)");
            return;
        }

        $ownedTags = TagManager::$ownedTags[$player->getName()];
        
        $ownedTags[] = $tag;
        TagManager::$ownedTags[$player->getName()] = $ownedTags;

        $serializedData = (string)serialize($ownedTags);
        MySQL::getDatabase()->query("UPDATE Tags SET OwnedTag='" . base64_encode($serializedData) ."' WHERE Name='" . $player->getName() . "';");

    }

    public static function setPlayerCurrentTag(Player $player, string $tag) {
        /** @var Rank|null $rankClass */
        $tagClass = self::$tags[strtolower($tag)] ?? null;
        if($tagClass === null) {
            $player->kick("Invalid tag ($tag)");
            Main::getInstance()->getLogger()->info("§cReceived invalid rank ($tag)");
            return;
        }
        //$rankCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Rank.yml", Config::YAML);

        //$rankCfg->set($player->getXuid(), $rankClass->getName());
        //$rankCfg->save();

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UData SET Rank=" . $rankClass->getName() . " WHERE Name=" . $player->getName()));
        MySQL::getDatabase()->query("UPDATE Tags SET CurrentTag='" . (string) $tagClass->getName() . "' WHERE Name='" . $player->getName() . "';");

        TagManager::$currentTag[$player->getName()] = $tagClass->getName();
 
        
        }
    

    public static function getPlayerCurrentTag(Player $player): ?Tag {
        //$rankCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Rank.yml", Config::YAML);
        //if (!$rankCfg->exists($player->getXuid())){
        //    self::setPlayerRank($player, "Player");
        //}
        
        $tag = TagManager::$currentTag[$player->getName()];

        if($tag == " ") return null;

        return self::$tags[strtolower((string)$tag)] ?? null;

    }

    public static function ownedTag(Player $player, string $tag): bool {
        
        $ownedTags = TagManager::$ownedTags[$player->getName()];

        return in_array($tag, $ownedTags);
    }

     public static function getTagByName(string $tag): ?Tag {
        return self::$tags[strtolower($tag)] ?? null;
    
    }

}