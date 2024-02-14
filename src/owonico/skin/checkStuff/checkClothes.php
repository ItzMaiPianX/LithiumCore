<?php

namespace owonico\skin\checkStuff;

use owonico\Main;
use owonico\skin\ClothesManager;

class checkClothes
{
    public function checkClothes()
    {
        $main = Main::$instance;
        //allDirs1 is the folder for button in the main form
        //allDirs2 is the folder of clothes to chose in the deeper form
        $checkFileAvailable = [];
        if (!file_exists($main->getDataFolder() . "clothes")) {
            mkdir($main->getDataFolder() . "clothes", 0777);
        }
        $path = $main->getDataFolder() . "clothes/";
        $allDirs = scandir($path);
        foreach ($allDirs as $foldersName) {
            if (is_dir($path . $foldersName)) {
                array_push(ClothesManager::$clothesTypes, $foldersName);
                $allFiles = scandir($path . $foldersName);
                foreach ($allFiles as $allFilesName) {
                    if (strpos($allFilesName, ".json")) {
                        array_push($checkFileAvailable, str_replace('.json', '', $allFilesName));
                    }
                }
                foreach ($checkFileAvailable as $value) {
                    if (!in_array($value . ".png", $allFiles)) {
                        unset($checkFileAvailable[array_search($value, $checkFileAvailable)]);
                    }
                }
                ClothesManager::$clothesDetails[$foldersName] = $checkFileAvailable;
                sort(ClothesManager::$clothesDetails[$foldersName]);
                $checkFileAvailable = [];
            }
        }
        unset(ClothesManager::$clothesTypes[0]);
        unset(ClothesManager::$clothesTypes[1]);
        unset(ClothesManager::$clothesTypes[array_search("saveskin", ClothesManager::$clothesTypes)]);
        unset(ClothesManager::$clothesDetails["."]);
        unset(ClothesManager::$clothesDetails[".."]);
        unset(ClothesManager::$clothesDetails["saveskin"]);
        sort(ClothesManager::$clothesTypes);
    }

    public function checkCos()
    {
        $main = Main::$instance;
        //allDirs1 is the folder for button in the main form
        //allDirs2 is the folder of Cos to chose in the deeper form
        $checkFileAvailable = [];
        if (!file_exists($main->getDataFolder() . "cosplays")) {
            mkdir($main->getDataFolder() . "cosplays", 0777);
        }
        $path = $main->getDataFolder() . "cosplays/";
        $allDirs = scandir($path);
        foreach ($allDirs as $foldersName) {
            if (is_dir($path . $foldersName)) {
                array_push(ClothesManager::$cosplaysTypes, $foldersName);
                $allFiles = scandir($path . $foldersName);
                foreach ($allFiles as $allFilesName) {
                    if (strpos($allFilesName, ".json")) {
                        array_push($checkFileAvailable, str_replace('.json', '', $allFilesName));
                    }
                }
                foreach ($checkFileAvailable as $value) {
                    if (!in_array($value . ".png", $allFiles)) {
                        unset($checkFileAvailable[array_search($value, $checkFileAvailable)]);
                    }
                }
                ClothesManager::$cosplaysDetails[$foldersName] = $checkFileAvailable;
                sort(ClothesManager::$cosplaysDetails[$foldersName]);
                $checkFileAvailable = [];
            }
        }
        unset(ClothesManager::$cosplaysTypes[0]);
        unset(ClothesManager::$cosplaysTypes[1]);
        unset(ClothesManager::$cosplaysTypes[array_search("saveskin", ClothesManager::$cosplaysTypes)]);
        unset(ClothesManager::$cosplaysDetails["."]);
        unset(ClothesManager::$cosplaysDetails[".."]);
        unset(ClothesManager::$cosplaysDetails["saveskin"]);
        sort(ClothesManager::$cosplaysTypes);
    }
}