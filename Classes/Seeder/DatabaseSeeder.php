<?php

namespace R3H6\T3devtools\Seeder;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class DatabaseSeeder
{
    /**
     * @var int
     */
    protected $defaultPid;

    /**
     * @var \R3H6\T3devtools\Seeder\Faker|null
     */
    private $defaultFaker;

    public function __construct()
    {
        $this->defaultPid = 1;
        $this->defaultFaker = null;
    }

    public function setDefaultPid(int $pid)
    {
        $this->defaultPid = $pid;
    }

    public function table($table): Table
    {
        return GeneralUtility::makeInstance(Table::class, $table, $this->defaultPid, $this->defaultFaker);
    }

    public function file($uploadDir = 'fileadmin/user_upload/import')
    {
        return GeneralUtility::makeInstance(File::class, $uploadDir);
    }

    public function run()
    {
        $this->seed($this);
    }

    public function setDefaultFaker(Faker $faker)
    {
        $this->defaultFaker = $faker;
    }

    abstract protected function seed($seeder);
}
