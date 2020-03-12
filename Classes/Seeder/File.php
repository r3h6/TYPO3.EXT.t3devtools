<?php

namespace R3H6\T3devtools\Seeder;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class File
{
    protected $files;

    protected $seeder;


    public function __construct(DatabaseSeeder $seeder, $files)
    {
        $this->seeder = $seeder;
        $this->files = $files;
    }

    public function each($callback)
    {
        $faker = $this->seeder->getFaker();
        foreach ($this->files as $file) {
            call_user_func($callback, $file, $faker);
        }
        return $this;
    }

    public function random($amount)
    {
        $keys = (array) array_rand($this->files, $amount);
        $return = [];
        foreach ($keys as $key) {
            $return[] =  $this->files[$key];
        }
        return $return;
    }

    public function one()
    {
        return $this->random(1)[0];
    }
}