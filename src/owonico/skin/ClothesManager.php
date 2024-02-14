<?php

namespace owonico\skin;

use owonico\skin\checkStuff\checkClothes;
use owonico\skin\checkStuff\checkRequirement;

class ClothesManager {

    public static array $clothesTypes = [];
    public static array $cosplaysTypes = [];
    public static array $clothesDetails = [];
    public static array $cosplaysDetails = [];

    
    public static function init(){
        $a = new checkRequirement();
        $a->checkRequirement();

        $a = new checkClothes();
        $a->checkClothes();
        $a->checkCos();
    }

}