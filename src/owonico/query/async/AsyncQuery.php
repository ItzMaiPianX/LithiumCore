<?php

namespace owonico\query\async;

use owonico\Main;
use owonico\provider\MySQL;
use pocketmine\scheduler\Task;

class AsyncQuery extends Task{

    public $query = "";

    public function __construct(string $query){
        $this->query = $query;
    }

    public function onRun(): void
    {
        MySQL::getDatabase()->query($this->query);
        Main::getInstance()->getLogger()->info("Done Query: " . $this->query);
    }
}
