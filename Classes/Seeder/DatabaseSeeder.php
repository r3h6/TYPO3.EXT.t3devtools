<?php

namespace R3H6\T3devtools\Seeder;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class DatabaseSeeder
{


    /**
     * @var \R3H6\T3devtools\Seeder\Data
     */
    protected $data;

    /**
     * @var \R3H6\T3devtools\Seeder\Faker
     */
    protected $faker;

    /**
     * @var int
     */
    protected $defaultPid;

    public function __construct()
    {
        // $this->data = GeneralUtility::makeInstance(Data::class);
        // $this->faker = GeneralUtility::makeInstance(Faker::class, $this->data);
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
        // $this->data->commit();
    }

    public function getData(): Data
    {
        return $this->data;
    }

    public function getFaker(): Faker
    {
        return $this->faker;
    }

    abstract protected function seed($seeder);
}
