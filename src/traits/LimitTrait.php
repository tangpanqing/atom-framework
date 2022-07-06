<?php

namespace atom\traits;

use atom\core\Db;

trait LimitTrait
{
    protected $offset;
    protected $page;
    protected $limit;

    public function offset($num): Db
    {
        $this->offset = $num;
        return $this;
    }

    public function page($num): Db
    {
        $this->page = $num;
        return $this;
    }

    public function limit($num): Db
    {
        $this->limit = $num;
        return $this;
    }

    public function handleLimit(): string
    {
        if (empty($this->limit)) return "";

        $offset = 0;
        if (isset($this->offset)) $offset = $this->offset;
        if (isset($this->page)) $offset = ($this->page - 1) * $this->limit;

        return " Limit " . $offset . "," . $this->limit;
    }
}
