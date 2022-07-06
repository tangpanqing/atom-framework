<?php

namespace atom\traits;

use atom\core\Db;
use atom\model\WhereItem;

trait WhereTrait
{
    protected $where = [];

    public function where($obj): Db
    {
        return $this->whereEq($obj);
    }

    public function whereEq($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, '=', $v);
        }
        return $this;
    }

    public function whereIn($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, 'IN', $v);
        }
        return $this;
    }

    public function whereNotIn($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, 'NOT IN', $v);
        }
        return $this;
    }

    public function whereNotEq($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, '!=', $v);
        }
        return $this;
    }

    public function whereLt($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, '<', $v);
        }
        return $this;
    }

    public function whereLte($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, '==', $v);
        }
        return $this;
    }

    public function whereGt($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, '>', $v);
        }
        return $this;
    }

    public function whereGte($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, '>=', $v);
        }
        return $this;
    }

    public function whereBetween($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, 'BETWEEN', $v);
        }
        return $this;
    }

    public function whereNotBetween($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, 'NOT BETWEEN', $v);
        }
        return $this;
    }

    public function whereLike($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, 'LIKE', $v);
        }
        return $this;
    }

    public function whereNotLike($obj): Db
    {
        foreach ($obj as $k => $v) {
            if (!is_null($v)) $this->where[] = new WhereItem($k, 'NOT LIKE', $v);
        }
        return $this;
    }

    public function handleWhere(&$bind): string
    {
        if (empty($this->where)) return "";

        $join = [];
        foreach ($this->where as $v) {
            if ($v->opt == "IN" || $v->opt == "NOT IN") {
                $f = array_pad([], count($v->val), "?");
                $join[] = $v->key . " " . $v->opt . " " . '(' . join(',', $f) . ')';
                foreach ($v->val as $vv) $bind[] = $vv;
                break;
            }

            if ($v->opt == "BETWEEN" || $v->opt == "NOT BETWEEN") {
                $f = array_pad([], count($v->val), "?");
                $join[] = $v->key . " " . $v->opt . " " . join(' AND ', $f);
                foreach ($v->val as $vv) $bind[] = $vv;
                break;
            }

            if ($v->opt == "LIKE" || $v->opt == "NOT LIKE") {
                $val = str_replace("%", "", $v->val);
                $val_str = str_replace($val, "?", $v->val);
                $val_arr = str_split($val_str);
                foreach ($val_arr as &$n) if ($n == "%") $n = "'" . $n . "'";

                $join[] = $v->key . " " . $v->opt . " " . "CONCAT(" . join(",", $val_arr) . ")";
                $bind[] = $val;
                break;
            }

            $join[] = $v->key . $v->opt . '?';
            $bind[] = $v->val;
        }

        return " WHERE " . join(" AND ", $join);
    }
}
