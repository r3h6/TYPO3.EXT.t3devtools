<?php

namespace R3H6\T3devtools\Seeder;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class DatabaseSeeder implements SeederInterface
{


    /**
     * @var \R3H6\T3devtools\Seeder\Data
     */
    protected $data;

    /**
     * @var \R3H6\T3devtools\Seeder\Faker
     */
    protected $faker;

    public function __construct()
    {
        $this->data = GeneralUtility::makeInstance(Data::class);
        $this->faker = GeneralUtility::makeInstance(Faker::class, $this->data);
    }

    public function table($table): TableFactory
    {
        return GeneralUtility::makeInstance(TableFactory::class, $this, $table);
    }

    public function file($extensions = null)
    {
        return GeneralUtility::makeInstance(FileFactory::class, $this, $extensions);
    }

    public function run()
    {
        $this->seed();
        $this->data->commit();
    }

    public function getData(): Data
    {
        return $this->data;
    }

    public function getFaker(): Faker
    {
        return $this->faker;
    }

    abstract protected function seed();
}
