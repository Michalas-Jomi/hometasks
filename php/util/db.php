<?php

try {
    return new PDO(
        'mysql:host=localhost;dbname=hometasks;charset=utf8',
        'hometasksphp',
        '@sq!1knl53B08QW8',
        [
        PDO::ATTR_EMULATE_PREPARES => false, 
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
} catch(PDOException $err) {
    exit('Problem z Bazą danych');
}

