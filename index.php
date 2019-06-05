<?php require("php-backend/handler.php"); $users->checkPermissions(); ?>
<!DOCTYPE html>
<html>
<head>
    <?php include "pages/components/head.inc.php"; ?>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <?php
        include "pages/components/header.php";

        $page = $_GET["page"];
        switch ($page){
            case "":
                include "pages/auth.php";
                break;
            case "list":
                include "pages/list.php";
                break;
            case "preview":
                include "pages/preview.php";
                break;
            default:
                include "pages/not_found.php";
        }
    ?>

    <?php include "pages/components/scripts.inc.php"; ?>
</body>
</html>
