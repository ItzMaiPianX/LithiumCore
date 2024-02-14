<?php

namespace owonico\manager;

/*---------------------------------
basic core uses
---------------------------------*/

use owonico\Main;
use owonico\query\async\AsyncQuery;
use owonico\provider\MySQL;
use owonico\rank\Rank;

/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class RankManager {

    /** @var Rank[] */
    public static $ranks = [];

    public static function init() {
        $ranks = [
            new Rank("Owner", "§c",["quza.builder", "quza.operator", "quza.staff", "pocketmine.command.gamemode", "pocketmine.command.teleport", "pocketmine.command.kick", "core.nick"], "§c "),
            new Rank("Manager", "§5", [], "§5 "),
            new Rank("Developer", "§4 ", ["core.nick", "quza.staff"], "§4 "),
            new Rank("Admin", "§b", ["pocketmine.command.teleport", "pocketmine.command.kick", "staffchat.command", "blazinfly.command", "cucumber.command.history", "cucumber.command.vanish", "cucumber.warn", "cucumber.command.ban", "cucumber.command.banlist", "cucumber.command.ipban", "cucumber.command.uban", "cucumber.command.ipbanlist", "cucumber.command.warn", "cucumber.command.warnings", "cucumber.command.delwarn", "core.nick", "staffmenu.cmd", "mod.command", "cucumber.command.unmute", "cucumber.command.mutelist", "cucumber.command.mute"], "§b "),
            new Rank("Sr.Mod", "§6", ["pocketmine.command.teleport", "pocketmine.command.kick", "staffchat.command", "blazinfly.command", "cucumber.command.history", "cucumber.command.vanish", "cucumber.warn", "cucumber.command.ban", "cucumber.command.banlist", "cucumber.command.warn", "cucumber.command.warnings", "cucumber.command.delwarn", "core.nick", "staffmenu.cmd", "mod.command", "cucumber.command.unmute", "cucumber.command.mutelist", "cucumber.command.mute"], "§6"),
            new Rank("Mod", "§6", ["pocketmine.command.teleport", "staffchat.command", "blazinfly.command", "cucumber.command.history", "cucumber.command.vanish", "cucumber.warn", "cucumber.ban", "cucumber.command.ban", "cucumber.command.banlist", "cucumber.command.warn", "cucumber.command.warnings", "cucumber.command.delwarn", "cucumber.mute", "staffmenu.cmd", "mod.command", "cucumber.command.unmute", "cucumber.command.mutelist", "cucumber.command.mute"], "§6 "),

            new Rank("Helper", "§e", ["pocketmine.command.teleport", "staffchat.command", "blazinfly.command", "cucumber.command.history", "cucumber.command.vanish", "cucumber.warn", "cucumber.mute", "staffmenu.cmd", "mod.command", "cucumber.command.unmute", "cucumber.command.mutelist", "cucumber.command.mute"], "§e "),
            new Rank("H-Builder", "§1", ["buildertools.command"], "§1 "),
            new Rank("Builder", "§9", ["quza.builder", "quza.staff", "buildertools.command"], "§9 "),


            new Rank("LTX+", "§a", ["blazinfly.command", "core.nick"], "§r "),
            new Rank("LTX", "§a", ["blazinfly.command"], "§r "),
            new Rank("Famous", "§r", ["quza.naga", "quza.mvp", "core.nick"], "§c "),
            new Rank("Media", "§1", ["quza.mvp", "core.nick"], "§1 "),
            new Rank("Voter", "§r", ["quza.voter"], "§r"),
            new Rank("Player", "§7", ["quza.player"], "§7 ")
        ];

        foreach ($ranks as $rank) {
            self::$ranks[strtolower($rank->getName())] = $rank;
        }
    }

    public static function setPlayerRank(Player $player, string $rank) {
        /** @var Rank|null $rankClass */
        $rankClass = self::$ranks[strtolower($rank)] ?? null;
        if($rankClass === null) {
            $player->kick("Invalid rank ($rank)");
            Main::getInstance()->getLogger()->info("§cReceived invalid rank ($rank)");
            return;
        }
        //$rankCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Rank.yml", Config::YAML);

        //$rankCfg->set($player->getXuid(), $rankClass->getName());
        //$rankCfg->save();

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UData SET Rank=" . $rankClass->getName() . " WHERE Name=" . $player->getName()));
        MySQL::getDatabase()->query("UPDATE UData SET Rank='" . (string) $rankClass->getName() . "' WHERE Name='" . $player->getName() . "';");

        Main::$userdata[$player->getName()]["Rank"] = $rankClass->getName();

        $player->recalculatePermissions();
        foreach ($rankClass->getPermissions() as $permission) {
            $player->addAttachment(Main::getInstance(), $permission, true);
        }
    }

    public static function saveVoteTime(Player $player) {
        //QueryQueue::submitQuery(new UpdateRowQuery(["HasVoted" => 1, "VoteDate" => time()], "Name", $player->getName()));
        //TODO

        $voterCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Voter.yml", Config::YAML);
        $voterCfg->set($player->getName(), time());
        $voterCfg->save();

        if (self::getPlayerRank($player)->getName() == "Player") {
            self::setPlayerRank($player, "Voter");
        }
    }

    public static function hasVoted(Player $player): bool {
        return self::getPlayerRank($player)->getName() == "Voter";
    }

    public static function checkRankExpiration(Player $player, int $voteTime) {
        if(self::getPlayerRank($player)->getName() != "Voter") {
            return;
        }
        if($voteTime + 86400 >= time()) {
            return;
        }

        $player->sendMessage("§e§l§oRANKS:§r§f:§b Your VOTER rank expired. Vote again to extend it.");
        if(self::getPlayerRank($player)->getName() == "Voter") {
            self::setPlayerRank($player, "Player");
        }

        $voterCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Voter.yml", Config::YAML);
        $voterCfg->remove($player->getName());
        $voterCfg->save();

        //QueryQueue::submitQuery(new UpdateRowQuery(["HasVoted" => 0], "Name", $player->getName()));
    }

    public static function getPlayerRank(Player $player): Rank {
        //$rankCfg = new Config(Main::getInstance()->getDataFolder() . "rank/Rank.yml", Config::YAML);
        //if (!$rankCfg->exists($player->getXuid())){
        //    self::setPlayerRank($player, "Player");
        //}
        
        return self::$ranks[strtolower((string) Main::$userdata[$player->getName()]["Rank"] ?? "Player")] ?? self::$ranks["player"];
    }

    public static function getRankByName(string $rank): ?Rank {
        return self::$ranks[strtolower($rank)] ?? null;
    }
}
