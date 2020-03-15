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

    public function __construct($locale = \Faker\Factory::DEFAULT_LOCALE)
    {
        $this->faker = \Faker\Factory::create($locale);
    }

    public function __call(string $name , array $arguments)
    {
        return call_user_func_array([$this->faker, $name], $arguments);
    }

    public function __get(string $name)
    {
        return $this->faker->$name;
    }
}
