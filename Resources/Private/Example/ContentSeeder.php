<?php

use R3H6\T3devtools\Seeder\Seed;
use R3H6\T3devtools\Seeder\Faker;
use R3H6\T3devtools\Seeder\DatabaseSeeder;

class ContentSeeder extends DatabaseSeeder
{
    protected function seed($seeder)
    {
        $seeder
            ->table('tt_content')
            ->create(1)
            ->each(function (Seed $seed, Faker $faker) {
                $seed['CType'] = 'text';
                $seed['header'] = $faker->sentence();
                $seed['bodytext'] = $faker->realText();
            })
            ->localize('de_DE', 1)
            ->each(function (Seed $seed, Faker $faker) {
                $seed['header'] = $faker->sentence();
                $seed['bodytext'] = $faker->realText();
            })
        ;
    }
}