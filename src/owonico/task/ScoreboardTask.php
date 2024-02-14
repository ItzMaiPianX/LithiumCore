<?php

namespace owonico\task;

/*---------------------------------
basic core uses
---------------------------------*/

use owonico\Main;
use owonico\Variables;
use owonico\manager\FormManager;
use owonico\manager\PlayerManager;
use owonico\listeners\ServerListener;
use owonico\manager\RankManager;

/*---------------------------------
basic libs uses
---------------------------------*/

use maipian\scoreboard\Scoreboard;
use SpekledFrog\KillStreak\KillStreak;
//use vixikhd\duels\arena\Arena;

/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ScoreboardTask extends Task {

    public $plugin;
    public $player;

    public function __construct(Main $plugin, Player $player){
        $this->plugin = $plugin;
        $this->player = $player;
    }

    public function onRun(): void
    {
        $duel = $this->plugin->getServer()->getPluginManager()->getPlugin("Unranked-Sumo");
        $duel2 = $this->plugin->getServer()->getPluginManager()->getPlugin("Unranked-Fist");
        $duel3 = $this->plugin->getServer()->getPluginManager()->getPlugin("Unranked-Nodebuff");

        if($this->player == null){
            $this->getHandler()?->cancel();
        }

        if (!$this->player->isConnected()){
            $this->getHandler()?->cancel();
        }

        if ($this->player->isConnected()) {
            if (Main::$scoreboardEnabled[$this->player->getName()]) {
                $killstreak = KillStreak::getInstance()->getProvider()->getPlayerKSPoints($this->player);
                $kill = PlayerManager::getPlayerKill($this->player);
                $death = PlayerManager::getPlayerDeath($this->player);
                $elo = PlayerManager::getPlayerElo($this->player);
                $honour = PlayerManager::getPlayerHonour($this->player);
                $serverip = Variables::ServerIP;
                $coin = PlayerManager::getPlayerCoin($this->player);
                $cps = Main::getInstance()->getCPS($this->player);
                $region = Main::getInstance()->getConfig()->get("name");
                $r = Main::getInstance()->getConfig()->get("region");
                $queue = 0;//Arena::$queue + \vixikhd\duels2\arena\Arena::$queue2 + \vixikhd\duels3\arena\Arena::$queue3;
                $playing = Main::getWorldCount(Variables::Nodebuffffa) + Main::getWorldCount(Variables::Comboffa) + Main::getWorldCount(Variables::Sumoffa) + Main::getWorldCount(Variables::Fistffa) + Main::getWorldCount(Variables::Gappleffa) + Main::getWorldCount(Variables::Knockffa) + Main::getWorldCount(Variables::Resistanceffa) + Main::getWorldCount(Variables::Buildffa);

                $playerCount = count($this->plugin->getServer()->getOnlinePlayers());
                $combatTimer = PlayerManager::getCombatTimer($this->player);
                $rank = RankManager::getPlayerRank($this->player)->getDisplayFormat();
                $ping = $this->player !== null ? $this->player->getNetworkSession()->getPing() : 0;
                $theirping = PlayerManager::getOpponentsPing($this->player);//$this->player !== null ? Server::getInstance()->getPlayerExact(PlayerManager::getCombatOpponent($this->player))?->getNetworkSession()->getPing() : 0;//PlayerManager::getCombatOpponent($this->player->getNetworkSession()->getPing());

                if ($this->player->getWorld() === $this->plugin->getServer()->getWorldManager()->getDefaultWorld()) {
                    $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ",
                            3 => " §r§l§b§r§f K: §b{$kill} §r§fD: §b{$death}",
                            4 => " §r§l§b§r§f Elo: §b{$elo}",
                            5 => " §r§l§b§r§f Honour: §b{$honour}",
                            6 => " §r§l§b§r§f Rank:§r {$rank}",
                            7 => "§r  ",
                            8 => "§r§l§b ",
                            9 => " §r§l§b§r§f In-Queue: §b{$queue}",
                            10 => "§r §l§b§r§f In-Arena: §b{$playerCount}",
                            11 => "§r ",
                            12 => "§f§8",
                            13 => "§7 {$serverip} - {$r}"
                    ];
                    Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                    foreach ($lines as $line => $content) {
                        Scoreboard::setLine($this->player, $line, $content);
                    }
                }
                if (!isset(Main::$playerArena[$this->player->getName()])) return;
                switch (Main::$playerArena[$this->player->getName()]) {
                    case "Lobby":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ",
                            3 => " §r§l§b§r§f K: §b{$kill} §r§fD: §b{$death}",
                            4 => " §r§l§b§r§f Elo: §b{$elo}",
                            5 => " §r§l§b§r§f Honour: §b{$honour}",
                            6 => " §r§l§b§r§f Rank:§r {$rank}",
                            7 => "§r  ",
                            8 => "§r§l§b ",
                            9 => " §r§l§b§r§f In-Queue: §b{$queue}",
                            10 => "§r §l§b§r§f In-Arena: §b{$playerCount}",
                            11 => "§r ",
                            12 => "§f§8",
                            13 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "NodebuffFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ARENA",
                            3 => " §r§l§b:§r§f Mode:§b Nodebuff",
                            4 => " §r§l§b:§r§f K: §b{$kill} §r§fD: §b{$death}  ",
                            5 => " §r§l§b:§r§f Elo: §b{$elo}",
                            6 => " §r§l§b:§r§f Kill Streak§f: §b{$killstreak}",
                            7 => "§r     ",
                            8 => "§r§l§b FIGHT",
                            9 => " §r§l§b:§r§f Combat§f: §b{$combatTimer}",
                            10 => " §r§l§b:§r§f Ping: §a{$ping}§f : §c{$theirping}",
                            11 => "§f§8",
                            12 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "ComboFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ARENA",
                            3 => " §r§l§b:§r§f Mode:§b ComboFFA",
                            4 => " §r§l§b:§r§f K: §b{$kill} §r§fD: §b{$death}  ",
                            5 => " §r§l§b:§r§f Elo: §b{$elo}",
                            6 => " §r§l§b:§r§f Kill Streak§f: §b{$killstreak}",
                            7 => "§r     ",
                            8 => "§r§l§b FIGHT",
                            9 => " §r§l§b:§r§f Combat§f: §b{$combatTimer}",
                            10 => " §r§l§b:§r§f Ping: §a{$ping}§f : §c{$theirping}",
                            11 => "§f§8",
                            12 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "FistFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ARENA",
                            3 => " §r§l§b:§r§f Mode:§b Fist",
                            4 => " §r§l§b:§r§f K: §b{$kill} §r§fD: §b{$death}  ",
                            5 => " §r§l§b:§r§f Elo: §b{$elo}",
                            6 => " §r§l§b:§r§f Kill Streak§f: §b{$killstreak}",
                            7 => "§r     ",
                            8 => "§r§l§b FIGHT",
                            9 => " §r§l§b:§r§f Combat§f: §b{$combatTimer}",
                            10 => " §r§l§b:§r§f Ping: §a{$ping}§f : §c{$theirping}",
                            11 => "§f§8",
                            12 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "SumoFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ARENA",
                            3 => " §r§l§b:§r§f Mode:§b Sumo",
                            4 => " §r§l§b:§r§f K: §b{$kill} §r§fD: §b{$death}  ",
                            5 => " §r§l§b:§r§f Elo: §b{$elo}",
                            6 => " §r§l§b:§r§f Kill Streak§f: §b{$killstreak}",
                            7 => "§r     ",
                            8 => "§r§l§b FIGHT",
                            9 => " §r§l§b:§r§f Combat§f: §b{$combatTimer}",
                            10 => " §r§l§b:§r§f Ping: §a{$ping}§f : §c{$theirping}",
                            11 => "§f§8",
                            12 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "GappleFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ARENA",
                            3 => " §r§l§b:§r§f Mode:§b Gapple",
                            4 => " §r§l§b:§r§f K: §b{$kill} §r§fD: §b{$death}  ",
                            5 => " §r§l§b:§r§f Elo: §b{$elo}",
                            6 => " §r§l§b:§r§f Kill Streak§f: §b{$killstreak}",
                            7 => "§r     ",
                            8 => "§r§l§b FIGHT",
                            9 => " §r§l§b:§r§f Combat§f: §b{$combatTimer}",
                            10 => " §r§l§b:§r§f Ping: §a{$ping}§f : §c{$theirping}",
                            11 => "§f§8",
                            12 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "KnockFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ARENA",
                            3 => " §r§l§b:§r§f Mode:§b Knock",
                            4 => " §r§l§b:§r§f K: §b{$kill} §r§fD: §b{$death}  ",
                            5 => " §r§l§b:§r§f Elo: §b{$elo}",
                            6 => " §r§l§b:§r§f Kill Streak§f: §b{$killstreak}",
                            7 => "§r     ",
                            8 => "§r§l§b FIGHT",
                            9 => " §r§l§b:§r§f Combat§f: §b{$combatTimer}",
                            10 => " §r§l§b:§r§f Ping: §a{$ping}§f : §c{$theirping}",
                            11 => "§f§8",
                            12 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "ResistanceFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ARENA",
                            3 => " §r§l§b:§r§f Mode:§b ResistanceFFA",
                            4 => " §r§l§b:§r§f K: §b{$kill} §r§fD: §b{$death}  ",
                            5 => " §r§l§b:§r§f Elo: §b{$elo}",
                            6 => " §r§l§b:§r§f Kill Streak§f: §b{$killstreak}",
                            7 => "§r     ",
                            8 => "§r§l§b FIGHT",
                            9 => " §r§l§b:§r§f Combat§f: §b{$combatTimer}",
                            10 => " §r§l§b:§r§f Ping: §a{$ping}§f : §c{$theirping}",
                            11 => "§f§8",
                            12 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "BuildFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r §bArena§f: Build",
                            3 => "§r    ",
                            4 => "§r §bKill Streak§f: {$killstreak}",
                            5 => "§r     ",
                            6 => "§r §aYour Ping§f: {$ping}",
                            7 => "§r §cTheir Ping§f: {$theirping}",
                            8 => "§f§8",
                            9 => "§b {$serverip}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "MidfightFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ARENA",
                            3 => " §r§l§b:§r§f Mode:§b Midfight",
                            4 => " §r§l§b:§r§f K: §b{$kill} §r§fD: §b{$death}  ",
                            5 => " §r§l§b:§r§f Elo: §b{$elo}",
                            6 => " §r§l§b:§r§f Kill Streak§f: §b{$killstreak}",
                            7 => "§r     ",
                            8 => "§r§l§b FIGHT",
                            9 => " §r§l§b:§r§f Combat§f: §b{$combatTimer}",
                            10 => " §r§l§b:§r§f Ping: §a{$ping}§f : §c{$theirping}",
                            11 => "§f§8",
                            12 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "SkywarsFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ARENA",
                            3 => " §r§l§b:§r§f Mode:§b Skywars",
                            4 => " §r§l§b:§r§f K: §b{$kill} §r§fD: §b{$death}  ",
                            5 => " §r§l§b:§r§f Elo: §b{$elo}",
                            6 => " §r§l§b:§r§f Kill Streak§f: §b{$killstreak}",
                            7 => "§r     ",
                            8 => "§r§l§b FIGHT",
                            9 => " §r§l§b:§r§f Combat§f: §b{$combatTimer}",
                            10 => " §r§l§b:§r§f Ping: §a{$ping}§f : §c{$theirping}",
                            11 => "§f§8",
                            12 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                    case "BuilduhcFFA":
                        $lines = [
                            1 => "§r§8",
                            2 => "§r§l§b ARENA",
                            3 => " §r§l§b:§r§f Mode:§b BuildUHC",
                            4 => " §r§l§b:§r§f K: §b{$kill} §r§fD: §b{$death}  ",
                            5 => " §r§l§b:§r§f Elo: §b{$elo}",
                            6 => " §r§l§b:§r§f Kill Streak§f: §b{$killstreak}",
                            7 => "§r     ",
                            8 => "§r§l§b FIGHT",
                            9 => " §r§l§b:§r§f Combat§f: §b{$combatTimer}",
                            10 => " §r§l§b:§r§f Ping: §a{$ping}§f : §c{$theirping}",
                            11 => "§f§8",
                            12 => "§7 {$serverip} - {$r}"
                        ];
                        Scoreboard::new($this->player, "", "§l§f{$region}§b PRACTICE");

                        foreach ($lines as $line => $content) {
                            Scoreboard::setLine($this->player, $line, $content);
                        }
                        break;
                }
            }
        }
    }
}