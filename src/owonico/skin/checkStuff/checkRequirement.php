<?php

namespace owonico\skin\checkStuff;

use owonico\skin\libs\jojoe77777\FormAPI\SimpleForm;
use owonico\skin\copyResource\copyResource;
use owonico\Main;

class checkRequirement
{
    public function checkRequirement()
    {
        $main = Main::$instance;
        if (!extension_loaded("gd")) {
            $main->getServer()->getLogger()->info("§6Clothes: Uncomment gd2.dll (remove symbol ';' in ';extension=php_gd2.dll') in bin/php/php.ini to make the plugin working");
            $main->getServer()->getPluginManager()->disablePlugin($main);
            return;
        }
        if (!file_exists($main->getDataFolder() . "steve.png") || !file_exists($main->getDataFolder() . "steve.json") || !file_exists($main->getDataFolder() . "config.yml")) {
            if (file_exists(str_replace("config.yml", "", $main->getResources()["config.yml"]))) {
                $var = new copyResource();
                $var->recurse_copy(str_replace("config.yml", "", $main->getResources()["config.yml"]), $main->getDataFolder());
            } else {
                $main->getServer()->getLogger()->info("§6Clothes: Something wrong with the resources");
                return;
            }
        }
    }
}