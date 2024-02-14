<?php

namespace owonico\task;

use pocketmine\scheduler\Task;
use owonico\Main;
use owonico\provider\MySQL;

class QueryTask extends Task{

    public $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onRun(): void
    {
        MySQL::getDatabase()->query("SELECT * FROM UData");
    }
}