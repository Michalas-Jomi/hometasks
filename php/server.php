<?php

require_once 'models/User.php';
require_once 'utils.php';



function badRequiest($err = null) {
    if ($err !== null)
    echo $err;
    http_response_code(400);
    exit();
}

if (!isset($_POST['action']))
    badRequiest();


session_start();
if (!isset($_SESSION['user']))
    badRequiest();


$user = $_SESSION['user'];

switch ($_POST['action']) {
    case "users":
        $users = Utils::db()->query('SELECT * FROM users')->fetchAll();
        foreach($users as $user)
            echo Utils::jsUser($user).";\n";
        break;
    case "newQuest":
        if (empty($_POST['title'])) badRequiest('Tytuł zadania jest wymagany');

        $id = 0;
        $tasks = [];
        while (isset($_POST["title_task" . ++$id])) {
            $title        = $_POST['title_task' . $id];
            $description  = $_POST['description_task' . $id];

            if (empty($title)) {
                if (empty($description)) {
                    continue;
                } else {
                    $title = $description;
                    $description = null;
                }
            }
            if (empty($description))
                $description = null;

            $tasks[] = ['title' => $title, 'description' => $description];
        }
        if (count($tasks) == 0) badRequiest('Nie uzupełniono żadnego pola Do Wykonania');

        $title = $_POST['title'];
        

        $db = Utils::db();


        $owner = null;
        if (!empty($_POST['owner'])) {
            $query = $db->prepare('SELECT id FROM users WHERE nick=:nick');
            $query->bindValue(':nick', htmlentities($_POST['owner']));
            $query->execute();

            $owner = $query->fetch();
            if (!$owner) badRequiest('Niepoprawny użytkownik: ' . $_POST['owner']);

            $owner = $owner['id'];
        }

        $expiry = null;
        if (!empty($_POST['expiry']))
            $expiry = date_create($_POST['expiry'])->format('Y-m-d H:i:s');

        $author = $_SESSION['user']->id;

        $query = $db->prepare('INSERT INTO quests(author, owner, title, expiry) VALUES (:author, :owner, :title, :expiry)');
        $query->bindValue(':author', $author);
        $query->bindValue(':owner', $owner);
        $query->bindValue(':title', htmlentities($title));
        $query->bindValue(':expiry', $expiry);
        $query->execute();

        $id = $db->lastInsertId();

        
        $rows = [];
        foreach ($tasks as $task) {
            $title       = htmlentities($task['title'],       ENT_QUOTES, "UTF-8");
            $description = htmlentities($task['description'], ENT_QUOTES, "UTF-8");
            $rows[] = "($id, '$title', '$description')";
        }
        $db->query('INSERT INTO tasks(quest, title, description) VALUES ' . join(", ", $rows));
        
        break;
    case "checkTask":
        //FD.append('task', task.id);
        //FD.append('status', ev.target.checked);

        $db = Utils::db();

        $status = $_POST['status'] === 'true' ? 1 : 0;
        $id = intval($_POST['task']);

        $db->query("UPDATE tasks SET done = $status WHERE id = $id");
        $db->query("INSERT INTO taskstate(task, user, state) VALUE ($id, {$user->id}, $status)");
        break;
    case "questRefresh":
        $id = intval($_POST['id']);

        $db = Utils::db();

        $result = $db->query("SELECT author, owner FROM quests WHERE id=$id")->fetch();
        if (!$result) badRequiest();
        
        if ($result['owner'] === null || $result['owner'] === $user->id || $result['author'] === $user->id) {
            $results = $db->query("SELECT id, title, description, done FROM tasks WHERE quest=$id")->fetchAll();
            echo "[\n";
            foreach ($results as $result)
                echo "new Task({$result['id']}, this, '{$result['title']}', '{$result['description']}', {$result['done']}),\n";
            echo "];";
        } else
            badRequiest();

        break;
    case "deleteQuest":
        $db = Utils::db();

        $id = intval($_POST['id']);

        $db->query("DELETE FROM quests WHERE id = $id AND author = {$user->id}");

        break;
    case "questDetails":
        $db = Utils::db();

        $id = intval($_POST['id']);


        $quest = $db->query("SELECT author, owner, title, expiry, created FROM quests WHERE id = $id")->fetch();

        $author = $db->query("SELECT id, nick FROM users WHERE id = {$quest['author']}")->fetch();
        $owner = $quest['owner'];
        if ($owner) $owner = $db->query("SELECT id, nick FROM users WHERE id = {$quest['owner']}")->fetch();


        $tasks = $db->query("SELECT id, title, done FROM tasks WHERE quest = $id")->fetchAll();
        

        $tasksIds = [];
        foreach ($tasks as $task) {
            $tasksIds[] = $task['id'];
        }
        $tasksIds = join(",", $tasksIds);

        $tasksStates = $db->query("SELECT task, user, state, date FROM taskstate WHERE task IN ($tasksIds) ORDER BY date")->fetchAll();


        $users = [];
        $missingUsers = [];

        $users[$author['id']] = $author['nick'];
        if ($owner) $users[$owner['id']] = $owner['nick'];

        foreach ($tasksStates as $taskState)
            if (!in_array($taskState['user'], $users))
                $missingUsers[] = $taskState['user'];
        
        if ($missingUsers) {
            $missingUsers = join(",", $missingUsers);
            $missing = $db->query("SELECT id, nick FROM users WHERE id IN ($missingUsers)")->fetchAll();
            foreach ($missing as $missingUser)
                $users[$missingUser['id']] = $missingUser['nick'];
        }

        
        function echoEl($prefix, $el) {
            if (!is_int($el)) $el = "'$el'";
            echo $prefix. ':' . $el . ',';
        }
        
        echo '{';
        echo "quest:{";
        echoEl('author', $author['nick']);
        echoEl('owner', $owner ? $owner['nick'] : 'null');
        echoEl('title', $quest['title']);
        echoEl('expiry', $quest['expiry']);
        echoEl('created', $quest['created']);
        echo '},tasks:[';
        foreach ($tasks as $task) {
            echo '{';
            // id, title, done
            echoEl('title', $task['title']);
            echoEl('done', $task['done'] ? 1 : 0);
            echo 'states: [';
            // task, user, state, date
            foreach ($tasksStates as $state) {
                if ($state['task'] != $task['id']) continue;

                echo '{';
                
                echoEl('user', $users[$state['user']]);
                echoEl('state', $state['state'] ? 1 : 0);
                echoEl('date', $state['date']);

                echo '},';
            }
            echo ']},';
        }
        echo ']';
        echo '};';
        break;
    default:
        http_response_code(404);
        exit();
    }
