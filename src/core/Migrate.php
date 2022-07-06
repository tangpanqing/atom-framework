<?php

namespace atom\core;

use atom\model\Table;
use atom\model\Field;
use Exception;
use ReflectionClass;
use ReflectionException;

class Migrate
{
    static $length_map = [
        'int' => '11',
        'bigint' => '20',
        'varchar' => '250'
    ];

    static $default_map = [
        'int' => '0',
        'bigint' => '0',
        'varchar' => ''
    ];

    static $auto_increment = "AUTO_INCREMENT";
    static $comment = "COMMENT";
    static $default = "DEFAULT";
    static $default_charset = "DEFAULT CHARSET";
    static $engine = "ENGINE";

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public static function run($models)
    {
        foreach ($models as $model) {
            $class = new ReflectionClass($model);

            $localTable = self::getLocalTable($class);

            //$localFields = self::getLocalFields($class);

            $databaseFields = self::getDatabaseFields($localTable->name);

            //echo self::getCreateSql($class, $localTable, $localFields);
        }
    }

    protected static function getLocalTable($class){
        $table_arr = self::getCommentArray($class->getDocComment());
        $table = Table::fromArray($table_arr);
        if (!isset($table->engine)) $table->engine = "InnoDB";
        if (!isset($table->default_charset)) $table->default_charset = "utf8mb4";
        if (!isset($table->comment)) $table->comment = "";
        if (!isset($table->name)) $table->name = self::getTableName($class);

        return $table;
    }

    protected static function getLocalFields($class): array
    {
        $field_list = [];
        foreach ($class->getProperties() as $property) {
            $field_arr = self::getCommentArray($property->getDocComment());
            $field_arr['name'] = $property->getName();
            $field = Field::fromArray($field_arr);

            if (!isset($field->length)) {
                foreach (self::$length_map as $type => $length) {
                    if ($field->type == $type) $field->length = $field->length ?? $length;
                }
            }

            if (!isset($field->not_null)) $field->not_null = "true";

            if (!isset($field->default)) {
                foreach (self::$default_map as $type => $default) {
                    if ($field->type == $type) $field->default = $default;
                }
            }

            $field_list[] = $field;
        }

        return $field_list;
    }

    protected static function getDatabaseFields($table_name){
        var_dump($table_name);
        return [];
    }

    /**
     * @throws Exception
     */
    public static function getCreateSql($class, Table $table, $field_list): string
    {
        $s = "CREATE TABLE IF NOT EXISTS `" . $table->name . "` (\n";

        $key_arr = [];
        foreach ($field_list as $field) {
            $s .= "  " . self::getFieldStr($class->getName(), $field);

            if (isset($field->auto_increment)) {
                $key_arr[] = "  PRIMARY KEY (`" . $field->name . "`),\n";
            }

            if (isset($field->key)) {
                if ($field->key == strtolower("UNIQUE")) $key_arr[] = "  UNIQUE KEY (`" . $field->name . "`),\n";
                if ($field->key == strtolower("INDEX")) $key_arr[] = "  INDEX KEY (`" . $field->name . "`),\n";
            }
        }

        foreach ($key_arr as $v) $s .= $v;
        $s = trim($s, ",\n");
        $s .= "\n";

        $s .= ") ";
        $s .= self::$engine . "=" . $table->engine . " ";
        $s .= self::$default_charset . "=" . $table->default_charset . " ";
        $s .= self::$comment . "=" . "'" . $table->comment . "'" . "\n\n";

        return $s;
    }

    /**
     * @throws Exception
     */
    static function getFieldType($class_name, Field $field): string
    {
        if (!isset($field->type)) throw new Exception("类 " . $class_name . " 下，属性 " . $field->name . " 缺少类型，可用 @type 标注");
        foreach (self::$length_map as $type => $length) {
            if ($field->type == $type) $field->length = $field->length ?? $length;
        }
        return $field->type . "(" . $field->length . ")";
    }

    /**
     * @throws Exception
     */
    static function getFieldStr($class_name, Field $field): string
    {
        $k = [];

        //字段名
        $k[] = "`" . $field->name . "`";

        //字段类型
        $k[] = self::getFieldType($class_name, $field);

        //是否默认空
        $not_null = true;
        if (isset($field->not_null) && $field->not_null == 'false') $not_null = false;
        if ($not_null) $k[] = "NOT NULL";

        //递增
        if (isset($field->auto_increment)) $k[] = self::$auto_increment;

        //默认值
        if (!isset($arr->auto_increment)) {
            if (isset($field->default)) {
                $k[] = self::$default . " " . "'" . $field->default . "'";
            } else {
                foreach (self::$default_map as $type => $default) {
                    if ($field->type == $type) $k[] = self::$default . " " . "'" . $default . "'";
                }
            }
        }

        //注释
        if (isset($field->comment)) $k[] = self::$comment . " " . "'" . $field->comment . "'";

        return join(" ", $k) . ",\n";
    }

    static function getTableName(ReflectionClass $class): string
    {
        $class_name = $class->getName();
        $class_arr = explode("\\", $class_name);
        $class = lcfirst($class_arr[count($class_arr) - 1]);

        return self::toUnderLine($class);
    }

    public static function toUnderLine($camelCaps): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . "_" . "$2", $camelCaps));
    }

    static function getCommentArray($docComment): array
    {
        $notes = [];
        if (false !== $docComment) {
            $docArr = explode("\n", $docComment);
            unset($docArr[count($docArr) - 1]);
            unset($docArr[0]);

            foreach ($docArr as $docItem) {
                $arr = explode("@", $docItem);
                $arr[1] = trim(preg_replace('!\s+!', ' ', $arr[1]));
                $a = explode(" ", $arr[1]);
                $notes[$a[0]] = $a[1] ?? "";
            }
        }

        return $notes;
    }
}