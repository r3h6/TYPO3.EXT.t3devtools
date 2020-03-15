<?php

namespace R3H6\T3devtools\Seeder;

class Seed extends \ArrayObject
{
    protected $identifier;

    protected $table;

    protected $hash;

    public function __construct($table, array $values = [], $identifier = null)
    {
        parent::__construct($values);
        $this->table = $table;
        $this->identifier = $identifier ?? $values['uid'] ?? uniqid('NEW');
        $this->hash = uniqid('#', true);
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setUid(int $uid): self
    {
        $this->identifier = $uid;
        return $this;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function reset(): self
    {
        $this->exchangeArray([]);
        return $this;
    }
}