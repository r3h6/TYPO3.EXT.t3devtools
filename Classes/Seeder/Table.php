<?php

namespace R3H6\T3devtools\Seeder;

use R3H6\T3devtools\Seeder\DatabaseSeeder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Table
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var \R3H6\T3devtools\Seeder\DatabaseSeeder
     */
    protected $seeder;

    public function __construct(DatabaseSeeder $seeder, $table)
    {
        $this->seeder = $seeder;
        $this->table = $table;
    }

    public function each($callback): self
    {
        $faker = $this->seeder->getFaker();
        $data = $this->seeder->getData();
        $seeds = $data[$this->table];
        foreach ($seeds as $uid => $values) {
            /** \R3H6\T3devtools\Seeder\Seed $seed */
            $seed = GeneralUtility::makeInstance(Seed::class, $this->table, $uid, $values);
            call_user_func($callback, $seed, $faker);
            $seeds[$seed->getIdentifier()] = $seed->getArrayCopy();
        }
        $data[$this->table] = $seeds;
        return $this;
    }

    public function random($amount)
    {
        $data = $this->seeder->getData();
        $seeds = $data[$this->table];
        $uids = array_keys($seeds);
        $keys = (array) array_rand($uids, $amount);
        $return = [];
        foreach ($keys as $key) {
            $return[] =  $uids[$key];
        }
        return implode(',', $return);
    }

    public function one()
    {
        return $this->random(1);
    }
}