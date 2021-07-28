<?php

session_start();

if (isset($_SESSION['user'])) {
    header('Location: board');
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <?= require 'templates/head.php' ?>
    <link rel="stylesheet" href="css/index.css"/>
</head>
<body>
    <div id="container">

        <div id="loginContainer">
            <a href="login.php" id="login">Zaloguj się</a>
            <a href="register.php" id="register">Zarejestruj się</a>
        </div>

        <?= require 'templates/footer.php' ?>
    </div>
</body>
</html>