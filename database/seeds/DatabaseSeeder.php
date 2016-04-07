<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory('App\User', 20)->create()->each(function($u) {
            for ($i=0; $i < 10; $i++) { 
                $u->repositories()->save(factory('App\Repository',20)->make()->each(function($u) {
                    $u->links()->save(factory('App\Link')->make());
                }));
            }
        });
        
        
    }
}
