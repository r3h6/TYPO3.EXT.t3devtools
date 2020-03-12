<?php

namespace R3H6\T3devtools\Seeder;

use R3H6\T3devtools\Seeder\Data;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Faker
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \R3H6\T3devtools\Seeder\Data
     */
    protected $data;

    public function __construct(Data $data)
    {
        $this->data = $data;
        $this->faker = \Faker\Factory::create();
        $this->faker->addProvider(GeneralUtility::makeInstance(FileReferenceProvider::class, $this));
    }

    public function __call(string $name , array $arguments)
    {
        return call_user_func_array([$this->faker, $name], $arguments);
    }

    public function __get(string $name)
    {
        return $this->faker->$name;
    }

    public function getData()
    {
        return $this->data;
    }
}
