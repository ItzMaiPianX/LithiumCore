<?php

namespace owonico\manager;

/*---------------------------------
basic core uses
---------------------------------*/

use owonico\{Main, query\async\AsyncQuery, Variables};
use owonico\rank\Rank;
use owonico\provider\MySQL;

/*---------------------------------
basic libs uses
---------------------------------*/

use maipian\form\pmforms\MenuForm;
use maipian\form\pmforms\MenuOption;
use maipian\form\formapi\SimpleForm;

/*---------------------------------
basic pocketmine uses
---------------------------------*/

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Skin;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

use function int;

class PlayerManager{

    public static array $isCombat;
    public static array $combatTimer;
    public static array $combatOpponent;
    public static $fight = 0;

    public static array $nickedname;
    public static array $nickedplayer;
    public static array $nickedPermissions;
    public static array $nickedRank;

    public static array $blockedNick = ["Lithium", "nigger", "niger", "nigga", "niga", "motherfucker", "fuckme", "currymuncher"];


    public static function getPlayerKill(Player $player){
        //$config = new Config(Main::getInstance()->getDataFolder() . "stats/Kills.yml", Config::YAML);

        //if (!$config->exists($player->getXuid())) return 0;

        //return $config->get($player->getXuid());


        return Main::$userdata[$player->getName()]["Kill"] ?? -1;

    }

    public static function getPlayerDeath(Player $player){
        //$config = new Config(Main::getInstance()->getDataFolder() . "stats/Deaths.yml", Config::YAML);

        //if (!$config->exists($player->getXuid())) return 0;

        //return $config->get($player->getXuid());

        return Main::$userdata[$player->getName()]["Death"] ?? -1;
    }

    public static function addPlayerKill(Player $player){
        //$config = new Config(Main::getInstance()->getDataFolder() . "stats/Kills.yml", Config::YAML);

        //$count = $config->get($player->getXuid());
        //$count++;
        //$config->set($player->getXuid(), $count);
        //$config->save();

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UData SET Kills=" . (self::getPlayerKill($player) + 1) . " WHERE Name=" . $player->getName()));
        MySQL::getDatabase()->query("UPDATE UData SET Kills=" . (self::getPlayerKill($player) + 1) . " WHERE Name='" . $player->getName() . "';");

        Main::$userdata[$player->getName()]["Kill"] = self::getPlayerKill($player) + 1;
    }

    public static function addPlayerDeath(Player $player){
        //$config = new Config(Main::getInstance()->getDataFolder() . "stats/Deaths.yml", Config::YAML);

        //$count = $config->get($player->getXuid());
        //$count++;
        //$config->set($player->getXuid(), $count);
        //$config->save();

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UData SET Death=" . (self::getPlayerDeath($player) + 1) . " WHERE Name=" . $player->getName()));
        MySQL::getDatabase()->query("UPDATE UData SET Death=" . (self::getPlayerDeath($player) + 1) . " WHERE Name='" . $player->getName() . "';");

        Main::$userdata[$player->getName()]["Death"] = self::getPlayerDeath($player) + 1;
    }

    public static function getPlayerCoin(Player $player){
        //$config = new Config(Main::getInstance()->getDataFolder() . "stats/Coins.yml", Config::YAML);

        //if (!$config->exists($player->getXuid())) return 0;

        //return $config->get($player->getXuid());


        return Main::$userdata[$player->getName()]["Coin"] ?? -1;
    }

