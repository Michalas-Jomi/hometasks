<?php

session_start();

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <?= require 'templates/head.php' ?>
    <link rel="stylesheet" href="css/login.css"/>
</head>
<body>
    <div id="container">

        <div id="loginContainer">
            <header><h1>Rejestracja</h1></header>
            <form action="php/register.php" method="POST">
                <label class="inputtext"><span>Login: </span>        <input type="text"     placeholder="Login"         required name="login"></label>
                <label class="inputtext"><span>Hasło: </span>        <input type="password" placeholder="Hasło"         required name="password"></label>
                <label class="inputtext"><span class="longtext">Powtórz Hasło: </span><input type="password" placeholder="Powtórz Hasło" required name="confirm_password"></label>
                <input type="submit" value="Zarejestruj">
                <?php 
                    if (isset($_SESSION['error'])) {
                        echo "<span class=\"error\">{$_SESSION['error']}!</span>";
                        
                        $filled = $_SESSION['filled_input'];
                        echo "<script>";
                        foreach ($filled as $id => $value)
                            echo "document.getElementsByName('$id').forEach(el => el.setAttribute('value', '$value'));\n";
                        echo "</script>";

                        unset($_SESSION['error']);
                        unset($_SESSION['filled_input']);
                    }
                ?>
            </form>
        </div>

        <?= require 'templates/footer.php' ?>
    </div>
</body>
</html>