<?php

namespace owonico\provider;

use owonico\Main;

class MySQL{

    public static ?\mysqli $db = null;

    public static function init(){

        $host = Main::getInstance()->getConfig()->get("host");
        $username = Main::getInstance()->getConfig()->get("username");
        $password = Main::getInstance()->getConfig()->get("password");
        $database = Main::getInstance()->getConfig()->get("schema");
        $port = Main::getInstance()->getConfig()->get("port");

        self::$db = new \mysqli($host, $username, $password, $database, $port);
        //                                     Host                       Username                       Password                            Schema                 Port
        //self::$db = new \mysqli("p:51.79.173.175", "u105378_dkLPVIVmt3", "Q!1HJeou4l7=hX^^5HFVPp63", "s105378_quza_database", 3306);
        //self::$db = new \mysqli("51.79.173.175", "u105378_51SadUAEKD", "wsTHPGjRKqEfU=n5C4U+D3ge", "s105378_quza_test", 3306);
        //self::$db = mysqli_connect("notexmc.net", "u6_RcbqAQnztl", "+x1aGeZGwKVe6Cc55x+^bi1d", "s6_test", 3306);

        if(self::$db->connect_error) {
            throw new \RuntimeException("Failed to connect to the MySQL database: " . self::$db->connect_error);
        }

        //Main::getInstance()->getLogger()->info("Loaded Database");

        //$resource = Main::getInstance()->getResource("mysql.sql");

        //self::$db->multi_query(stream_get_contents($resource));

        //fclose($resource);
    }

    public static function getDatabase(): \mysqli{
        $host = Main::getInstance()->getConfig()->get("host");
        $username = Main::getInstance()->getConfig()->get("username");
        $password = Main::getInstance()->getConfig()->get("password");
        $database = Main::getInstance()->getConfig()->get("schema");
        $port = Main::getInstance()->getConfig()->get("port");

        if (self::$db->ping()) {
            //echo "Our connection is ok!\n";
        } else {
            self::$db = new \mysqli($host, $username, $password, $database, $port);

            return self::$db;
        }

        return self::$db;
    }
}