    public static function addPlayerCoin(Player $player, int $amount){
        //$config = new Config(Main::getInstance()->getDataFolder() . "stats/Coins.yml", Config::YAML);

        //$count = $config->get($player->getXuid());
        //$config->set($player->getXuid(), $count + $amount);
        //$config->save();

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UData SET Coin=" . (self::getPlayerCoin($player) + $amount) . " WHERE Name=" . $player->getName()));
        MySQL::getDatabase()->query("UPDATE UData SET Coin=" . (self::getPlayerCoin($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userdata[$player->getName()]["Coin"] = self::getPlayerCoin($player) + $amount;

    }

    public static function reducePlayerCoin(Player $player, int $coin){
        //$coins = new Config(Main::getInstance()->getDataFolder() . "stats/Coins.yml", Config::YAML);

        //if (!$coins->exists($player->getXuid())) return;

        //$playerCoin = $player->getPlayerCoin($player);
       // $coins->set($player->getXuid(), $playerCoin - $coin);
        //$coins->save();

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UData SET Coin=" . (self::getPlayerCoin($player) - $coin) . " WHERE Name=" . $player->getName()));
        MySQL::getDatabase()->query("UPDATE UData SET Coin=" . (self::getPlayerCoin($player) - $coin) . " WHERE Name='" . $player->getName() . "';");

        Main::$userdata[$player->getName()]["Coin"] = self::getPlayerCoin($player) - $coin;

    }
    public static function getPlayerPass(Player $player){

        return Main::$userdata[$player->getName()]["Pass"] ?? -1;
    }

    public static function addPlayerPass(Player $player, int $amount){

        MySQL::getDatabase()->query("UPDATE UData SET Pass=" . (self::getPlayerPass($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userdata[$player->getName()]["Pass"] = self::getPlayerPass($player) + $amount;

    }

    public static function reducePlayerPass(Player $player, int $pass){

        MySQL::getDatabase()->query("UPDATE UData SET Pass=" . (self::getPlayerPass($player) - $pass) . " WHERE Name='" . $player->getName() . "';");

        Main::$userdata[$player->getName()]["Pass"] = self::getPlayerPass($player) - $pass;

    }    
    public static function getPlayerHonour(Player $player){
    //$config = new Config(Main::getInstance()->getDataFolder() . "stats/Honour.yml", Config::YAML);

        //if (!$config->exists($player->getXuid())) return 0;

        //return $config->get($player->getXuid());


        return Main::$userdata[$player->getName()]["Honour"] ?? -1;    
        
    }
    public static function addPlayerHonour(Player $player, int $amount){
        //$config = new Config(Main::getInstance()->getDataFolder() . "stats/Honour.yml", Config::YAML);

        //$count = $config->get($player->getXuid());
        //$config->set($player->getXuid(), $count + $amount);
        //$config->save();

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UData SET Honour=" . (self::getPlayerHonour($player) + $amount) . " WHERE Name=" . $player->getName()));
        MySQL::getDatabase()->query("UPDATE UData SET Honour=" . (self::getPlayerHonour($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userdata[$player->getName()]["Honour"] = self::getPlayerHonour($player) + $amount;

    }
    public static function getPlayerElo(Player $player){

        
        return Main::$userdata[$player->getName()]["Elo"] ?? -1;
    }

    public static function addPlayerElo(Player $player, int $amount){


        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UData SET Elo=" . (self::getPlayerElo($player) + $amount) . " WHERE Name=" . $player->getName()));
        MySQL::getDatabase()->query("UPDATE UData SET Elo=" . (self::getPlayerElo($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userdata[$player->getName()]["Elo"] = self::getPlayerElo($player) + $amount;

    }

    public static function reducePlayerElo(Player $player, int $elo){
        //$coins = new Config(Main::getInstance()->getDataFolder() . "stats/Coins.yml", Config::YAML);

        //if (!$coins->exists($player->getXuid())) return;

        //$playerCoin = $player->getPlayerCoin($player);
        // $coins->set($player->getXuid(), $playerCoin - $coin);
        //$coins->save();

        //Server::getInstance()->getAsyncPool()->submitTask(new AsyncQuery("UPDATE UData SET Elo=" . (self::getPlayerElo($player) - $elo) . " WHERE Name=" . $player->getName()));
        MySQL::getDatabase()->query("UPDATE UData SET Elo=" . (self::getPlayerElo($player) - $elo) . " WHERE Name='" . $player->getName() . "';");

        Main::$userdata[$player->getName()]["Elo"] = self::getPlayerElo($player) - $elo;

    }
    
    public static function getPlayerBoxingElo(Player $player){

        
        return Main::$userelo[$player->getName()]["Boxing"] ?? -1;
    }

    public static function addPlayerBoxingElo(Player $player, int $amount){


        MySQL::getDatabase()->query("UPDATE Elo SET Boxing=" . (self::getPlayerBoxingElo($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Boxing"] = self::getPlayerBoxingElo($player) + $amount;

    }

    public static function reducePlayerBoxingElo(Player $player, int $elo){

        MySQL::getDatabase()->query("UPDATE Elo SET Boxing=" . (self::getPlayerBoxingElo($player) - $elo) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Boxing"] = self::getPlayerBoxingElo($player) - $elo;

    }
    
        public static function getPlayerBedfightElo(Player $player){

        
        return Main::$userelo[$player->getName()]["Bedfight"] ?? -1;
    }

    public static function addPlayerBedfightElo(Player $player, int $amount){


        MySQL::getDatabase()->query("UPDATE Elo SET Bedfight=" . (self::getPlayerBedfightElo($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Bedfight"] = self::getPlayerBedfightElo($player) + $amount;

    }

    public static function reducePlayerBedfightElo(Player $player, int $elo){

        MySQL::getDatabase()->query("UPDATE Elo SET Bedfight=" . (self::getPlayerBedfightElo($player) - $elo) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Bedfight"] = self::getPlayerElo($player) - $elo;

    }
    
    public static function getPlayerBridgeElo(Player $player){

        
        return Main::$userelo[$player->getName()]["Bridge"] ?? -1;
    }

    public static function addPlayerBridgeElo(Player $player, int $amount){


        MySQL::getDatabase()->query("UPDATE Elo SET Bridge=" . (self::getPlayerBridgeElo($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Bridge"] = self::getPlayerBridgeElo($player) + $amount;

    }

    public static function reducePlayerBridgeElo(Player $player, int $elo){

        MySQL::getDatabase()->query("UPDATE Elo SET Bridge=" . (self::getPlayerBridgeElo($player) - $elo) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Bridge"] = self::getPlayerBridgeElo($player) - $elo;

    }
    
        public static function getPlayerBattlerushElo(Player $player){

        
        return Main::$userelo[$player->getName()]["Battlerush"] ?? -1;
    }

    public static function addPlayerBattlerushElo(Player $player, int $amount){


        MySQL::getDatabase()->query("UPDATE Elo SET Battlerush=" . (self::getPlayerBattlerushElo($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Battlerush"] = self::getPlayerBattlerushElo($player) + $amount;

    }

    public static function reducePlayerBattlerushElo(Player $player, int $elo){

        MySQL::getDatabase()->query("UPDATE Elo SET Battlerush=" . (self::getPlayerBattlerushElo($player) - $elo) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Battlerush"] = self::getPlayerBattlerushElo($player) - $elo;

    }
    
    public static function getPlayerBuilduhcElo(Player $player){

        
        return Main::$userelo[$player->getName()]["BuildUHC"] ?? -1;
    }

    public static function addPlayerBuilduhcElo(Player $player, int $amount){


        MySQL::getDatabase()->query("UPDATE Elo SET BuildUHC=" . (self::getPlayerBuilduhcElo($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["BuildUHC"] = self::getPlayerBuilduhcElo($player) + $amount;

    }

    public static function reducePlayerBuilduhcElo(Player $player, int $elo){

        MySQL::getDatabase()->query("UPDATE Elo SET BuildUHC=" . (self::getPlayerBuilduhcElo($player) - $elo) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["BuildUHC"] = self::getPlayerBuilduhcElo($player) - $elo;

    }
    
        public static function getPlayerMidfightElo(Player $player){

        
        return Main::$userelo[$player->getName()]["Midfight"] ?? -1;
    }

    public static function addPlayerMidfightElo(Player $player, int $amount){


        MySQL::getDatabase()->query("UPDATE Elo SET Midfight=" . (self::getPlayerMidfightElo($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Midfight"] = self::getPlayerMidfightElo($player) + $amount;

    }

    public static function reducePlayerMidfightElo(Player $player, int $elo){

        MySQL::getDatabase()->query("UPDATE Elo SET Midfight=" . (self::getPlayerMidfightElo($player) - $elo) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Midfight"] = self::getPlayerMidfightElo($player) - $elo;

    }
    
    public static function getPlayerFistElo(Player $player){

        
        return Main::$userelo[$player->getName()]["Fist"] ?? -1;
    }

    public static function addPlayerFistElo(Player $player, int $amount){


        MySQL::getDatabase()->query("UPDATE Elo SET Fist=" . (self::getPlayerFistElo($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Fist"] = self::getPlayerFistElo($player) + $amount;

    }

    public static function reducePlayerFistElo(Player $player, int $elo){

        MySQL::getDatabase()->query("UPDATE Elo SET Fist=" . (self::getPlayerFistElo($player) - $elo) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Fist"] = self::getPlayerFistElo($player) - $elo;

    }
    
        public static function getPlayerSumoElo(Player $player){

        
        return Main::$userelo[$player->getName()]["Sumo"] ?? -1;
    }

    public static function addPlayerSumoElo(Player $player, int $amount){


        MySQL::getDatabase()->query("UPDATE Elo SET Sumo=" . (self::getPlayerSumoElo($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Sumo"] = self::getPlayerSumoElo($player) + $amount;

    }

    public static function reducePlayerSumoElo(Player $player, int $elo){

        MySQL::getDatabase()->query("UPDATE Elo SET Sumo=" . (self::getPlayerSumoElo($player) - $elo) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Sumo"] = self::getPlayerSumoElo($player) - $elo;

    }
    
    public static function getPlayerNodebuffElo(Player $player){

        
        return Main::$userelo[$player->getName()]["Nodebuff"] ?? -1;
    }

    public static function addPlayerNodebuffElo(Player $player, int $amount){


        MySQL::getDatabase()->query("UPDATE Elo SET Nodebuff=" . (self::getPlayerNodebuffElo($player) + $amount) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Nodebuff"] = self::getPlayerNodebuffElo($player) + $amount;

    }

    public static function reducePlayerNodebuffElo(Player $player, int $elo){

        MySQL::getDatabase()->query("UPDATE Elo SET Nodebuff=" . (self::getPlayerNodebuffElo($player) - $elo) . " WHERE Name='" . $player->getName() . "';");

        Main::$userelo[$player->getName()]["Nodebuff"] = self::getPlayerNodebuffElo($player) - $elo;

    }

    public static function sendLobbyKit(Player $player) {
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setItem(0, VanillaItems::BOWL()->setCustomName("§r§8»§b Game Selector"));
        $player->getInventory()->setItem(1, VanillaItems::IRON_SWORD()->setCustomName("§r§8»§b Play Duel"));
        $player->getInventory()->setItem(5, VanillaItems::CLOCK()->setCustomName("§r§8»§bSpectate"));
        $player->getInventory()->setItem(6, VanillaItems::BOOK()->setCustomName("§r§8»§b Stats"));
        $player->getInventory()->setItem(7, VanillaItems::DIAMOND()->setCustomName("§r§8»§b Cosmetics"));
        $player->getInventory()->setItem(8, VanillaItems::COAL()->setCustomName("§r§8»§b Settings"));
    }

    public static function sendNodebuffKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 0xCCCCCCC,0,false));
        $sword = $item->get(ItemIds::DIAMOND_SWORD, 0, 1);
        $helmet = $item->get(ItemIds::DIAMOND_HELMET, 0, 1);
        $chestplate = $item->get(ItemIds::DIAMOND_CHESTPLATE, 0, 1);
        $leggins = $item->get(ItemIds::DIAMOND_LEGGINGS, 0, 1);
        $boots = $item->get(ItemIds::DIAMOND_BOOTS, 0, 1);
        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1);
        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);

        $sword->addEnchantment($sharpness);
        $sword->addEnchantment($unbreaking);
        $helmet->addEnchantment($protection);
        $chestplate->addEnchantment($protection);
        $leggins->addEnchantment($protection);
        $boots->addEnchantment($protection);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($unbreaking);
        $leggins->addEnchantment($unbreaking);
        $boots->addEnchantment($unbreaking);
        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem($item->get(ItemIds::ENDER_PEARL, 0, 16));
        $player->getInventory()->addItem($item->get(ItemIds::SPLASH_POTION, 22, 34));
        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }

    public static function sendComboKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $sword = $item->get(ItemIds::DIAMOND_SWORD, 0, 1);
        $helmet = $item->get(ItemIds::DIAMOND_HELMET, 0, 1);
        $chestplate = $item->get(ItemIds::DIAMOND_CHESTPLATE, 0, 1);
        $leggins = $item->get(ItemIds::DIAMOND_LEGGINGS, 0, 1);
        $boots = $item->get(ItemIds::DIAMOND_BOOTS, 0, 1);
        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4);
        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);
        $sword->addEnchantment($sharpness);
        $sword->addEnchantment($unbreaking);
        $helmet->addEnchantment($protection);
        $chestplate->addEnchantment($protection);
        $leggins->addEnchantment($protection);
        $boots->addEnchantment($protection);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($unbreaking);
        $leggins->addEnchantment($unbreaking);
        $boots->addEnchantment($unbreaking);
        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem($item->get(ItemIds::ENCHANTED_GOLDEN_APPLE, 0, 8));
        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }

    public static function sendFistKit(Player $player){
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->addItem(VanillaItems::STEAK());

    }

    public static function sendSumoKit(Player $player){
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $player->getInventory()->setItem(0, VanillaItems::STICK());
        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 0xCCCCCCC,100,false));
    }

    public static function sendGappleKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();

        $player->getEffects()->clear();
        $player->setHealth(20);

        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3);
        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);

        $helmet = $item->get(ItemIds::DIAMOND_HELMET, 0, 1);
        $chestplate = $item->get(ItemIds::DIAMOND_CHESTPLATE, 0, 1);
        $leggins = $item->get(ItemIds::DIAMOND_LEGGINGS, 0, 1);
        $boots = $item->get(ItemIds::DIAMOND_BOOTS, 0, 1);

        $helmet->addEnchantment($protection);
        $chestplate->addEnchantment($protection);
        $leggins->addEnchantment($protection);
        $boots->addEnchantment($protection);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($unbreaking);
        $leggins->addEnchantment($unbreaking);
        $boots->addEnchantment($unbreaking);

        $sword = $item->get(ItemIds::DIAMOND_SWORD, 0, 1);
        $sword->addEnchantment($sharpness);
        $sword->addEnchantment($unbreaking);

        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem($item->get(ItemIds::ENCHANTED_GOLDEN_APPLE, 0, 64));
        $player->getInventory()->addItem($helmet);
        $player->getInventory()->addItem($chestplate);
        $player->getInventory()->addItem($leggins);
        $player->getInventory()->addItem($boots);

        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }

    public static function sendResistanceKit(Player $player){
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();

        $player->getInventory()->addItem(VanillaItems::DIAMOND_AXE());

        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 0xCCCCCCC, 100, false));
    }

    public static function sendBFFAKit(Player $player){
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $player->getEffects()->clear();
        $player->setHealth(20);

        $helmet = VanillaItems::IRON_HELMET();
        $chestplate = VanillaItems::IRON_CHESTPLATE();
        $leggins = VanillaItems::IRON_LEGGINGS();
        $boots = VanillaItems::IRON_BOOTS();

        $sword = VanillaItems::IRON_SWORD();
        $pickaxe = VanillaItems::DIAMOND_PICKAXE();

        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 20);

        $sword->addEnchantment($sharpness);
        $sword->addEnchantment($unbreaking);

        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem(VanillaBlocks::SANDSTONE()->asItem()->setCount(128));
        $player->getInventory()->setItem(4, VanillaItems::ENDER_PEARL()->setCount(2));
        $player->getInventory()->addItem(VanillaItems::GOLDEN_APPLE()->setCount(5));

        $pickaxe->addEnchantment($unbreaking);
        $player->getInventory()->addItem($pickaxe);

        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2);
        $helmet->addEnchantment($protection);
        $chestplate->addEnchantment($protection);
        $leggins->addEnchantment($protection);
        $boots->addEnchantment($protection);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($unbreaking);
        $leggins->addEnchantment($unbreaking);
        $boots->addEnchantment($unbreaking);

        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }

    public static function sendKnockKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        
        $player->getEffects()->clear();
        $player->setHealth(20);
        
        $bow = $item->get(ItemIds::BOW, 0, 1);
        $stick = $item->get(ItemIds::STICK, 0, 1);
        $pickaxe = $item->get(ItemIds::IRON_PICKAXE, 0, 1);
        
        $punch = new EnchantmentInstance(VanillaEnchantments::PUNCH(), 1);
        $knockback = new EnchantmentInstance(VanillaEnchantments::KNOCKBACK(), 2);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);
        $infinity = new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1);
        
        $stick->addEnchantment($knockback);
        $stick->addEnchantment($unbreaking);
        $bow->addEnchantment($punch);
        $bow->addEnchantment($unbreaking);
        $bow->addEnchantment($infinity);
        $pickaxe->addEnchantment($unbreaking);
        
        $player->getInventory()->addItem($stick);
        $player->getInventory()->addItem($bow);
        $player->getInventory()->addItem($pickaxe);
        $player->getInventory()->addItem($item->get(ItemIds::SANDSTONE, 0, 64));
        $player->getInventory()->addItem($item->get(ItemIds::SANDSTONE, 0, 64));
        $player->getInventory()->addItem($item->get(ItemIds::ENDER_PEARL, 0, 2));
        $player->getInventory()->setItem(9, VanillaItems::ARROW());
        
        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 0xCCCCCCC,100,false));
    }
    
    public static function sendMidFightKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);

        $sword = $item->get(ItemIds::DIAMOND_SWORD, 0, 1);
        $helmet = $item->get(ItemIds::DIAMOND_HELMET, 0, 1);
        $chestplate = $item->get(ItemIds::DIAMOND_CHESTPLATE, 0, 1);
        $leggins = $item->get(ItemIds::DIAMOND_LEGGINGS, 0, 1);
        $boots = $item->get(ItemIds::DIAMOND_BOOTS, 0, 1);

        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);

        $sword->addEnchantment($unbreaking);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($unbreaking);
        $leggins->addEnchantment($unbreaking);
        $boots->addEnchantment($unbreaking);

        $player->getInventory()->addItem($sword);
        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }

     public static function sendSkywarsKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);

        $sword = $item->get(ItemIds::DIAMOND_SWORD, 0, 1);
        $bow = $item->get(ItemIds::BOW, 0, 1);
        $trident = $item->get(ItemIds::TRIDENT, 0, 1);
        $helmet = $item->get(ItemIds::IRON_HELMET, 0, 1);
        $chestplate = $item->get(ItemIds::DIAMOND_CHESTPLATE, 0, 1);
        $leggins = $item->get(ItemIds::IRON_LEGGINGS, 0, 1);
        $boots = $item->get(ItemIds::IRON_BOOTS, 0, 1);

        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3);
        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(),1);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);
        $power = new EnchantmentInstance(VanillaEnchantments::POWER(), 2);
        $infinity = new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1);

        $sword->addEnchantment($sharpness);
        $sword->addEnchantment($unbreaking);
        $bow->addEnchantment($power);
        $bow->addEnchantment($infinity);
        $bow->addEnchantment($unbreaking);
        $trident->addEnchantment($unbreaking);
        $helmet->addEnchantment($protection);
        $chestplate->addEnchantment($protection);
        $leggins->addEnchantment($protection);
        $boots->addEnchantment($protection);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($unbreaking);
        $leggins->addEnchantment($unbreaking);
        $boots->addEnchantment($unbreaking);

        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem($item->get(ItemIds::PLANKS, 0, 64));
        $player->getInventory()->addItem($item->get(ItemIds::PLANKS, 0, 64));
        $player->getInventory()->addItem($bow);
        $player->getInventory()->addItem($trident);
        $player->getInventory()->addItem($item->get(ItemIds::ENDER_PEARL, 0, 3));
        $player->getInventory()->addItem($item->get(ItemIds::SPLASH_POTION, 23, 1));
        $player->getInventory()->addItem($item->get(ItemIds::GOLDEN_APPLE, 0, 3));
        $player->getInventory()->addItem($item->get(ItemIds::EGG, 0, 16));
        $player->getInventory()->setItem(9, VanillaItems::ARROW());
        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }

    public static function sendBuildUHCKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
        $rod = $item->get(ItemIds::FISHING_ROD, 0, 1);
        $sword = $item->get(ItemIds::DIAMOND_SWORD, 0, 1);
        $bow = $item->get(ItemIds::BOW, 0, 1);
        $pickaxe = $item->get(ItemIds::DIAMOND_PICKAXE, 0, 1);
        $axe = $item->get(ItemIds::DIAMOND_AXE, 0, 1);
        $helmet = $item->get(ItemIds::DIAMOND_HELMET, 0, 1);
        $chestplate = $item->get(ItemIds::DIAMOND_CHESTPLATE, 0, 1);
        $leggins = $item->get(ItemIds::DIAMOND_LEGGINGS, 0, 1);
        $boots = $item->get(ItemIds::DIAMOND_BOOTS, 0, 1);
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);
        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2);
        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2);
        $efficiency = new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2);
        $power = new EnchantmentInstance(VanillaEnchantments::POWER(), 2);
        $bow->addEnchantment($unbreaking);
        $bow->addEnchantment($power);
        $rod->addEnchantment($unbreaking);
        $sword->addEnchantment($unbreaking);
        $sword->addEnchantment($sharpness);
        $pickaxe->addEnchantment($unbreaking);
        $pickaxe->addEnchantment($efficiency);
        $axe->addEnchantment($unbreaking);
        $axe->addEnchantment($efficiency);
        $helmet->addEnchantment($unbreaking);
        $helmet->addEnchantment($protection);
        $chestplate->addEnchantment($unbreaking);
        $chestplate->addEnchantment($protection);
        $leggins->addEnchantment($unbreaking);
        $leggins->addEnchantment($protection);
        $boots->addEnchantment($unbreaking);
        $boots->addEnchantment($protection);
        $player->getInventory()->addItem($sword);
        $player->getInventory()->addItem($rod);
        $player->getInventory()->addItem($bow);  
        $player->getInventory()->addItem($item->get(ItemIds::BUCKET, 10, 1));
        $player->getInventory()->addItem($item->get(ItemIds::BUCKET, 8, 1));
        $player->getInventory()->addItem($item->get(ItemIds::GOLDEN_APPLE, 0, 8));
        $player->getInventory()->setItem(6, VanillaItems::GOLDEN_APPLE()->setCount(3));
        $player->getInventory()->addItem($item->get(ItemIds::STONE, 0, 64));
        $player->getInventory()->addItem($item->get(ItemIds::PLANKS, 0, 64));
        $player->getInventory()->addItem($item->get(ItemIds::ARROW, 0, 64));
        $player->getInventory()->addItem($pickaxe);
        $player->getInventory()->addItem($axe);
        $player->getInventory()->addItem($item->get(ItemIds::BUCKET, 10, 1));
        $player->getInventory()->addItem($item->get(ItemIds::BUCKET, 8, 1));
        $player->getInventory()->addItem($item->get(ItemIds::STONE, 0, 64));
        $player->getInventory()->addItem($item->get(ItemIds::PLANKS, 0, 64));
        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggins);
        $player->getArmorInventory()->setBoots($boots);
    }
   
    public static function sendSpectateKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);
    /*    $player->getInventory()->setItem(0, VanillaItems::EMERALD()->setCustomName("§r§8»§aEnable Flight"));
        $player->getInventory()->setItem(1, VanillaItems::REDSTONE_DUST()->setCustomName("§r§8»§cDisable Flight"));*/
        $player->getInventory()->setItem(8, VanillaItems::RED_DYE()->setCustomName("§r§8»§bBack To Hub"));
       // $player->getEffects()->add(new EffectInstance(VanillaEffects::INVISIBILITY(), 0xCCCCCCC,100, false));
    }
    
    public static function sendLKit(Player $player){
        $item = ItemFactory::getInstance();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);

        $player->getInventory()->addItem($item->get(ItemIds::POISONOUS_POTATO, 0, 1));
        
        $player->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), 0xCCCCCCC,100, false));
    }
                                    
    public static function getCombatTimer(Player $player){
        if (isset(self::$combatTimer[$player->getName()])){
            return self::$combatTimer[$player->getName()];
        }
        return 0;
    }

    public static function combatTimer(Player $player){
        if (!isset(self::$combatTimer[$player->getName()])){
            return;
        }
        --self::$combatTimer[$player->getName()];
    }

    /*public static function hasCombat(Player $player){
        if (isset(self::$isCombat[$player->getName()])){
            return self::$isCombat[$player->getName()];
        }
        return 0;
    }*/

    public static function removeCombatTimer(Player $player){
        if (!isset(self::$combatTimer[$player->getName()])){
            return;
        }
        unset(self::$combatTimer[$player->getName()]);
    }

    public static function setCombatTimer(Player $player, Player $enemy){
        self::$combatTimer[$player->getName()] = 10;
        self::$combatTimer[$enemy->getName()] = 10;
    }

    public static function removeCombatOpponent(Player $player){
        if (!isset(self::$combatOpponent[$player->getName()])){
            return;
        }
        unset(self::$combatOpponent[$player->getName()]);
    }

    public static function setCombatOpponent(Player $player, Player $enemy){
        self::$combatOpponent[$player->getName()] = $enemy->getName();
        self::$combatOpponent[$enemy->getName()] = $player->getName();
    }

    public static function getCombatOpponent(Player $player){
        if (!isset(self::$combatOpponent[$player->getName()])){
            return "";
        }
        return self::$combatOpponent[$player->getName()];
    }

    //public static function getPlayerExact(Player $player){
    //
    //}

    public static function hasCombatOpponent(Player $player){
        if (isset(self::$combatOpponent[$player->getName()])){
            return true;
        }
        return false;
    }

    public static function getOpponentsPing(Player $player){
        $opponent = Server::getInstance()->getPlayerExact(PlayerManager::getCombatOpponent($player));

        if($opponent != null){
            return $opponent->getNetworkSession()->getPing();
        }
        return 0;
    }

}