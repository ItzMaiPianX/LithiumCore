<?php

namespace owonico\task;

/*---------------------------------
basic core uses
---------------------------------*/

use owonico\Main;

/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\scheduler\Task;

class BroadcastTask extends Task{

    public $plugin;

    private static $message = [
        "§c §fNot on your region? Use §b§n/region§r to switch!",
        "§c §fJoin the offical discord (§n§bhttps://dsc.gg/LithiumMC) §rto get notified of the latest updates and to participate in tournaments!",
        "§c §b§nLithiumMC S1§r is releasing soon!",
    " §fSupport our server financially by purchasing server ranks on our stone §n§blithiummc.fun/store§r."];

    private static $instance = 0;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(): void
    {
        $this->plugin->getServer()->broadcastMessage(self::$message[self::$instance]);
        self::$instance++;
        if(self::$instance > count(self::$message)-1)self::$instance = 0;
    }
}
