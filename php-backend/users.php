<?php
    session_start();


    class Users {

        private $dbLink;
        private $authPagesArr = ['list', 'editor'];
        const HASH_SALT = 'o95j43hiwjrthpoiwj45ihwpriobneop;jfgp3408ghqpqh5gpqoi4hgp9q85h';

        public function __construct(){
            $this->dbLink = Connection::getInstance();
        }


        protected function getHash($password){
            $salted = $password . self::HASH_SALT;
            return hash('sha256', $salted);
        }

        public function tester($string){
            return $this->getHash($string);
        }


        public function isAuth(){
            if(isset($_SESSION["is_auth"]) && isset($_SESSION["hash"])
                && $_COOKIE['hash'] == $_SESSION["hash"])
            {
                return true;
            }
            else return false;
        }


        public function login($login, $password){
            $dbLink = $this->dbLink;

            $login = trim($login);
            $password = trim($password);

            if(!empty($login) && !empty($password)){
                $passwordHash = $this->getHash($password);

                try {
                    $mysqli = $dbLink->prepare("SELECT id FROM users WHERE login=? AND password=?");
                    $mysqli->bind_param("ss", $login, $passwordHash);
                    $mysqli->execute();

                    if(mysqli_num_rows($mysqli->get_result()) == 1){
                        $hash = $this->getHash($login.'up24hashlong');
                        $_SESSION['hash'] = $hash;
                        $_SESSION["is_auth"] = true;
                        setcookie("hash", $hash, time()+60*60*24*30);
                        header("Location: /?page=list");
                    }
                    else header("Location: /?error=1");
                }
                catch (Exception $e){
                    echo $e->getMessage();
                }
            }

            header("Location: /?error=1");
        }


        public function logout(){
            $_SESSION = array();
            session_destroy();
        }


        public function getToken(){
            $token = '';
            if($this->isAuth()){
                if (!isset($_SESSION['csrf_token'])) {
                    $this->setToken();
                }
                else $token = $_SESSION['csrf_token'];
            }

            return $token;
        }

        protected function setToken(){
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $token;
        }


        public function checkPermissions($param=false){
            if(!$param) $param = $_GET['page'];

            if(in_array($param, $this->authPagesArr)){
                if(!$this->isAuth()) {
                    header("Location: /");
                }
            }
            else if(empty($param)){
                if($this->isAuth()) header("Location: index.php?page=list");
            }
        }


    }

    $users = new Users();

    if (isset($_POST["login"]) && isset($_POST["pass"])) {
        $users->login($_POST["login"], $_POST["pass"]);
    }

    if (isset($_GET["exit"])) {
        if ($_GET["exit"] == 1) {
            $users->logout();
            header("Location: /");
        }
    }

