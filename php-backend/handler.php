<?php
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    require 'connection.php';
    require 'users.php';
    require 'templates.php';

    $action = $_POST['action'];
    if(!empty($action)){
        $authPermissionActions = ['rename', 'delete', 'duplicate', 'send_email', 'load', 'save'];

        $isUserAuth = $users->isAuth();
        if($isUserAuth){
            if (empty($_POST['token']) || !hash_equals($_SESSION['csrf_token'], $_POST['token'])) {
                echo 'error_token';
                //$users->logout();
                return false;
            }
        }
        else if (in_array($action, $authPermissionActions)){
            echo 'error_auth';
            return false;
        }


        $templateObj = new Templates($users);
        $result = call_user_func_array(array($templateObj, $action."Template"), [$isUserAuth]);

        echo $result;
    }

