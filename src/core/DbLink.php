<?php

namespace atom\core;

class DbLink
{
    private static $instance;

    public $conn;

    public static function getInstance(): DbLink
    {
        if (self::$instance == null) {
            $s = new self();
            $s->setConn();
            self::$instance = $s;
        }

        return self::$instance;
    }

    public function getConn(): \PDO
    {
        return $this->conn;
    }

    protected function setConn()
    {
        $driver = "";
        $host = "";
        $database = "";
        $port = "";
        $username = "";
        $password = "";

        $options = [];
        $dsn = "$driver:host=$host; port=$port; dbname=$database";
        $pdo = new \PDO ($dsn, $username, $password, $options);
        $pdo->query('set names utf8');
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->conn = $pdo;
    }
}
