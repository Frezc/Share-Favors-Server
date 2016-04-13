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
        $tag = array('呆', '萌', '蠢', '2', '黑丝', '白丝', '御姐', '萝莉', 'F♂A', '胖次');
        for ($i=0; $i < 10; $i++) { 
            factory('App\Tag')->create([
                'text' => $tag[$i]
            ]);
        }
        factory('App\User', 20)->create()->each(function($u) {
            for ($i=0; $i < 5; $i++) { 
                $u->repositories()->save(factory('App\Repository')->make() );
            }
        });
        factory('App\Repository', 100)->create()->each(function($r) {
             factory('App\Repolist')->create([
                        'repo_id' => rand(1,100),
                        'type' => 0,
                        'item_id' => $r->id,
                        'status' => rand(0,1)
                    ]);
        });
        factory('App\Link', 1000)->create([
                'repo_id' => 1,
            ])->each(function($l) {
                    factory('App\Repolist')->create([
                        'repo_id' => 1,
                        'type' => 1,
                        'item_id' => $l->id,
                        'status' => 1
                    ]);
                    factory('App\TagItem', 2)->create([
                        'tag_id' => rand(1,10),
                        'item_id' => $l->id,
                        'tagitems_type' => 'App\Link'
                    ]);
                });
        factory('App\Link', 1000)->create()->each(function($l) {
                    factory('App\Repolist')->create([
                        'repo_id' => rand(2,200),
                        'type' => 1,
                        'item_id' => $l->id,
                        'status' => 1
                    ]);
                    factory('App\TagItem', 2)->create([
                        'tag_id' => rand(1,10),
                        'item_id' => $l->id,
                        'tagitems_type' => 'App\Link'
                    ]);
                });
       factory('App\Starlist', 60)->create();
    }
}
