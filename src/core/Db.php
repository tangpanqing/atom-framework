<?php

namespace atom\core;

use atom\model\DbRaw;
use atom\traits\WhereTrait;
use atom\traits\LimitTrait;
use PDO;

class Db
{
    use WhereTrait;
    use LimitTrait;

    protected $table_name = "";
    protected $condition = [];

    public function exists(): bool
    {
        return !empty($this->findOne());
    }

    public static function beginTransaction()
    {
        return self::getPdo()->query("BEGIN");
    }

    public static function commit()
    {
        return self::getPdo()->query("COMMIT");
    }

    public static function rollback()
    {
        return self::getPdo()->query("ROLLBACK");
    }

    public static function raw($str): DbRaw
    {
        return new DbRaw($str);
    }

    public function count($field = "*"): int
    {
        $key = "count($field)";
        $this->condition['field'] = $key;
        $res = $this->findOne();
        if (false === $res) return false;
        return $res[$key];
    }

    public function sum($field)
    {
        $key = "sum($field)";
        $this->condition['field'] = $key;
        $res = $this->findOne();

        if (false === $res) return false;
        return $res[$key];
    }

    protected function handleField()
    {
        return $this->condition['field'] ?? "*";
    }

    protected function handleGroup(): string
    {
        if (!isset($this->condition['group'])) return "";
        return " GROUP BY " . join(',', $this->condition['group']);
    }

    public function groupBy($key): Db
    {
        if (!isset($this->condition['group'])) $this->condition['group'] = [];
        $this->condition['group'][] = $key;
        return $this;
    }

    public function field($field = "*"): Db
    {
        $this->condition['field'] = $field;
        return $this;
    }

    public static function table($table_name): Db
    {
        if (is_object($table_name)) {
            $class_arr = explode("\\", get_class($table_name));
            $class = $class_arr[count($class_arr) - 1];
            $class = lcfirst($class);
            $table_name = self::toUnderLine($class);
        }

        $db = new self();
        $db->table_name = $table_name;
        return $db;
    }

    public static function toUnderLine($camelCaps): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . "_" . "$2", $camelCaps));
    }

    public static function tableWhere($data): Db
    {
        return self::table($data)->where($data);
    }

    public function findOne()
    {
        return $this->find()->fetch(PDO::FETCH_ASSOC);
    }

    public static function whereFindOne($data)
    {
        return self::table($data)->where($data)->findOne();
    }

    public function findAll()
    {
        return $this->find()->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function find()
    {
        $bind = [];
        $sql = "SELECT " . $this->handleField() . " FROM " . $this->table_name . $this->handleWhere($bind) . $this->handleGroup() . $this->handleLimit();

        $pdo = self::getPdo();
        $state = $pdo->prepare($sql);
        $state->execute($bind);

        return $state;
    }

    public static function whereFindAll($obj): array
    {
        return self::table($obj)->where($obj)->findAll();
    }

    public function insert($obj): bool
    {
        $key = [];
        $val = [];
        $bind = [];
        foreach ($obj as $k => $v) {
            if (!is_null($v)) {
                $key[] = $k;
                $val[] = "?";
                $bind[] = $v;
            }
        }

        $sql = "INSERT INTO " . $this->table_name . " (" . join(',', $key) . ") VALUES (" . join(',', $val) . ")";
        return self::getPdo()->prepare($sql)->execute($bind);
    }

    public static function add($obj): bool
    {
        return self::table($obj)->insert($obj);
    }

    public static function tableInsert($obj): bool
    {
        return Db::table($obj)->insert($obj);
    }

    public function update($obj): bool
    {
        $key = [];
        $bind = [];
        foreach ($obj as $k => $v) {
            if (!is_null($v)) {
                if (is_object($v) && "atom\model\DbRaw" == get_class($v)) {
                    $key[] = "$k=" . $v->key;
                } else {
                    $key[] = "$k=?";
                    $bind[] = $v;
                }
            }
        }

        $sql = "UPDATE " . $this->table_name . " SET " . join(',', $key) . $this->handleWhere($bind);
        return self::getPdo()->prepare($sql)->execute($bind);
    }

    public function delete(): bool
    {
        $bind = [];
        $sql = "DELETE FROM " . $this->table_name . $this->handleWhere($bind);
        return self::getPdo()->prepare($sql)->execute($bind);
    }

    public function deleteObj($obj): bool
    {
        return self::table($obj)->where($obj)->delete();
    }

    public static function whereDelete($obj): bool
    {
        return self::table($obj)->where($obj)->delete();
    }

    public static function getPdo(): PDO
    {
        return DbLink::getInstance()->getConn();
    }
}