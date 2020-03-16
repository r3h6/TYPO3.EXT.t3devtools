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

    public function __construct()
    {
        $this->defaultPid = 1;
    }

    public function setDefaultPid(int $pid)
    {
        $this->defaultPid = $pid;
    }

    public function table($table): Table
    {
        return GeneralUtility::makeInstance(Table::class, $table, $this->defaultPid);
    }

    public function file($uploadDir = 'fileadmin/user_upload/import')
    {
        return GeneralUtility::makeInstance(File::class, $uploadDir);
    }

    public function run()
    {
        $this->seed($this);
    }

    abstract protected function seed($seeder);
}
