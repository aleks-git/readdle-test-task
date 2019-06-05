<?php

    class Connection {

        private static $dbLink = null;

        protected function __construct () {
            $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config.ini');
            $host = $config["db_host"];
            $database = $config["db_name"];
            $user = $config["db_users"];
            $password = $config["db_password"];

            try {
                $dbLink = mysqli_connect($host, $user, $password, $database);
                self::$dbLink = $dbLink;
            }
            catch (Exception $e){
                echo $e->getMessage().' at line: '.$e->getLine();
            }

        }


        public static function getInstance(){

            if (self::$dbLink === null) {
                new self();
            }

            return self::$dbLink;
        }

        private function __clone () {}
        private function __wakeup () {}
    }