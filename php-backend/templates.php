<?php


    class Templates {

        private $dbLink;


        public function __construct(){
            $this->dbLink = Connection::getInstance();
        }


        public function renameTemplate(){
            $hash = $_POST['hash'];

            if(isset($_POST['name'])) $name = trim($_POST['name']);
            else $name = 'nameless_'.time();

            try {
                $query = "UPDATE mail_templates SET name=? WHERE hash=?";
                $result = $this->doMysqliQuery("ss", [$name, $hash], $query)['effectedRows'];
            }
            catch (Exception $e){
                $result = $e->getMessage().' at line: '.$e->getLine();
            }

            return $result;
        }


        public function deleteTemplate(){
            $hash = $_POST['hash'];

            try {
                $query = "DELETE FROM mail_templates WHERE hash=?";
                $result = $this->doMysqliQuery("s", [$hash], $query)['effectedRows'];
            }
            catch (Exception $e){
                $result = $e->getMessage().' at line: '.$e->getLine();
            }

            return $result;
        }


        public function duplicateTemplate(){
            $hash = $_POST['hash'];

            try {
                $query = "SELECT name, hash, metadata, content, html FROM mail_templates WHERE hash=?";
                $mysqli = $this->doMysqliQuery("s", [$hash], $query)['mysqli'];
                $mysqli = $mysqli->get_result();
                $result = $this->copyTemplate($mysqli, $hash);
            }
            catch (Exception $e){
                $result = $e->getMessage().' at line: '.$e->getLine();
            }

            return $result;
        }


        protected function copyTemplate($mysqli, $oldHash){
            $dbLink = $this->dbLink;
            $blockArr = [];

            while($item = mysqli_fetch_assoc($mysqli)) {
                $newHash = $this->generateHash();
                $checkMysqli = mysqli_query($dbLink, "SELECT 1 from mail_templates WHERE hash='$newHash' limit 1");
                $check = mysqli_num_rows($checkMysqli);

                if ($check == 0) {
                    $name = $item['name']."-Copy";
                    $metadata = str_replace($oldHash, $newHash, $item['metadata']) ;

                    $content = addslashes($item['content']);
                    $html = addslashes($item['html']);

                    $query = "INSERT INTO mail_templates VALUES(NULL, '$name', '$newHash', '$metadata', '$content', '$html')";
                    mysqli_query($dbLink, $query) or die("Error of duplicating template " . mysqli_error($dbLink));

                    $blockArr['name'] = $name;
                    $blockArr['hash'] = $newHash;
                } else {
                    copyTemplate($mysqli, $oldHash);
                }
            }

            return json_encode($blockArr);
        }


        protected function generateHash(){
            $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
            $pass = array();
            $alphaLength = strlen($alphabet) - 1;
            for ($i = 0; $i < 7; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            return implode($pass);
        }


        public function sendEmailTemplate(){
            $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config.ini');
            $adminEmail = $config["admin_email"];

            $hash = $_POST['hash'];
            $recipient = trim($_POST['recipient']);
            $result = '';

            try {
                $query = "SELECT id, html FROM mail_templates WHERE hash=?";
                $mysqli = $this->doMysqliQuery("s", [$hash], $query)['mysqli'];
                $mysqli = $mysqli->get_result();

                while($item = mysqli_fetch_assoc($mysqli)){
                    $message = htmlspecialchars_decode($item['html']);
                    $message = stripslashes($message);

                    $from = $adminEmail;
                    $subject = '=?utf-8?B?'.base64_encode('Test email').'?=';
                    $headers = 'From: '.$from.'';
                    $headers  .= 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                    $headers .= 'X-Mailer: PHP mail script';
                    $mailResult = mail($recipient, $subject, $message, $headers);

                    if($mailResult) $result = 'success';
                }
            }
            catch (Exception $e){
                $result = $e->getMessage().' at line: '.$e->getLine();
            }

            return $result;

        }


        public function previewTemplate($isUserAuth=false){
            $dbLink = $this->dbLink;
            $hash = $_POST['hash'];

            try {
                $query = "SELECT id, html FROM mail_templates WHERE hash=?";
                $mysqli = $this->doMysqliQuery("s", [$hash], $query)['mysqli'];
                $mysqli = $mysqli->get_result();

                while($item = mysqli_fetch_assoc($mysqli)) {
                    if(!$isUserAuth){
                        $curentTime = date("Y-m-d H:i:s");
                        $userIp = $_SERVER['REMOTE_ADDR'];;
                        $query = "INSERT INTO unauthorized_preview VALUES(null, '{$item["id"]}', '$userIp', '$curentTime')";
                        mysqli_query($dbLink, $query) or die("Error insert preview visit ".mysqli_error($dbLink));
                    }

                    $result = htmlspecialchars_decode($item['html']);
                }

            }
            catch (Exception $e){
                $result = $e->getMessage().' at line: '.$e->getLine();
            }

            return $result;

        }


        public function loadTemplate(){
            $hash = $_POST['hash'];
            $result = '';

            try {
                $query = "SELECT metadata, content FROM mail_templates WHERE hash=?";
                $mysqli = $this->doMysqliQuery("s", [$hash], $query)['mysqli'];
                $mysqli = $mysqli->get_result();

                $error = 1;
                while($item = mysqli_fetch_assoc($mysqli)){
                    $error = 0;
                    $result = "[{$item['metadata']},{$item['content']}]";
                }

                if($error) $result = json_encode('error');
            }
            catch (Exception $e){
                $result = $e->getMessage().' at line: '.$e->getLine();
            }

            return $result;
        }



        public function saveTemplate(){
            $hash = $_POST['hash'];
            $metadata = $_POST['metadata'];
            $content = $_POST['content'];
            $html = htmlspecialchars($_POST['html']);

            try {
                $query = "SELECT 1 from mail_templates WHERE hash=? limit 1";
                $checkMysqli = $this->doMysqliQuery("s", [$hash], $query)['mysqli'];
                $checkMysqli = $checkMysqli->get_result();

                $check = mysqli_num_rows($checkMysqli);

                if (!$check && $hash){
                    if(!empty($_POST['name'])) $name = $_POST['name'];
                    else $name = 'nameless_'.time();

                    $query = "INSERT INTO mail_templates VALUES(NULL, ?, ?, ?, ?, ?)";
                    $this->doMysqliQuery("sssss", [$name, $hash, $metadata, $content, $html], $query);
                }
                else {
                    $query = "UPDATE mail_templates SET metadata=?, content=?, html=? WHERE hash=?";
                    $this->doMysqliQuery("ssss", [$metadata, $content, $html, $hash], $query);
                }
            }
            catch (Exception $e){
                return $e->getMessage().' at line: '.$e->getLine();
            }
        }


        public function listTemplate($isUserAuth=false){
            $dbLink = $this->dbLink;
            $type = $_POST['type'];
            $result = '';

            try {
                $query = "SELECT name, hash, metadata, content FROM mail_templates ORDER BY id";
                $mysqli = mysqli_query($dbLink, $query)
                    or die("Error of getting all templates list" . mysqli_error($dbLink));

                while($item = mysqli_fetch_assoc($mysqli)) {
                    $editLink = 'editor.php?id=' . $item['hash'] . '&name=' . $item['name'];
                    $previewLink = 'index.php?page=preview&id=' . $item['hash'];
                    $itemHash = $item['hash'];
                    $optionBlock = '';

                    if ($isUserAuth && $type == 'full') {
                        $optionBlock =
                            '<div class="btn-group float-right">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Options <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a class="edit dropdown-item" href="' . $editLink . '" target="_blank">Edit</a></li>
                                <li><a data-id="' . $itemHash . '" class="rename dropdown-item" href="#x">Rename</a></li>
                                <li><a data-id="' . $itemHash . '" class="duplicate dropdown-item" href="#x">Duplicate</a></li>
                                <li><a class="preview dropdown-item" href="' . $previewLink . '" target="_blank">Preview</a></li>
                                <li><a data-id="' . $itemHash . '" class="send_email dropdown-item" href="#x">Send Email</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a data-id="' . $itemHash . '" class="delete dropdown-item text-danger" href="#x">Delete</a></li>
                            </ul>
                        </div>';
                    }
                    else $editLink = $previewLink;

                    $result .= '<li class="list-group-item">
                                <a href="' . $editLink . '" class="float-left template-name" target="_blank">' . $item['name'] . '</a>
                                ' . $optionBlock . '
                                <div class="clearfix"></div>
                            </li>';
                }
            }
            catch (Exception $e){
                $result = $e->getMessage().' at line: '.$e->getLine();
            }

            return $result;
        }


        protected function doMysqliQuery($prepareParam, $prepareParamsArr, $query){
            $dbLink = $this->dbLink;
            $resArr = array();

            $mysqli = $dbLink->prepare($query);
            $mysqli->bind_param($prepareParam, ...$prepareParamsArr);
            $mysqli->execute();

            $resArr['effectedRows'] = mysqli_affected_rows($dbLink);
            $resArr['mysqli'] = $mysqli;

            return $resArr;
        }



    }