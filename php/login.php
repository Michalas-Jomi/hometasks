<?php

require_once 'models/User.php';

session_start();


$login = @$_POST['login'];
$pass  = @$_POST['password'];

if ($login == null || $pass == null || !preg_match('/^[\wąćęłńóśźżĄĆĘŁŃÓŚŹŻ]+$/i', $login)) return header('Location: ../login.php');


function login() {
    global $login, $pass;
    $db = require 'util/db.php';
    
    $query = $db->prepare('SELECT * FROM users WHERE nick=:nick');
    $query->bindValue(':nick', htmlentities($login));
    $query->execute();

    if ($query->rowCount() == 0) return 'Nie poprawny Login';

    $user = $query->fetch();

    if (!password_verify($pass, $user['password'])) return 'Niepoprawne Hasło';

    $_SESSION['user'] = new \models\User($user);

    return null;
}

$err = login();
if ($err != null) {
    $_SESSION['error'] = $err;
    $_SESSION['filled_input'] = ['login' => $login];

    header('Location: ../login.php');
    return;
}

header('Location: ../board');
