<?php require("php-backend/handler.php"); $users->checkPermissions('all'); ?>
<!DOCTYPE html>
<html>
<head>
    <?php include "pages/components/head.inc.php"; ?>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <?php include "pages/components/header.php"; ?>

    <main class="container jumbotron bg-white">
        <div class="container text-center">
            <div class="mt-5 mb-4">
                <h1>Email Editor</h1>
            </div>

            <ul id="saved" data-type="short"></ul>

        </div>
    </main>

    <?php include "pages/components/scripts.inc.php"; ?>
</body>
</html>
