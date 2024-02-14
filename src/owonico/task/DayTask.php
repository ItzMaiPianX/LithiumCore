<?php

namespace owonico\task;

/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\Server;
use pocketmine\scheduler\Task;

class DayTask extends Task
{
    public function onRun(): void
    {
        foreach(Server::getInstance()->getWorldManager()->getWorlds() as $worlds)
        {
            $worlds->setTime(1000);
            $worlds->stopTime();
        }
    }
}