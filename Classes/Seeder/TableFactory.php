<?php

namespace R3H6\T3devtools\Seeder;

use R3H6\T3devtools\Seeder\DatabaseSeeder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TableFactory
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

    public function create($count)
    {
        $data = $this->seeder->getData();
        for ($i = 0; $i < $count; $i++) {
            $data[$this->table][uniqid('NEW')] = [
                'pid' => 1,
            ];
        }
        return GeneralUtility::makeInstance(Table::class, $this->seeder, $this->table);
    }
}