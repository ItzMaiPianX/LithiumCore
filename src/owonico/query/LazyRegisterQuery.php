<?php

namespace owonico\query;

use owonico\provider\MySQL;
use owonico\query\async\AsyncQuery;
use pocketmine\player\Player;
use pocketmine\Server;

class LazyRegisterQuery{

    public static function registerData(Player $player) {
        $database = MySQL::getDatabase();
        $name = $database->real_escape_string($player->getName());

        // Codes from CHATGPT

        $utagQuery = "SELECT Name FROM Tags WHERE Name='$name'";
        $utagResult = $database->query($utagQuery);

        if($utagResult->num_rows === 0){
            $ownedTag = base64_encode(serialize([]));
            $insertUTagQuery = "INSERT INTO Tags(Name, CurrentTag, OwnedTag) VALUES('$name', ' ', '$ownedTag')";

            if($database->query($insertUTagQuery) == true){
                Server::getInstance()->getLogger()->notice("Inserted UTag Data to player");
            } else {
                Server::getInstance()->getLogger()->warning("Error occured while inserting Tag Data");
            }
        }

        $udataQuery = "SELECT Name FROM UData WHERE Name='$name'";
        $udataResult = $database->query($udataQuery);

        if ($udataResult->num_rows === 0) {
            $insertUDataQuery = "INSERT INTO UData(Name, Kills, Death, Coin, Pass, Elo, Rank, Honour) VALUES('$name', 0, 0, 125, 0, 1000, 'Player', 0);";
            
            if($database->query($insertUDataQuery) == true){
                Server::getInstance()->getLogger()->info("Inserted data");
            } else {
                Server::getInstance()->getLogger()->info("Error occured when insert data");
            }
           
        }

        $ustdataQuery = "SELECT Name FROM USettings WHERE Name='$name'";
        $ustdataResult = $database->query($ustdataQuery);

        if ($ustdataResult->num_rows === 0) {
            $insertUSettingsQuery = "INSERT INTO USettings(Name, CpsCount, AutoSprint, Scoreboard, HitEffect, ArenaRespawn) VALUES('$name', 1, 0, 1, 0, 0);";
            $database->query($insertUSettingsQuery);
            
        }
        $eloQuery = "SELECT Name FROM Elo WHERE Name='$name'";
        $eloDataResult = $database->query($eloQuery);

        if ($eloDataResult->num_rows === 0) {
            $insertEloQuery = "INSERT INTO Elo(Name, Boxing, Bedfight, Bridge, Battlerush, BuildUHC, Midfight, Fist, Sumo, Nodebuff) VALUES('$name', 1000, 1000, 1000, 1000, 1000, 1000, 1000, 1000, 1000);";
            $database->query($insertEloQuery);
        }
    }
}
