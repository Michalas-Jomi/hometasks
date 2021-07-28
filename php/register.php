<?php

session_start();


$login = @$_POST['login'];
$pass  = @$_POST['password'];
$pass2 = @$_POST['confirm_password'];

if ($login == null || $pass == null || $pass2 == null) return header('Location: ../register.php');

function checkSyntax() {
    global $login, $pass, $pass2;
    
    if (strlen($login) < 3 || strlen($login) > 25)  return "Login musi zawierać od 3 do 25 znaków";
    if (strlen($pass) < 8  || strlen($pass)  > 30)  return "Hasło musi zawierać od 8 do 30 znaków";    
    if ($pass != $pass2) return "Hasła nie są identyczne";

    if (!preg_match('/^[\wąćęłńóśźżĄĆĘŁŃÓŚŹŻ]+$/i', $login)) return "Login zawiera niepoprawne znaki";


    return null;
}
function checkDatabase($db) {
    global $login;
    $query = $db->prepare('SELECT * FROM users WHERE nick=:nick');
    $query->bindValue(':nick', $login);
    $query->execute();
    
    if ($query->rowCount() > 0) return 'Podany nick jest już zajęty';

    return null;
}
function exportToDb($db) {
    global $login, $pass;

    $query = $db->prepare('INSERT INTO users(nick, password) VALUES(:nick, :password)');
    $query->bindValue(':nick',     htmlentities($login));
    $query->bindValue(':password', password_hash($pass, PASSWORD_DEFAULT));
    $query->execute();
}

$err = checkSyntax();

if ($err == null) {
    $db = require 'util/db.php';
    $err = checkDatabase($db);
    if ($err == null) {
        exportToDb($db);
        header('Location: ../registered.php');
        return;
    }
}

if ($err != null) {
    $_SESSION['error'] = $err;
    $_SESSION['filled_input'] = ['login' => $login];
    header('Location: ../register.php');
    return;
}
