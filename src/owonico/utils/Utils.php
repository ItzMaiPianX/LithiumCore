<?php

namespace owonico\utils;

/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\Server;
use pocketmine\world\Position;

class Utils{

    /**
     * @param Vector3 $vector
     * @return string
     */
    public static function vectorToString(Vector3 $vector): string{
        return $vector->getX() . ":" . $vector->getY() . ":" . $vector->getZ();
    }

    /**
     * @param string $delimeter
     * @param string $string
     * @return Vector3
     */
    public static function stringToVector(string $delimeter, ?string $string): ?Vector3
    {
        if($string !== null) {
            $split = explode($delimeter, $string);
            return new Vector3($split[0], $split[1], $split[2]);
        }
        return null;
    }

    public static function getBoolean(int $a){
        return $a >= 1;
    }
    
    public static function randomTeleport(Player $player){
        $world = Server::getInstance()->getWorldManager()->getWorldByName("ffa_skywars");
        switch(mt_rand(1,9)){
               case 1:
                   $player->teleport(new Position(227, 85, 266, $world));
               break;
               case 2:
                   $player->teleport(new Position(276, 84, 257, $world));
               break;
               case 3:
                   $player->teleport(new Position(319, 91, 259, $world));
               break;
               case 4:
                   $player->teleport(new Position(243, 92, 338, $world));
               break;
               case 5:
                   $player->teleport(new Position(226, 85, 266, $world));
               break;
               case 6:
                   $player->teleport(new Position(253, 69, 270, $world));
               break;
               case 7:
                   $player->teleport(new Position(253, 71, 244, $world));
               break;
               case 8:
                   $player->teleport(new Position(241, 79, 241, $world));
               break;
               case 9:
                   $player->teleport(new Position(203, 94, 240, $world));
               break;
        }
    }
}
