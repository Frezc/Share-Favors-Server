<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repository;
use App\Tag;
use App\Link;
use App\Repolist;
use App\TagRepo;
use JWTAuth;

class RepositoryController extends Controller
{
    public function index(Request $request) {
        
    }
    
    public function show(Request $request, $id) {
        $this->validate($request, [
            'showList' => 'boolean',
            'offset'   => 'integer|min:0',
            'limit'    => 'integer|min:1',
            'token'    => 'string'
        ]);
        $limit    = $request->input('limit', 50);
        $offset   = $request->input('offset', 0);
        $showlist = $request->input('showList', true);
        $token    = $request->input('token');
        $oneRepo = Repository::findOrFail($id);
        if(!$oneRepo->status){
            $user = JWTAuth::authenticate($token);
            if($user->id!=$oneRepo->creator) {
                return "wrong user";
            }
        }
        $result = array();
        $result['repository'] = $oneRepo->toArray();
        $result['repository']['tags'] = array();
        $result['list'] = array();
        foreach ($oneRepo->tags as $tag) {
            $result['repository']['tags'][] = $tag->toArray();
        }
        if($showlist) {
            $itemlist = Repolist::where('repoid', $id)->orderBy('updated_at', 'DESC')->take($limit)->get();
            foreach ($itemlist as $item) {
                //仓库为0 link是1
                if($item->type == 0) {
                    $itemdetail = Repository::findOrFail($item->itemid);
                    if(!$itemdetail->status){
                        continue;
                    }
                    setCreator($itemdetail);
                    $itemdetail->tags;
                    $result['list'][]=['type' => 0, 'repository' => $itemdetail];
                }
                else{
                     $itemdetail = Link::findOrFail($item->itemid);
                     $itemdetail->tags;
                     $result['list'][]=['type' => 1, 'link' => $itemdetail];
                }
            }
        }
        return response()->json($result);
    }

    public function store(Request $request, $id) {
        
    }

    public function update(Request $request) {
        
    }

    public function destroy(Request $request) {
        
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'tags'        => 'array|required',
            'tags.*'      => 'string',
            'name'        => 'string|required',
            'description' => 'string|required',
            'status'      => 'integer|required',
            'token'       => 'string|required'
        ]);
        $repoTags = $request->input('tags');
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        $newRepo = new Repository;
        $newRepo->name = $request->input('name');
        $newRepo->status = $request->input('status');
        $newRepo->description = $request->input('description');
        $newRepo->creator = $user->id;
        $newRepo->save();
        $tags = $request->input('tags');
        $useTags = Tag::whereIn('text', $tags)->get();
        Tag::whereIn('text',$tags)->increment('used');
        $haveTags = array();
        $tagsCreator = array();
        $tagRepoCreator = array();
        foreach($useTags as $oneTag) {
            $haveTags[] = $oneTag->text;
            $tagRepoCreator[] = array('tagid' => $oneTag->id, 'repoid' => $newRepo->id, 'created_at' => date("Y-m-d H:i:s",time()) );
        }
        $createTagsText = array_diff($tags, $haveTags);
        foreach($createTagsText as $createTagText) {
            $tagsCreator[] = array('text' => $createTagText, 'used' => 1, 'created_at' => date("Y-m-d H:i:s",time()));
        }
        if(!empty($tagsCreator)) {
            Tag::insert($tagsCreator);
        }
        $createTags = Tag::whereIn('text', $createTagsText)->get();
        foreach($createTags as $createTag) {
            $tagRepoCreator[] = array('tagid' => $createTag->id, 'repoid' => $newRepo->id, 'created_at' => date("Y-m-d H:i:s",time()));
        }
        TagRepo::insert($tagRepoCreator);
        $newRepo->tags;
        setCreator($newRepo);
        
        return response()->json($newRepo);
    }
}
