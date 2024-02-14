<?php

namespace owonico\task;

/*---------------------------------
basic core uses
---------------------------------*/

use owonico\Main;
use owonico\manager\PlayerManager;
use owonico\utils\Utils;
use owonico\Variables;
use owonico\manager\RankManager;

/*---------------------------------
basic libs uses
---------------------------------*/

use maipian\scoreboard\scoreboard;

/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\World;

class Base extends Task{

    public $plugin;
    public $player;

    public function __construct(Main $plugin, Player $player){
        $this->plugin = $plugin;
        $this->player = $player;
    }

    public function onRun(): void
    {
        if($this->player == null){
            $this->getHandler()?->cancel();
        }

        if (!$this->player->isConnected()){
            if ($this->getHandler() !== null){
                $this->getHandler()->cancel();
            }
        }
        if($this->player->isConnected()) {

            $this->player->setNameTag(Main::getPlayerOS($this->player) . RankManager::getPlayerRank($this->player)->getRankColor() . ($this->player->getDisplayName()));

            $this->player->setScoreTag("§b" . $this->player->getNetworkSession()->getPing() . " §rMS§8 |§b " . Main::getInstance()->getCPS($this->player) . " §rCPS");

            if(isset(PlayerManager::$nickedplayer[$this->player->getName()])){
                $name = PlayerManager::$nickedname[$this->player->getName()];

                $this->player->setDisplayName($name);

            }
            
            if($this->player->getWorld()->getFolderName() == Server::getInstance()->getWorldManager()->getDefaultWorld()->getFolderName()){

                if(isset(PlayerManager::$nickedplayer[$this->player->getName()])){
                    $this->player->sendPopup("§bYou are currently nicked");
                }
            }

        }
        


        if (isset($this->plugin->chatcooldown[$this->player->getName()]) && $this->plugin->chatcooldown[$this->player->getName()] != 0) {
            $this->plugin->chatcooldown[$this->player->getName()]--;
        }
        if(isset($this->plugin->chatcooldown[$this->player->getName()]) && $this->plugin->chatcooldown[$this->player->getName()] == 0){
            unset($this->plugin->chatcooldown[$this->player->getName()]);
        }
    }

}