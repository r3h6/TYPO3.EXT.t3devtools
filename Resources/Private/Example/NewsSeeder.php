<?php

use R3H6\T3devtools\Seeder\Seed;
use R3H6\T3devtools\Seeder\Faker;
use R3H6\T3devtools\Seeder\DatabaseSeeder;

class NewsSeeder extends DatabaseSeeder
{
    protected function seed($seeder)
    {
        $images = $seeder
            ->file('tx_t3devtools/images/')
            ->create([
                'Example1.png' => 'local',
                'Example.jpg' => 'local:640x240',
                'local;640x240;gif',
                'local;;png',
                'Picsum.jpg' => 'https://i.picsum.photos/id/1002/800/600.jpg',
                'https://loremflickr.com/g/320/240/paris',
            ]);

        $files = $seeder
            ->file('tx_t3devtools/files/')
            ->create(3);

        $categories = $seeder
            ->table('sys_category')
            ->create(3)
            ->each(function(Seed $seed, Faker $faker){
                $seed['title'] = $faker->word();
            });

        $seeder
            ->table('tx_news_domain_model_news')
            ->create(1)
            ->each(function (Seed $seed, Faker $faker) use ($seeder, $images, $files, $categories) {
                $seed['title'] = $faker->sentence();
                $seed['teaser'] = $faker->paragraph();
                $seed['bodytext'] = $faker->text();
                $seed['author'] = $faker->name;
                $seed['datetime'] = $faker->dateTimeBetween('-3 month', '+3 month')->format('c');
                $seed['categories'] = $categories->one();

                $seed['fal_media'] = $images->one();
                $seed['fal_related_files'] = $files->random(2);

                $seed['content_elements'] = $seeder
                    ->table('tt_content')
                    ->create(1)
                    ->each(function(Seed $seed, Faker $faker) {
                        $seed['header'] = $faker->sentence();
                        $seed['CType'] = 'text';
                        $seed['bodytext'] = $faker->paragraph();
                    })
                ;
            })
        ;
    }
}