<?php

namespace owonico\task;

/*---------------------------------
basic core uses
---------------------------------*/

use owonico\Main;

/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class BowTask extends Task {

    public $player;

    public function __construct(Player $player){
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
        if (!isset(Main::$bowCooldown[$this->player->getName()])){
            if ($this->getHandler() !== null){
                $this->getHandler()->cancel();
            }
        }
        if ($this->player->isConnected()) {
            --Main::$bowCooldown[$this->player->getName()];
            $this->player->getXpManager()->setXpLevel(Main::$bowCooldown[$this->player->getName()]);
            $this->player->getXpManager()->setXpProgress(Main::$bowCooldown[$this->player->getName()] == 5 ? 1.0 : Main::$bowCooldown[$this->player->getName()] / 5); //A good hack

            if (Main::$bowCooldown[$this->player->getName()] == 0) {
                unset(Main::$bowCooldown[$this->player->getName()]);
                $this->player->getXpManager()->setXpLevel(0);
                $this->player->getXpManager()->setXpProgress(0.0);
                $this->getHandler()?->cancel();
            }
        }
    }
}
