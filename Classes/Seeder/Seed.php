<?php

namespace R3H6\T3devtools\Seeder;

class Seed extends \ArrayObject
{
    protected $identifier;

    protected $table;

    public function __construct($table, $identifier, $values)
    {
        parent::__construct($values);
        $this->table = $table;
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getTable()
    {
        return $this->table;
    }
}