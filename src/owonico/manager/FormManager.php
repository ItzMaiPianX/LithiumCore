<?php

namespace owonico\manager;

/*---------------------------------
basic core uses
---------------------------------*/

use owonico\{Main, Variables};
use owonico\utils\Utils;
use owonico\manager\{PlayerManager, PassManager, RankManager};

/*---------------------------------
basic libs uses
---------------------------------*/

use maipian\webhook\Embed;
use maipian\webhook\Message;
use maipian\webhook\Webhook;
use maipian\form\pmforms\MenuForm;
use maipian\form\pmforms\MenuOption;
use maipian\form\pmforms\FormIcon;
use maipian\form\formapi\SimpleForm;
use maipian\form\pmforms\ModalForm;
use maipian\form\formapi\CustomForm;
use maipian\scoreboard\Scoreboard;
use vixikhd\duels5\arena\Arena;
use maipian\query\PMQuery;
use maipian\query\PmQueryException;
use owonico\skin\ClothesManager;
use owonico\skin\skinStuff\resetSkin;
use owonico\skin\skinStuff\setSkin;
use owonico\task\CrateTask;
/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\entity\Skin;
use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\sound\XpLevelUpSound;

class FormManager{

    public static function getFFAForm(): MenuForm{
        return new MenuForm(
            "§b" . "§fFREE FOR ALL",
            "§bHey, Which FFA Gamemode Would You Like To Play? §7Choose from our selection of games.",
            [
                new MenuOption("§bNodebuff\n§7 " . Main::getWorldCount(Variables::Nodebuffffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/nodebuff.png", FormIcon::IMAGE_TYPE_PATH)),
            /*    new MenuOption("§b Combo\n§7 " . Main::getWorldCount(Variables::Comboffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/combo.png", FormIcon::IMAGE_TYPE_PATH)),*/
                new MenuOption("§b Fist\n§7 " . Main::getWorldCount(Variables::Fistffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/fist.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Sumo\n§7 " . Main::getWorldCount(Variables::Sumoffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/sumo.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Gapple\n§7 " . Main::getWorldCount(Variables::Gappleffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/gapple.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b KnockBack\n§7 " . Main::getWorldCount(Variables::Knockffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/knock.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Resistance\n§7 " . Main::getWorldCount(Variables::Resistanceffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/resistance.png", FormIcon::IMAGE_TYPE_PATH)),
              /*  new MenuOption("§b Build\n§7 " . Main::getWorldCount(Variables::Buildffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/build.png", FormIcon::IMAGE_TYPE_PATH)),
             */ new MenuOption("§b Midfight\n§7 " .  Main::getWorldCount(Variables::Midfightffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/combo.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Skywars\n§7 " .  Main::getWorldCount(Variables::Skywarsffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/build.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b BuildUHC\n§7 " .  Main::getWorldCount(Variables::Builduhcffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/uch.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Nodebuffffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "NodebuffFFA";
                        PlayerManager::sendNodebuffKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                  /*  case 1:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Comboffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "ComboFFA";
                        PlayerManager::sendComboKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break; */
                    case 1:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Fistffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "FistFFA";
                        PlayerManager::sendFistKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 2:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Sumoffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "SumoFFA";
                        PlayerManager::sendSumoKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 3:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Gappleffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "GappleFFA";
                        PlayerManager::sendGappleKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 4:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Knockffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "KnockFFA";
                        PlayerManager::sendKnockKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 5:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Resistanceffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "ResistanceFFA";
                        PlayerManager::sendResistanceKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    /*case 7:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Skywarsffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "BuildFFA";
                        PlayerManager::sendSkywarsKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;*/
                    case 6:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Midfightffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "MidfightFFA";
                        PlayerManager::sendMidfightKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 7:
                        Utils::randomTeleport($player);
                        Main::$playerArena[$player->getName()] = "SkywarsFFA";
                        PlayerManager::sendSkywarsKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;
                    case 8:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Builduhcffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "BuildUHCFFA";
                        PlayerManager::sendBuildUHCKit($player);
                        $player->setGamemode(Gamemode::SURVIVAL());
                        break;
                }
            },

            function (Player $submitter): void{
                //I dont want to handle when his close the form
            }
        );
    }
    public static function getSpectateForm(Player $player): MenuForm{
      return new MenuForm(
          "§7Spectate",
          "",
          [
                new MenuOption("§aFFAS", new FormIcon("quza/textures/ui/quza_ui/ffa.png", FormIcon::IMAGE_TYPE_PATH)),              
                new MenuOption("§aDuels", new FormIcon("quza/textures/ui/quza_ui/duels.png", FormIcon::IMAGE_TYPE_PATH))
              ],
          function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->sendForm(self::getSpecFormFfa($player));
                        break;                        
                    case 1:
                        $player->sendForm(self::getSpecFormDuels($player));
                        break;
                }
            },

            function (Player $submitter): void{
                //I dont want to handle when his close the form
            }
        );
    }
    public static function getSpecFormFfa(Player $player): MenuForm{
      return new MenuForm(
          "§7FFAS",
          "§bHey, Which FFA Gamemode Would You Like To Spectate? §7Choose from our selection of games.",
          [
              new MenuOption("§bNodebuff\n§7 " . Main::getWorldCount(Variables::Nodebuffffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/nodebuff.png", FormIcon::IMAGE_TYPE_PATH)),
            /*    new MenuOption("§b Combo\n§7 " . Main::getWorldCount(Variables::Comboffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/combo.png", FormIcon::IMAGE_TYPE_PATH)),*/
                new MenuOption("§b Fist\n§7 " . Main::getWorldCount(Variables::Fistffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/fist.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Sumo\n§7 " . Main::getWorldCount(Variables::Sumoffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/sumo.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Gapple\n§7 " . Main::getWorldCount(Variables::Gappleffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/gapple.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b KnockBack\n§7 " . Main::getWorldCount(Variables::Knockffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/knock.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Resistance\n§7 " . Main::getWorldCount(Variables::Resistanceffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/resistance.png", FormIcon::IMAGE_TYPE_PATH)),
              /*  new MenuOption("§b Build\n§7 " . Main::getWorldCount(Variables::Buildffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/build.png", FormIcon::IMAGE_TYPE_PATH)),
             */ new MenuOption("§b Midfight\n§7 " .  Main::getWorldCount(Variables::Midfightffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/combo.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Skywars\n§7 " .  Main::getWorldCount(Variables::Skywarsffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/build.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b BuildUHC\n§7 " .  Main::getWorldCount(Variables::Builduhcffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/uch.png", FormIcon::IMAGE_TYPE_PATH))
              ],
          function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Nodebuffffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "NodebuffFFA";
                        PlayerManager::sendSpectateKit($player);
                        hidePlayer($player);
                        $player->setGamemode(GameMode::SPECTATOR());
                        break;
                    case 1:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Fistffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "FistFFA";
                        PlayerManager::sendSpectateKit($player);
                        $player->setGamemode(GameMode::SPECTATOR());
                        break;
                    case 2:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Sumoffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "SumoFFA";
                        PlayerManager::sendSpectateKit($player);
                        $player->setGamemode(GameMode::SPECTATOR());
                        break;
                    case 3:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Gappleffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "GappleFFA";
                        PlayerManager::sendSpectateKit($player);
                        $player->setGamemode(GameMode::SPECTATOR());
                        break;
                    case 4:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Knockffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "KnockFFA";
                        PlayerManager::sendSpectateKit($player);
                        $player->setGamemode(GameMode::SPECTATOR());
                        break;
                    case 5:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Resistanceffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "ResistanceFFA";
                        PlayerManager::sendSpectateKit($player);
                        $player->setGamemode(GameMode::SPECTATOR());
                        break;
                    /*case 7:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Buildffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "BuildFFA";
                        PlayerManager::sendBFFAKit($player);
                        $player->setGamemode(GameMode::SURVIVAL());
                        break;*/
                    case 6:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Midfightffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "MidfightFFA";
                        PlayerManager::sendSpectateKit($player);
                        $player->setGamemode(GameMode::SPECTATOR());
                        break;
                    case 7:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Skywarsffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "SkywarsFFA";
                        PlayerManager::sendSpectateKit($player);
                        $player->setGamemode(GameMode::SPECTATOR());
                        break;
                    case 8:
                        $player->teleport(Server::getInstance()->getWorldManager()->getWorldByName(Variables::Builduhcffa)->getSafeSpawn());
                        Main::$playerArena[$player->getName()] = "BuildUHCFFA";
                        PlayerManager::sendSpectateKit($player);
                        $player->setGamemode(Gamemode::SPECTATOR());
                        break;
                }
            },

            function (Player $submitter): void{
                //I dont want to handle when his close the form
            }
        );
    }
    public static function getSpecFormDuels(Player $player): MenuForm{
        return new MenuForm(
          "§7DUELS",
          "§bHey, Which Duel Gamemode Would You Like To Spectate? §7Choose from our selection of games.",
          [
              new MenuOption("§bNodebuff\n§7 " /*. Main::getWorldCount(Variables::Nodebuffduel1, Nodebuffduel2) . "§7 Playing"*/, new FormIcon("quza/textures/ui/ui_png/nodebuff.png", FormIcon::IMAGE_TYPE_PATH)),
            /*    new MenuOption("§b Combo\n§7 " . Main::getWorldCount(Variables::Comboffa) . "§7 Playing", new FormIcon("quza/textures/ui/ui_png/combo.png", FormIcon::IMAGE_TYPE_PATH)),*/
                new MenuOption("§b Sumo\n§7 " /*. Main::getWorldCount(Variables::Sumoduel1, Sumoduel2) . "§7 Playing"*/, new FormIcon("quza/textures/ui/ui_png/sumo.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Fist\n§7 " /*. Main::getWorldCount(Variables::Fistduel1) . "§7 Playing"*/, new FormIcon("quza/textures/ui/ui_png/fist.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b BuildUHC\n§7 " /*. Main::getWorldCount(Variables::Builduhcduel1) . "§7 Playing"*/, new FormIcon("quza/textures/ui/ui_png/builduhc.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Bridge\n§7 " /*. Main::getWorldCount(Variables::Bridgeduel1, Bridgeduel2, Bridgeduel3) . "§7 Playing"*/, new FormIcon("quza/textures/ui/ui_png/bridge.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Midfight\n§7 " /*. Main::getWorldCount(Variables::Sumoduel1, Sumoduel2) . "§7 Playing"*/, new FormIcon("quza/textures/ui/ui_png/midfight.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§b Boxing\n§7 " /*.  Main::getWorldCount(Variables::Midfightffa) . "§7 Playing"*/, new FormIcon("quza/textures/ui/ui_png/boxing.png", FormIcon::IMAGE_TYPE_PATH))
              ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->sendForm(self::getDuelsSpecFormNodebuff($player));
                        break;
                    case 1:
                        $player->sendForm(self::getDuelsSpecFormSumo($player));
                        break;
                    case 2:
                        $player->sendForm(self::getDuelsSpecFormFist($player));
                        break;
                    case 3:
                        $player->sendForm(self::getDuelsSpecFormBuilduhc($player));
                        break;
                    case 4:
                        $player->sendForm(self::getDuelsSpecFormBridge($player));
                        break;
                    case 5:
                        $player->sendForm(self::getDuelsSpecFormMidfight($player));
                        break;
                    case 6:
                        $player->sendForm(self::getDuelsSpecFormBoxing($player));
                        break;
                }
            },

            function (Player $submitter): void{
                //I dont want to handle when his close the form
            }
        );
    }
    public static function getSettingsForm(Player $player): MenuForm{
        $cps = new Config(Main::getInstance()->getDataFolder() . "settings/CPSPopup.yml", Config::YAML);
        $hitParticles = new Config(Main::getInstance()->getDataFolder() . "settings/HitParticles.yml", Config::YAML);
        $autoSprint = new Config(Main::getInstance()->getDataFolder() . "settings/AutoSprint.yml", Config::YAML);
        $scoreboard = new Config(Main::getInstance()->getDataFolder() . "settings/Scoreboard.yml", Config::YAML);
        $arenaRespawn = new Config(Main::getInstance()->getDataFolder() . "settings/ArenaRespawn.yml", Config::YAML);
        if (SettingsManager::getCpsEnabled($player)) {
            $cpsStatus = "§bCPS Popup\n§aEnabled";
        } else {
            $cpsStatus = "§bCPS Popup\n§cDisabled";
        }
        if (SettingsManager::getHitEffectEnabled($player)) {
            $hitStatus = "§bHit Effect\n§aEnabled";
        } else {
            $hitStatus = "§bHit Effect\n§cDisabled";
        }
        if (SettingsManager::getAutoSprintEnabled($player)) {
            $autoStatus = "§bAuto Sprint\n§aEnabled";
        } else {
            $autoStatus = "§bAuto Sprint\n§cDisabled";
        }
        if (SettingsManager::getArenaRespawnEnabled($player)) {
            $arenaStatus = "§bArena Respawn\n§aEnabled";
        } else {
            $arenaStatus = "§bArena Respawn\n§cDisabled";
        }
        /*if (SettingsManager::getScoreboardEnabled($player)) {
            $scoreStatus = "§bScoreboard\n§aEnabled";
        } else {
            $scoreStatus = "§bScoreboard\n§cDisabled";
        }*/

        return new MenuForm(
            "§7SETTINGS",
            "",
            [
                new MenuOption($cpsStatus, new FormIcon("quza/textures/ui/ui_png/settings.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption($hitStatus, new FormIcon("quza/textures/ui/ui_png/settings.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption($autoStatus, new FormIcon("quza/textures/ui/ui_png/settings.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption($arenaStatus, new FormIcon("quza/textures/ui/ui_png/arenarespawn.png", FormIcon::IMAGE_TYPE_PATH))
                //new MenuOption($scoreStatus, new FormIcon("quza/textures/ui/ui_png/settings.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $submitter, int $selected) use ($cps, $autoSprint, $hitParticles, $scoreboard, $arenaRespawn): void{
                switch ($selected){
                    case 0:
                        if (SettingsManager::getCpsEnabled($submitter)){
                            //$cps->remove($submitter->getXuid());
                            //$cps->save();
                            SettingsManager::setCpsEnabled($submitter, false);

                            $submitter->sendMessage(Variables::Prefix . "§aDisabled CPS Popup");
                        } else{
                            //$cps->set($submitter->getXuid(), true);
                            //$cps->save();
                            SettingsManager::setCpsEnabled($submitter, true);

                            $submitter->sendMessage(Variables::Prefix . "§aEnabled CPS Popup");
                        }
                        break;
                    case 1:
                        if (SettingsManager::getHitEffectEnabled($submitter)){
                            //$hitParticles->remove($submitter->getXuid());
                            //$hitParticles->save();
                            SettingsManager::setHitEffectEnabled($submitter, false);

                            $submitter->sendMessage(Variables::Prefix . "§aDisabled Hit Effect");
                        } else{
                            //$hitParticles->set($submitter->getXuid(), true);
                            //$hitParticles->save();
                            SettingsManager::setHitEffectEnabled($submitter, true);

                            $submitter->sendMessage(Variables::Prefix . "§aEnabled Hit Effect");
                        }
                        break;
                     case 2:
                        if (SettingsManager::getAutoSprintEnabled($submitter)){
                            //$autoSprint->remove($submitter->getXuid());
                            //$autoSprint->save();
                            SettingsManager::setAutoSprintEnabled($submitter, false);

                            $submitter->sendMessage(Variables::Prefix . "§aDisabled Auto Sprint");
                        } else{
                            //$autoSprint->set($submitter->getXuid(), true);
                            //$autoSprint->save();
                            SettingsManager::setAutoSprintEnabled($submitter, true);

                            $submitter->sendMessage(Variables::Prefix . "§aEnabled Auto Sprint");
                        }
                        break;
                        case 3:
                        if (SettingsManager::getArenaRespawnEnabled($submitter)){
                            //$autoSprint->remove($submitter->getXuid());
                            //$autoSprint->save();
                            SettingsManager::setArenaRespawnEnabled($submitter, false);

                            $submitter->sendMessage(Variables::Prefix . "§aDisabled Arena Respawn");
                        } else{
                            //$autoSprint->set($submitter->getXuid(), true);
                            //$autoSprint->save();
                            SettingsManager::setArenaRespawnEnabled($submitter, true);

                            $submitter->sendMessage(Variables::Prefix . "§aEnabled Arena Respawn");
                        }
                        break;
                    /*case 3:
                        if (SettingsManager::getScoreboardEnabled($submitter)){
                            //$scoreboard->set($submitter->getXuid(), false);
                            //$scoreboard->save();
                            SettingsManager::setScoreboardEnabled($submitter, false);

                            Main::$scoreboardEnabled[$submitter->getName()] = false;
                            Scoreboard::remove($submitter);

                            $submitter->sendMessage(Variables::Prefix . "§aDisabled Scoreboard");
                        } else{
                            //$scoreboard->set($submitter->getXuid(), true);
                            //$scoreboard->save();
                            SettingsManager::setScoreboardEnabled($submitter, true);

                            Main::$scoreboardEnabled[$submitter->getName()] = true;

                            $submitter->sendMessage(Variables::Prefix . "§aEnabled Scoreboard");
                        }
                        break;*/
                }
            },
            function (Player $submitter): void{

            }
        );
    }

    public static function getRuleForm(): MenuForm{
        return new MenuForm(
            "§eWelcome to §l§eLithiumMC",
            Main::getRuleContent(),
            [
                new MenuOption("§aAgree"),
                new MenuOption("§cDisagree")
            ],
            function(Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->sendMessage(Variables::Prefix . "§aHave Fun!");
                        break;
                    case 1:
                        $player->kick("§cYou must agree on the rule to play on server!");
                        break;
                }
            },
            function (Player $selecter): void{
                //$selecter->kick("§cYou must agree on the rule to play on server!");
            }
        );
    }
    
    public static function getPassesForm(Player $player): MenuForm{
      $aamt = "1000";
      $aamt2 = "2500";
      $aamt3 = "7500";
      $aamt4 = "12500";
      $aamt5 = "17500";
      $aamt6 = "20000";
      $aamt7 = "30000";
      $aamt8 = "40000";
      $aamt9 = "60000";
      $aamt10 = "80000";
      $amt = "10000";
      $amt2 = "25000";
      $amt3 = "50000";
      $amt4 = "75000";
      $amt5 = "100000";
      $apmt1 = "15000";
      $apmt2 = "22500";
      $apmt3 = "35000";
      $apmt4 = "45000";
      $apmt5 = "70000";
      $apmt6 = "90000";
      $paamt = "1000";
      $paamt2 = "2500";
      $paamt3 = "7500";
      $paamt4 = "12500";
      $paamt5 = "17500";
      $paamt6 = "20000";
      $paamt7 = "30000";
      $paamt8 = "40000";
      $paamt9 = "60000";
      $paamt10 = "80000";
      $pamt = "10000";
      $pamt2 = "25000";
      $pamt3 = "50000";
      $pamt4 = "75000";
      $pamt5 = "100000";
      $papmt1 = "15000";
      $papmt2 = "22500";
      $papmt3 = "35000";
      $papmt4 = "45000";
      $papmt5 = "70000";
      $papmt6 = "90000";
      $paidpassamt = "1";
      $honourrn = PlayerManager::getPlayerHonour($player);
      $paidpass = PlayerManager::getPlayerPass($player);
       if ($paidpass >= $paidpassamt){
         $passowned = "§aOWNED";
       } else {
         $passowned = "§cNOT OWNED";
       }
       if ($honourrn >= $aamt){
        $ccolor = "§a";
              } else {
        $ccolor = "§c";
       }
       if ($honourrn >= $aamt2){
           $ccolor2 = "§a";
              } else {
        $ccolor2 = "§c";
           }
       if ($honourrn >= $aamt3){
            $ccolor3 = "§a";
              } else {
        $ccolor3 = "§c";
       }
       if ($honourrn >= $aamt4){
            $ccolor4 = "§a";
              } else {
        $ccolor4 = "§c";
       }
       if ($honourrn >= $aamt5){
            $ccolor5 = "§a";          
    } else {
        $ccolor5 = "§c";
    }
        if ($honourrn >= $aamt6){
        $ccolor6 = "§a";
              } else {
        $ccolor6 = "§c";
       }
       if ($honourrn >= $aamt7){
           $ccolor7 = "§a";
              } else {
        $ccolor7 = "§c";
           }
       if ($honourrn >= $aamt8){
            $ccolor8 = "§a";
              } else {
        $ccolor8 = "§c";
       }
       if ($honourrn >= $aamt9){
            $ccolor9 = "§a";
              } else {
        $ccolor9 = "§c";
       }
       if ($honourrn >= $aamt10){
            $ccolor10 = "§a";          
    } else {
        $ccolor10 = "§c";
    }
       if ($honourrn >= $amt){
        $color = "§a";
              } else {
        $color = "§c";
       }
       if ($honourrn >= $amt2){
           $color2 = "§a";
              } else {
        $color2 = "§c";
           }
       if ($honourrn >= $amt3){
            $color3 = "§a";
              } else {
        $color3 = "§c";
       }
       if ($honourrn >= $amt4){
            $color4 = "§a";
              } else {
        $color4 = "§c";
       }
       if ($honourrn >= $amt5){
            $color5 = "§a";          
    } else {
        $color5 = "§c";
    }
       if ($honourrn >= $apmt1){
            $pcolor1 = "§a";          
    } else {
        $pcolor1 = "§c";
    }
       if ($honourrn >= $apmt2){
        $pcolor2 = "§a";
              } else {
        $pcolor2 = "§c";
       }
       if ($honourrn >= $apmt3){
           $pcolor3 = "§a";
              } else {
        $pcolor3 = "§c";
           }
       if ($honourrn >= $apmt4){
            $pcolor4 = "§a";
              } else {
        $pcolor4 = "§c";
       }
       if ($honourrn >= $apmt5){
            $pcolor5 = "§a";
              } else {
        $pcolor5 = "§c";
       }
       if ($honourrn >= $apmt6){
            $pcolor6 = "§a";          
    } else {
        $pcolor6 = "§c";
        }
        //distinguisher
    if ($honourrn >= $paamt && $paidpass >= $paidpassamt){
        $pccolor = "§a";
              } else {
        $pccolor = "§c";
       }
       if ($honourrn >= $paamt2 && $paidpass >= $paidpassamt){
           $pccolor2 = "§a";
              } else {
        $pccolor2 = "§c";
           }
       if ($honourrn >= $paamt3 && $paidpass >= $paidpassamt){
            $pccolor3 = "§a";
              } else {
        $pccolor3 = "§c";
       }
       if ($honourrn >= $paamt4 && $paidpass >= $paidpassamt){
            $pccolor4 = "§a";
              } else {
        $pccolor4 = "§c";
       }
       if ($honourrn >= $paamt5 && $paidpass >= $paidpassamt){
            $pccolor5 = "§a";          
    } else {
        $pccolor5 = "§c";
    }
        if ($honourrn >= $paamt6 && $paidpass >= $paidpassamt){
        $pccolor6 = "§a";
              } else {
        $pccolor6 = "§c";
       }
       if ($honourrn >= $paamt7 && $paidpass >= $paidpassamt){
           $pccolor7 = "§a";
              } else {
        $pccolor7 = "§c";
           }
       if ($honourrn >= $paamt8 && $paidpass >= $paidpassamt){
            $pccolor8 = "§a";
              } else {
        $pccolor8 = "§c";
       }
       if ($honourrn >= $paamt9 && $paidpass >= $paidpassamt){
            $pccolor9 = "§a";
              } else {
        $pccolor9 = "§c";
       }
       if ($honourrn >= $paamt10 && $paidpass >= $paidpassamt){
            $pccolor10 = "§a";          
    } else {
        $pccolor10 = "§c";
    }
       if ($honourrn >= $pamt && $paidpass >= $paidpassamt){
        $pcolor = "§a";
              } else {
        $pcolor = "§c";
       }
       if ($honourrn >= $pamt2 && $paidpass >= $paidpassamt){
           $pcolor2 = "§a";
              } else {
        $pcolor2 = "§c";
           }
       if ($honourrn >= $pamt3 && $paidpass >= $paidpassamt){
            $pcolor3 = "§a";
              } else {
        $pcolor3 = "§c";
       }
       if ($honourrn >= $pamt4 && $paidpass >= $paidpassamt){
            $pcolor4 = "§a";
              } else {
        $pcolor4 = "§c";
       }
       if ($honourrn >= $pamt5 && $paidpass >= $paidpassamt){
            $pcolor5 = "§a";          
    } else {
        $pcolor5 = "§c";
    }
        if ($honourrn >= $papmt1 && $paidpass >= $paidpassamt){
            $ppcolor1 = "§a";          
    } else {
        $ppcolor1 = "§c";
    }
       if ($honourrn >= $papmt2 && $paidpass >= $paidpassamt){
        $ppcolor2 = "§a";
              } else {
        $ppcolor2 = "§c";
       }
       if ($honourrn >= $papmt3 && $paidpass >= $paidpassamt){
           $ppcolor3 = "§a";
              } else {
        $ppcolor3 = "§c";
           }
       if ($honourrn >= $papmt4 && $paidpass >= $paidpassamt){
            $ppcolor4 = "§a";
              } else {
        $ppcolor4 = "§c";
       }
       if ($honourrn >= $papmt5 && $paidpass >= $paidpassamt){
            $ppcolor5 = "§a";
              } else {
        $ppcolor5 = "§c";
       }
       if ($honourrn >= $papmt6 && $paidpass >= $paidpassamt){
            $ppcolor6 = "§a";          
    } else {
        $ppcolor6 = "§c";
    }
         return new MenuForm(
            "§aYour Passes",
            "
§7YOUR HONOUR§f: §a{$honourrn}            
            
§7HONOUR PASS [§aOWNED§7]§r 

[{$ccolor}1000§f] §e150 §fCoins
[{$ccolor2}2500§f] §e500 §fCoins
[{$ccolor3}7500§f] §e1500 §fCoins
[{$color}10000§f] §2Gold §fTier
[{$ccolor4}12500§f] §e3000 §fCoins
[{$pcolor1}15000§f] §cClaymore §f[§cA§f]
[{$ccolor5}17500§f] §e5000 §fCoins
[{$ccolor6}20000§f] §e10000 §fCoins
[{$pcolor2}22500§f] §cFire §f[§cC§f]
[{$color2}25000§f] §2Champion §fTier
[{$ccolor7}30000§f] §e12500 §fCoins
[{$pcolor3}35000§f] §cThrown Into Hell §f[§cK§f]
[{$ccolor8}40000§f] §e15000 §fCoins
[{$pcolor4}45000§f] §cDevil Wings §f[§cA§f]
[{$color3}50000§f] §2Master §fTier
[{$ccolor9}60000§f] §e20000 §fCoins
[{$pcolor5}70000§f] §cSkull Cape §f[§cC§f]
[{$color4}75000§f] §2Grandmaster §fTier
[{$ccolor10}80000§f] §e35000 §fCoins
[{$pcolor6}90000§f] §cDevil Costume §f[§cA§f]
[{$color5}100000§f] §2Heroic §fTier

§7PAID PASS [{$passowned}§7]§r

[{$pccolor}1000§f] §e300 §fCoins
[{$pccolor2}2500§f] §e1000 §fCoins
[{$pccolor3}7500§f] §e3000 §fCoins
[{$pcolor}10000§f] §2Gold §fTier
[{$pccolor4}12500§f] §e6000 §fCoins
[{$ppcolor1}15000§f] §cEarth §f[§cA§f]
[{$pccolor5}17500§f] §e10000 §fCoins
[{$pccolor6}20000§f] §e20000 §fCoins
[{$ppcolor2}22500§f] §cHeaven §f[§cC§f]
[{$pcolor2}25000§f] §2Champion §fTier
[{$pccolor7}30000§f] §e25000 §fCoins
[{$ppcolor3}35000§f] §cSent To Heaven §f[§cK§f]
[{$pccolor8}40000§f] §e30000 §fCoins
[{$ppcolor4}45000§f] §cStar §f[§cA§f]
[{$pcolor3}50000§f] §2Master §fTier
[{$pccolor9}60000§f] §e40000 §fCoins
[{$ppcolor5}70000§f] §cLight §f[§cC§f]
[{$pcolor4}75000§f] §2Grandmaster §fTier
[{$pccolor10}80000§f] §e70000 §fCoins
[{$ppcolor6}90000§f] §cAngel Wings §f[§cA§f]
[{$pcolor5}100000§f] §2Heroic §fTier 
            ",
            [
                new MenuOption("§aDone"),
            ],
            function(Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->sendForm(self::getCapeForm($player));
                        break;
                }
            },
            function (Player $selecter): void{
                //$selecter->kick("§cYou must agree on the rule to play on server!");
            }
        );
    }

    public static function getCapeForm(): MenuForm{
        return new MenuForm(
            "§7Cosmetics",
            "",
            [
               // new MenuOption("§aCrates", new FormIcon("quza/textures/ui/ui_png/crates.png", FormIcon::IMAGE_TYPE_PATH)),
                //new //MenuOption("§aBundles", new FormIcon("quza/textures/ui/ui_png/bundles.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§aYour Cosmetics", new FormIcon("quza/textures/ui/quza_ui/on.png", FormIcon::IMAGE_TYPE_PATH)),
            //    new MenuOption("§aTrade Menu", new FormIcon("quza/textures/ui/quza_ui/trade.png", FormIcon::IMAGE_TYPE_PATH)),
       //         new MenuOption("§aRecycle Cosmetics", new FormIcon("quza/textures/ui/quza_ui/recycle.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§aPass", new FormIcon("quza/textures/ui/ui_png/pass.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected) : void{
                switch ($selected){
                    /*case 0:
                        $player->sendForm(self::getBundlesForm($player));
                        break;*/
              /*          $pdata = new Config(Main::getInstance()->getDataFolder() . "capes/data.yml", Config::YAML);
                        $oldSkin = $player->getSkin();
                        $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), "", $oldSkin->getGeometryName(), $oldSkin->getGeometryData());

                        $player->setSkin($setCape);
                        $player->sendSkin();

                        if($pdata->get($player->getXuid()) !== null){
                            $pdata->remove($player->getXuid());
                            $pdata->save();
                        }

                        $player->sendMessage(Variables::Prefix . "§aRemoved your cape!");
                        break;*/
                    case 0:
                        $player->sendForm(self::getOwnedForm($player));
                        break;
                    case 1:
                        $player->sendForm(self::getPassesForm($player));
                        break;
                 /*   case 3:
                        $player->sendForm(self::getRecycleForm($player));
                        break;*/
               /*     case 4:
                        $player->sendForm(self::getPassesForm($player));
                        break;*/
                }
            },
            function (Player $submiter): void{
            }
         );
    }
    public static function getBundlesForm(): MenuForm{
        return new MenuForm(
            "§7Bundles",
            "",
            [
                new MenuOption("§bExodus Bundle", new FormIcon("quza/textures/ui/quza_ui/artifacts.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§bHats Bundle", new FormIcon("quza/textures/ui/quza_ui/capes.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        //$player->sendForm(self::getArtifactBuyForm());
                        $player->sendForm(self::getArtifactsForm($player));
                        break;                        
                    case 1:
                        $player->sendForm(self::getBundle2Form($player));
                        break;
                }
            },
            function (Player $player): void{

            }
        );
    }
    public static function getArtifactBuyForm(): MenuForm{
        return new MenuForm(
            "§7Exodus Bundle",
            "
§l§6CONTAINS:§r

§3Angel Wings §f[§2A§f],
§3Devil Wings §f[§2A§f], 
§3Claymore §f[§2A§f].

§l§6COSTS:§r
§a25000 §6coins!",

            [
                new MenuOption("§aPurchase", new FormIcon("quza/textures/ui/quza_ui/on.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§cCancel", new FormIcon("quza/textures/ui/quza_ui/off.png", FormIcon::IMAGE_TYPE_PATH))
             ],
           function (Player $player, int $selected): void{
                switch($selected){
                    case 0:
               $price = "25000";
                if(PlayerManager::getPlayerCoin($player) >= $price){
                    PlayerManager::reducePlayerCoin($player, $price);
                    $player->sendMessage(Variables::Prefix . "§aYou have successfully purchased the Exodus Bundle!");
                    $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(5));
                } else {
                    $player->sendMessage(Variables::Prefix . "§cYou dont have enough money to purchase this Bundle!");
                }
                    break;
                    case 1:
                        $player->sendForm(self::getBundlesForm($player));
                        break;
                }
            },
            function (Player $player): void{
            }
        );
    }
    public static function getBundle2Form(): MenuForm{
        return new MenuForm(
            "§7Hats Bundle",          
            "
§l§6CONTAINS:§r
            
§1Dynamite Hat §f[§2H§f],
§1Cowboy Hat §f[§2H§f], 
§1Witch Hat §f[§2H§f].

§l§6COSTS:§r
§a10000 §6coins!",

            [
                new MenuOption("§aPurchase", new FormIcon("quza/textures/ui/quza_ui/on.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§cCancel", new FormIcon("quza/textures/ui/quza_ui/off.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $price = "10000";
                if(PlayerManager::getPlayerCoin($player) >= $price){
                    PlayerManager::reducePlayerCoin($player, $price);
                    $player->sendMessage(Variables::Prefix . "§aYou have successfully purchased the Hats Bundle!");
                    $player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(5));
                } else {
                    $player->sendMessage(Variables::Prefix . "§cYou dont have enough money to purchase this Bundle!");
                }
                        break;
                    case 1:
                        $player->sendForm(self::getBundlesForm($player));
                        break;
                }
            },
            function (Player $player): void{

            }
        );
    }
    public static function getOwnedForm(): MenuForm{
        return new MenuForm(
            "§7Purchase A Cosmetic",
            "",
            [
                new MenuOption("§bArtifacts", new FormIcon("quza/textures/ui/quza_ui/artifacts.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§bCapes", new FormIcon("quza/textures/ui/quza_ui/capes.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        //$player->sendForm(self::getOwnedArtifactForm());
                        $player->getServer()->dispatchCommand($player, "clothes");
                        break;                        
                    case 1:
                        $player->sendForm(self::getCapeListForm($player));
                        break;
                }
            },
            function (Player $player): void{

            }
        );
    }
    public static function getOwnedArtifactForm(): MenuForm{
        return new MenuForm(
            "§7Artifacts",
            "",
            [
               new MenuOption("§bA1", new FormIcon("quza/textures/ui/quza_ui/artifact.png", FormIcon::IMAGE_TYPE_PATH))
                ],
            function (Player $player, int $selected): void{
              switch($selected){
                  case 0:
                      $player->getServer()->dispatchCommand($player, "clothes");
                      break;
                }
            },
            function (Player $player): void{
            }
        );
    }
    public static function getCosmeticForm(): MenuForm{
        return new MenuForm(
            "§7COSMETICS",
            "§bHere You Can Buy Cosmetics of our server using server economy! §7Please Select a cosmetic category.",
            [
                new MenuOption("§7Cosmetics", new FormIcon("quza/textures/ui/quza_ui/cape.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->sendForm(self::getCapeForm());
                        break;
                }
            },
            function (Player $player): void{
            }
        );
    }

    public static function getBuyCapeForm(string $capeName){
        $form = new ModalForm("Purchase - Cape", "§bYou didnt buy this cape yet, Do you want to purchase it now?\n\n§bCape: §6{$capeName}\n§bPrice: §a" . CosmeticsManager::getPriceFromCapeName($capeName) . " Coins", function(Player $player, bool $choice) use ($capeName) : void{

            $config = CosmeticsManager::getConfigFromCapeName($capeName);

            if($config == null) return;

            $price = CosmeticsManager::getPriceFromCapeName($capeName);

            if($choice){
                if(PlayerManager::getPlayerCoin($player) >= $price){
                    PlayerManager::reducePlayerCoin($player, $price);

                    $config->set($player->getXuid(), true);
                    $config->save();

                    $player->sendMessage(Variables::Prefix . "§aPurchased cape §b" . $capeName);
                } else {
                    $player->sendMessage(Variables::Prefix . "§cYou dont have enough money to purchase this!");
                }
            } else {
                //NOOP
            }

        }, "§aConfirm", "§cCancel");

        return $form;
    }

    public static function getCapeListForm(Player $player): SimpleForm{
        $form = new SimpleForm(function (Player $player, $data = null){
            $result = $data;

            if(is_null($result)) {
                return true;
            }

            $cape = $data;
            $pdata = new Config(Main::getInstance()->getDataFolder() . "capes/data.yml", Config::YAML);

            if(!file_exists(Main::getInstance()->getDataFolder() . "capes/" . $data . ".png")) {
                $player->sendMessage(Variables::Prefix . "§cThe chosen skin is not available!");
            } else {

                if(CosmeticsManager::ownedCape($player, $cape)){
                    $oldSkin = $player->getSkin();
                    $capeData = Main::getInstance()->createCape($cape);
                    $setCape = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $capeData, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());

                    $player->setSkin($setCape);
                    $player->sendSkin();

                    $player->sendMessage(Variables::Prefix . "§aChanged your cape to " . $cape);

                    $pdata->set($player->getXuid(), $cape);
                    $pdata->save();
                } else {
                    //$player->sendForm(self::getBuyCapeForm($cape));
                    $player->sendMessage(Variables::Prefix . "§cYou didnt own this cape!");
                }
            }
        });
        $form->setTitle(" ");
        foreach(Main::getInstance()->getAllCapes() as $capes) {
            $form->addButton("§b" . CosmeticsManager::getCapeFormText($player, $capes), -1, "quza/textures/ui/ui_png/cape.png", $capes);
        }
        return $form;
    }

    /*public static function getRankForm(){
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if ($data === null) {
                return true;
            }
            switch($data){
                case 0:
                    $shopvip = new Config($this->getDataFolder() . "rank\Rank.yml", Config::YAML);
                    if ($shopvip->exists($player->getXuid())){
                        RankManager::setPlayerRank($player, VIP);
                        $player->sendMessage("You was changed the ranked to VIP");
                    } else {
                        $shopvipForm = new ModalForm("VIP Rank");
                        $shopvipForm->setAccpeyText("Buy");
                        $shopvipForm->setDenyText("No");

                        $shopvipForm->setAccpetListener(function (Player $player){
                            $price = 600;
                            $shopvipForm = new Config($this->getDataFolder() . "shop\Rank.yml", Config::YAML);
                            if (PlayerManager::getPlayerCoin($player) >= $price){
                                PlayerManager::reducePlayerCoin($player, $price);
                                $player->sendMessage("§aSuccessfully bought VIP");
                                $shopvipForm->set($player->getXuid(), true);
                                $shopvipForm->save();
                            } else{
                                $player->sendMessage("§cYou dont have enough money!");
                            }
                        });
                    }
            }
        });
        $form->setTitle("Rank");
        $form->addButton("VIP");

        return $form;
    }*/


    public static function getDuelForm(): MenuForm{
        return new MenuForm(
            "§7DUELS",
            "§bThere Are 2 Types of Duel Modes, §7Unranked Duels & Ranked Duels",
            [
                new MenuOption("§bUnranked", new FormIcon("quza/textures/ui/ui_png/unranked_duel.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("§bRanked", new FormIcon("quza/textures/ui/ui_png/ranked_duel.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        //$player->sendMessage(Variables::Prefix . "§aComing Soon...");
                        $player->sendForm(self::getUnrankedForm());
                        break;
                    case 1:
                        $player->sendMessage(Variables::Prefix . "§aComing Soon...");
                        //$player->sendForm(self::getRankedForm());

                        break;
                }
            },
            function (Player $selector): void{

            }
        );
    }

    public static function getUnrankedForm(): MenuForm{

        $duel = Main::getInstance()->getServer()->getPluginManager()->getPlugin("Unranked-Sumo");
        $duel2 = Main::getInstance()->getServer()->getPluginManager()->getPlugin("Unranked-Fist");

        return new MenuForm(
            "§b" . "§fUNRANKED DUEL",
            "§bHey, Which Duel Gamemode Would You Like To Play? §7Choose from our selection of games.",
            [
                new MenuOption("Nodebuff\n§7 " . \vixikhd\duels3\arena\Arena::$queue3 . " §7Queuing", new FormIcon("quza/textures/ui/ui_png/nodebuff.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("Sumo\n§7 " . \vixikhd\duels\arena\Arena::$queue3 . " §7Queuing", new FormIcon("quza/textures/ui/ui_png/sumo.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("Fist\n§7 " . \vixikhd\duels2\arena\Arena::$queue3. " §7Queuing", new FormIcon("quza/textures/ui/ui_png/fist.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("Fireballfight\n§7 " . \owonico\fireballfight\arena\Arena::$queuef . " §7Queuing", new FormIcon("quza/textures/ui/ui_png/builduhcffa.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("Bridge\n§7 " . \owonico\bridgeduel\arena\Arena::$queue3 . " §7Queuing", new FormIcon("quza/textures/ui/ui_png/bridge.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("Midfight\n§7 " , /*vixikhd\duels6\arena\Arena::$queue6 . "§7Queuing",*/ new FormIcon("quza/textures/ui/ui_png/combo.png", FormIcon::IMAGE_TYPE_PATH)),
                //new MenuOption("Bedwars\n§7 " , /*vixikhd\duels7\arena\Arena::$queue7 . "§7Queuing",*/ new FormIcon("quza/textures/ui/ui_png/bedwars.png", FormIcon::IMAGE_TYPE_PATH)),
                //new MenuOption("Bridgefight\n§7 " , /*vixikhd\duels8\arena\Arena::$queue8 . "§7Queuing",*/ new FormIcon("quza/textures/ui/ui_png/bridgefight.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("Boxing\n§7 " . \vixikhd\bxduels3\arena\Arena::$queue3 . " §7Queuing", new FormIcon("quza/textures/ui/ui_png/boxing.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("Battlerush\n§7 " . \owonico\battlerush\arena\Arena::$queue3 . " §7Queuing", new FormIcon("quza/textures/ui/ui_png/battlerush.png", FormIcon::IMAGE_TYPE_PATH)),
                new MenuOption("Bedfight\n§7 " . \owonico\bedfight\arena\Arena::$queue3 . " §7Queuing", new FormIcon("quza/textures/ui/ui_png/bedfight.png", FormIcon::IMAGE_TYPE_PATH))
                //new MenuOption("Skywars\n§7 " , /*vixikhd\duels12\arena\Arena::$queue12 . "§7Queuing",*/ new FormIcon("quza/textures/ui/ui_png/skywars.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        //$player->getServer()->dispatchCommand($player, "dl3 random");
                        $player->sendMessage("§cNodebuff Duel Is Under Maintenance");

                        break;
	                case 1:
                        $player->getServer()->dispatchCommand($player, "dl random");

                        break;
                    case 2:
                        $player->getServer()->dispatchCommand($player, "dl2 random");
                        
                        break;
                    case 3:
                        $player->getServer()->dispatchCommand($player, "fireballfight random");
                        
                        break;
                    case 4:
                        $player->getServer()->dispatchCommand($player, "unrankedbridge random");
                        
                        break;
                    case 5:
                        $player->sendMessage("§cMidfight is under maintenance!");
                        
                        break;
                    case 6:
                        $player->getServer()->dispatchCommand($player, "bxdl3 random");
                        
                        break;
                    case 7:
                        $player->getServer()->dispatchCommand($player, "unrankedbattlerush random");

                        break;
                    case 8:
                        $player->getServer()->dispatchCommand($player, "bedfight random");
                        break;
                  /*  case 7:
                        $player->getServer()->dispatchCommand($player, "dl8 random");
                        
                        break;
                    case 8:
                        $player->getServer()->dispatchCommand($player, "bxdl3 random");
                        
                        break;
                    case 9:
                        $player->getServer()->dispatchCommand($player, "dl10 random");
                        
                        break;
                    case 10:
                        $player->getServer()->dispatchCommand($player, "dl11 random");
                        
                        break;
                    case 11:
                        $player->getServer()->dispatchCommand($player, "dl12 random");
                        
                        break;*/
                }
            },
            function (Player $selector): void{

            }
        );
    }

    public static function getRankedForm(): MenuForm{
        return new MenuForm(
            "§b",
            "",
            [
                new MenuOption("§cNodebuff"),
                new MenuOption("§bSumo"),
                new MenuOption("§eThe Bridge")
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->getServer()->dispatchCommand($player, "unrankednodebuff random");

                        break;
                    case 1:
                        $player->getServer()->dispatchCommand($player, "unrankedsumo random");

                        break;
                    case 2:
                        $player->getServer()->dispatchCommand($player, "tb random");
                        break;
                }
            },
            function (Player $selector): void{

            }
        );
    }

    public static function getRegionMenu(Player $player){
        $form = new SimpleForm(function(Player $player, int $data = null){
            if($data === null)
            {
                return true;
            }
            switch($data){
                case 0:
                    $player->sendMessage(Variables::Prefix . "§cServer Offline");
                    break;
            }
        });
        $form->setTitle("§b");
        $form->addButton("§bUSA [NA]\n§cOffline", 0, "quza/textures/ui/ui_png/region.png");
        $player->sendForm($form);
        return $form;
    }

    public static function getServerForm(): MenuForm{
        return new MenuForm(
            "§b",
            "",
            [
                new MenuOption("§bNA Region", new FormIcon("quza/textures/ui/ui_png/hyperiummc.png", FormIcon::IMAGE_TYPE_PATH))
            ],
            function (Player $player, int $selected): void{
                switch ($selected){
                    case 0:
                        $player->transfer("na.lithiummc.fun", 19130);

                        break;
                }
            },
            function (Player $selector): void{

            }
        );
    }

    public static function getReportForm()
    {

        $form = new CustomForm(function (Player $player, array $data = null){
            if($data === null){
                return true;
            }
            if($data[0] === null){
                $player->sendMessage(Variables::Prefix . "§cType Name");
                return true;
            }
            if($data[1] === null){
                $player->sendMessage(Variables::Prefix . "§cType Reason");
            }

            $player->sendMessage(Variables::Prefix . "§aSuccess sumbit the reports to staff group !");

            $webhook = new Webhook("" . Main::getInstance()->getConfig()->get("report-webhook"));
            $embed = new Embed();
            $embed->setTitle("New Report - " . Main::getInstance()->getConfig()->get("region"));
            $embed->addField("Name: ", $data[0]);
            $embed->addField("Reason: ", $data[1]);
            $embed->addField("Reporter: ", $player->getName());
            $embed->setFooter("Made by Kakashi");
            $embed->setTimestamp(new \DateTime("now"));
            $embed->setColor(0xF9F202);
            $message = new Message();
            $message->addEmbed($embed);
            $webhook->send($message);

        });
        $form->setTitle("");
        $form->addInput("§bName§f:");
        $form->addInput("§bReason§f:");
        return $form;
    }

    public static function getStatsForm($player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null){
            if($data === null){
                return true;
            }

            switch($data){

            }
        });
        $form->setTitle("§7STATS");
        $form->setContent("\n§bName§f: " . $player->getName() . "\n\n§bKills§f: " . PlayerManager::getPlayerKill($player) . "\n\n§bDeaths§f: " . PlayerManager::getPlayerDeath($player) . "\n\n§bElo§f: " . PlayerManager::getPlayerElo($player) ."\n\n§bRank§f: " . RankManager::getPlayerRank($player)->getName() . "\n\n§bCoins§f: " . PlayerManager::getPlayerCoin($player));
        return $form;
    }

    public static function getArtifactsForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return;
            }
            if ($result == 0) {
                $player->sendMessage("§aResetted the artifacts");
                $reset = new resetSkin();
                $reset->setSkin($player);
            } else {
                $clothesName = ClothesManager::$clothesTypes[0];
                if (!array_key_exists($result - 1, ClothesManager::$clothesDetails[0])) {
                    $player->sendMessage("§cAn Unexpected error occured");
                    return;
                }

                if (CosmeticsManager::ownedArtifact($player, $clothesName)) {
                    //if ($player->hasPermission($perms[ClothesManager::$clothesDetails[$clothesName][$result - 1]])) { 
                        $setskin = new setSkin();
                        $setskin->setSkin($player, ClothesManager::$clothesDetails[$clothesName][$result - 1], ClothesManager::$clothesTypes[0]);
                    } else {
                        $player->sendMessage("§cYou dont have this artifact owned!");
                        return;
                    }
                //} //else {
                 //   $setskin = new setSkin();
                //    $setskin->setSkin($player, ClothesManager::$clothesDetails[$clothesName][$result - 1], /ClothesManager::$clothesTypes[0]);
                //    $player->sendMessage("§aChanged the artifacts");
                //}
            }
        });
        $form->setTitle("Artifacts");
        $clothesName = ClothesManager::$clothesTypes[0];
        if (ClothesManager::$clothesDetails[$clothesName] != []) {
            foreach (ClothesManager::$clothesDetails[$clothesName] as $value) {
                $perms = ["lithiummc.staff"];
                //if (array_key_exists($value, $perms)) {
                    if (CosmeticsManager::ownedArtifact($player, $clothesName)) { 
                        $form->addButton($value, 0, "textures/ui/check");
                    } else {
                        $form->addButton($value, 0, "textures/ui/icon_lock");
                    }
               // } else {
               //     $form->addButton($value, 0, "textures/ui/check");
               // }
            }
        } else {
            $form->setContent("There is no " . $clothesName . " in here currently");
        }
        $form->addButton("Exit", 0, "textures/ui/redX1");
        return $form;
    }

    public static function getCrateForm(): ModalForm{

        return new ModalForm(
            "Crates",
            "§b1 crate costs §61500 coins\n§eDo you want to open it now?",
            function(Player $player, bool $choice): void {

                $coin = PlayerManager::getPlayerCoin($player);

                if($choice){
                    if($coin >= 1500){
                        PlayerManager::reducePlayerCoin($player, 1500);

                        Main::getInstance()->getScheduler()->scheduleRepeatingTask(new CrateTask($player), 20);
                        
                    } else {
                        $player->sendMessage(Variables::Prefix . "§cYou dont have enough money to open this crate!");
                    }
                }
            },
            "§aConfirm",
            "§cCancel",
        );
        
    }
}