<?php
// REST, OAuth i JWT
// znajomości asynchronicznej komunikacji (RabbitMQ)
// znajomości podstawowych zagadnień z obszaru bezpieczeństwa aplikacji webowych (np. ataki CSRF, XSS)
// korzystałeś z Kubernetesa
// masz doświadczenie z architekturami zarządzania tożsamością (np. SAML 2.0)


// CREATE TABLE `hometasks`.`users` ( `id` INT NOT NULL AUTO_INCREMENT ,  `nick` VARCHAR(25) NOT NULL ,  `password` TINYTEXT NOT NULL ,  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,    PRIMARY KEY  (`id`),    UNIQUE  (`nick`)) ENGINE = InnoDB;
// CREATE TABLE `hometasks`.`quests` ( `id` INT NOT NULL AUTO_INCREMENT ,  `author` INT NOT NULL ,  `owner` INT NULL ,  `title` TINYTEXT NOT NULL ,  `expiry` DATETIME NULL ,  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,    PRIMARY KEY  (`id`),    INDEX  (`author`),    INDEX  (`owner`)) ENGINE = InnoDB;
// CREATE TABLE `hometasks`.`tasks` ( `id` INT NOT NULL AUTO_INCREMENT ,  `quest` INT NOT NULL ,  `title` TINYTEXT NOT NULL ,  `description` TINYTEXT NULL ,  `done` BOOLEAN NOT NULL DEFAULT FALSE ,    PRIMARY KEY  (`id`),    INDEX  (`quest`)) ENGINE = InnoDB;

// INSERT INTO `quests` (`id`, `author`, `owner`, `title`, `expiry`, `created`) VALUES (NULL, '1', NULL, 'Zadanie 1', '2021-07-24 12:20:00', current_timestamp());
// INSERT INTO `tasks` (`id`, `quest`, `title`, `description`, `done`) VALUES (NULL, '2', 'task 1', 'opis taska1', '0');

require_once '../php/models/User.php';
require_once '../php/utils.php';

session_start();


if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}
                        
$db = Utils::db();
$user = $_SESSION['user'];

function questsList($sql, $div, $checkable) {
    global $db;

    $quests = $db->query($sql)->fetchAll();

    $eol = "\n\t\t";

    foreach ($quests as $quest) {
        $owner = $quest['owner'] ? $quest['owner'] : 'null';
        $author = $quest['author'];
        
        $author =                      Utils::jsUser($db->query("SELECT * FROM users WHERE id=$author")->fetch());
        if ($owner != 'null') $owner = Utils::jsUser($db->query("SELECT * FROM users WHERE id=$owner" )->fetch());

        echo "quest = Quest.create({$quest['id']}, $author, $owner, '{$quest['title']}', '{$quest['expiry']}', '{$quest['created']}', $checkable);$eol";
        $tasks = $db->query("SELECT * FROM tasks WHERE quest={$quest['id']} ORDER BY id")->fetchAll();
        foreach ($tasks as $task)
            echo "quest.addTask(new Task({$task['id']}, quest, '{$task['title']}', '{$task['description']}', {$task['done']}));$eol";
        echo "quest.write('$div');$eol";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <?= require '../templates/head.php' ?>

    <link rel="stylesheet" href="board.css"/>
    <link rel="stylesheet" href="contextmenu.css"/>
    <link rel="stylesheet" href="main.css"/>
    <link rel="stylesheet" href="newQuest.css"/>
    <link rel="stylesheet" href="sidebar.css"/>
    <link rel="stylesheet" href="details.css"/>
    <link rel="stylesheet" href="header.css"/>
    <script src="../js/jquery.js"></script>
    <script src="../js/utils.js"></script>
    <script src="models.js"></script>
</head>
<body>

    <div id="container">

        <header>
        <h1>Hometasks</h1>
        <div class="nick">Witaj, <?= $user->nick ?></div>
        <a href="../logout.php">Wyloguj</a>
        </header>
        <main>
            <aside id="quests">
                <div id="owningQuests">
                    <header><h3>Zadanie Dla Ciebie</h3></header>
                    <ol></ol>
                </div>
                <div id="ownQuests">
                    <header><h3>Twoje Zadania</h3></header>
                    <ol></ol>
                    <div id="newQuest">
                        <header>+ Nowe Zadanie</header>
                        <form id="newQuestBody" style="display: none;">
                            <label><input placeholder="Tytuł"    type="text"              name="title" required></label>
                            <label><input placeholder="Dla kogo" list="newQuestUsersList" name="owner"></label>
                            <div><label>Termin<input                  type="datetime-local"    name="expiry"></label></div>
                            <datalist id="newQuestUsersList">
                            </datalist>
                            <fieldset id="tasks">
                                <legend>Do Wykonania</legend>
                            </fieldset>
                            <div class="error"></div>
                            <input type="submit" value="Zatwierdz">
                        </form>
                    </div>
                </div>
                <div id="questsResize"></div>
            </aside>
            
            <div id="board"></div>
        </main>
        <?= require '../templates/footer.php' ?>

    </div>
    <nav id="contextmenu">
        <ol>
            <li class="refresh">Odśwież</li>
            <li class="details">Szczegóły</li>
            <li class="delete">Usuń</li>
        </ol>
    </nav>
    <div id="detailsContainer">
        <div class="close">x</div>
        <div id="details">
            <div class="author">Autor</div>
            <div class="owner">Owner</div>
            <div class="created">Created</div>
            <div class="expiry">Expiry</div>
            Do wykonania:
            <ol class="tasks">

            </ol>
        </div>
    </div>
    
    <script>
        var quest;
        var user = <?= $user->toJs() . ';'.PHP_EOL; ?>

        <?= questsList("SELECT * FROM quests WHERE owner={$user->id}", '#owningQuests', 'true'); ?>

        <?= questsList("SELECT * FROM quests WHERE author={$user->id}", '#ownQuests',   'false'); ?>

    </script>
    <script src="main.js"></script>
    <script src="contextmenu.js"></script>
    <script src="board.js"></script>
    <script src="sidebar.js"></script>
    <script src="newQuest.js"></script>
</body>
</html>