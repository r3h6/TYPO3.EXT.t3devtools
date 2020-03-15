<?php

use R3H6\T3devtools\Seeder\Seed;
use R3H6\T3devtools\Seeder\Faker;
use R3H6\T3devtools\Seeder\DatabaseSeeder;

class NewsSeeder extends DatabaseSeeder
{
    protected function seed($seeder)
    {
        // $images = $this
        //     ->file()
        //     ->create([
        //         'Test.png',
        //         'Test.jpg?200x100',
        //         'https://i.picsum.photos/id/1002/800/600.jpg',
        //         'https://loremflickr.com/g/320/240/paris',
        //     ]);

        $images = $seeder
            ->file()
            ->create(5);

        $categories = $seeder
            ->table('sys_category')
            ->create(3)
            ->each(function(Seed $seed, Faker $faker){
                $seed['title'] = $faker->word();
            });


        $news = $seeder
            ->table('tx_news_domain_model_news')
            ->create(9)
            ->each(function (Seed $seed, Faker $faker) use ($images, $categories) {
                $seed['title'] = $faker->sentence();
                $seed['teaser'] = $faker->paragraph();
                $seed['bodytext'] = $faker->text();
                $seed['datetime'] = $faker->dateTimeBetween('-3 month', '+3 month')->format('c');
                $seed['categories'] = $categories->one();

                $seed['fal_media'] = $images->one();
                // $seed['fal_related_files']

                // $seed['content_elements'] = $seeder->table('tt_content')->create(1);
            })
            ->localize('de_CH', 1)
            ->each(function (Seed $seed, Faker $faker) use ($seeder) {

            })
        ;
    }
}