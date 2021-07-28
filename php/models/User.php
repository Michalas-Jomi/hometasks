<?php

namespace models;

class User {
    var $id;
    var $nick;
    var $created;

    public function __construct($db_row) {
        $this->created = $db_row['created'];
        $this->nick = $db_row['nick'];
        $this->id = $db_row['id'];
    }

    public function toJs() {
        return "User.create({$this->id}, '{$this->nick}', '{$this->created}')";
    }
}