<?php

use Illuminate\Database\Seeder;
use App\Repository;
use App\Starlist;
use App\TagItem;
use App\Repolist;
use App\User;
use App\Tag;
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
            for ($i=0; $i < 5; $i++) {
                factory('App\Starlist')->create([
                    'user_id' => $u->id,
                    'repo_id' => rand($i*40, ($i+1)*40)
                ]);
            }
        });
        factory('App\Repository', 100)->create()->each(function($r) {
             factory('App\Repolist')->create([
                        'repo_id' => rand(1,100),
                        'type' => 0,
                        'item_id' => $r->id,
                        'status' => rand(0,1)
                    ]);
             factory('App\TagItem')->create([
                        'tag_id' => rand(1,5),
                        'item_id' => $r->id,
                        'tagitems_type' => 'App\Repository'
                    ]);
             factory('App\TagItem')->create([
                        'tag_id' => rand(6,10),
                        'item_id' => $r->id,
                        'tagitems_type' => 'App\Repository'
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
                    factory('App\TagItem')->create([
                        'tag_id' => rand(1,5),
                        'item_id' => $l->id,
                        'tagitems_type' => 'App\Link'
                    ]);
                    factory('App\TagItem')->create([
                        'tag_id' => rand(6,10),
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
                    factory('App\TagItem')->create([
                        'tag_id' => rand(1,5),
                        'item_id' => $l->id,
                        'tagitems_type' => 'App\Link'
                    ]);
                    factory('App\TagItem')->create([
                        'tag_id' => rand(6,10),
                        'item_id' => $l->id,
                        'tagitems_type' => 'App\Link'
                    ]);
                });
        $repos = Repository::where('deleted_at', null)->get();
        $users = User::all();
        $tags = Tag::all();
        foreach($repos as $thisRepo) {
            $thisRepo->repoNum = Repolist::where('repo_id', $thisRepo->id)->where('type', 0)->count();
            $thisRepo->linkNum = Repolist::where('repo_id', $thisRepo->id)->where('type', 1)->count(); 
            $thisRepo->stars = Starlist::where('repo_id', $thisRepo->id)->count();
            $thisRepo->save();
        } 
        foreach($users as $user) {
            $user->repoNum = Repository::where('creator_id', $user->id)->count();
            $user->starNum = Starlist::where('user_id', $user->id)->count();
            $user->save();
        }
        foreach($tags as $tag) {
            $tag->used = TagItem::where('tag_id', $tag->id)->count();
            $tag->save();
        }
    }
    
}
