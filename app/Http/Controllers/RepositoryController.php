<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
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
            if (!$token) {
                return response()->json(['error' => 'This repository is invisible'], 403);
            }
            $user = JWTAuth::authenticate($token);
            if($user->id != $oneRepo->creator) {
                return response()->json(['error' => 'This repository is invisible'], 403);
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
    
    public function updateItems(Request $request, $repoId) {
         $this->validate($request, [
            'tags'        => 'array|required',
            'tags.*'      => 'string',
            'name'        => 'string|required',
            'description' => 'string|required',
            'status'      => 'integer|required',
            'token'       => 'string|required'
        ]);
    }
    
    public function addItems(Request $request, $repoId) {
        //dd($request);
        //dd($request->toArray());
         $this->validate($request, [
            'token' => 'string|required',
            'items' => 'array|required' 
        ]);
        
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        $thisRepo = Repository::findOrFail($repoId);
        $haveRepo = Repolist::where('repoid', $repoId)->where('type', 0)->lists('itemid');
        //dd($haveRepo->toA);
        if($thisRepo->creator != $user->id) {
            return "wrong user not allowed";
        }
        
        $addItems = $request->input('items');
        $creatLinkList = array();
        foreach ($addItems as $addItem) {
            $v = Validator::make($addItem, [
                'type' => 'integer|required|between:0,1'
            ]);
            if ($v->fails()) {
                return  "something wrong in v";
            }
             //dd(1);
            // $this->validate($addItem, [
            //     'type' => 'integer|required|between:0,1' 
            // ]);
            //dd($addItem);
            $itemType = $addItem['type'];
            if($itemType == 0) {
                 $v1 = Validator::make($addItem, [
                'repoId'        => 'integer|required'
                ]);
                if ($v1->fails()) {
                return  "something wrong in v1";
                }
                $addRepoList[] = $addItem['repoId'];
            }
            elseif($itemType == 1) {
                $v2 = Validator::make($addItem, [
                'link.tags'    => 'array',
                'link.tags.*.text'  => 'string',
                'link.tags.*.id' => 'integer',
                'link.tags.*.used' => 'integer',
                'link.id'      => 'integer',
                'link.description'  => 'string|required',
                'link.title'        => 'string|required',
                'link.url'          => 'string|required'
                ]);
                //需测试没有id的情况]
                if ($v2->fails()) {
                return  "something wrong in v2";
                }
                //dd($addItem);
                if(!empty( $addItem['link']['id'] )) {
                    //dd($addItem['link']);
                    $addLinkList[] = $addItem['link']['id'];
                }
                else {
                    //dd(1);
                    $token = str_random(6);
                    $creatLinkList[] = [ 
                        'title' => $addItem['link']['title'], 
                        'description' => $addItem['link']['description'], 
                        'url' => $addItem['link']['url'], 
                        'created_at' => date("Y-m-d H:i:s",time()),
                        'updated_at' => date("Y-m-d H:i:s",time()),
                        'getId' => $token;
                        ];
                   // $item = new Link;
                    //$item->title = $request->input('link.title');
                    //$item->creator = $user->id;
                    //$item->description = $request->input('link.description');
                   // $item->url = $request->input('link.url');
                }
            }
            else {
                return "no such type";
            }
        } 
        //dd($creatLinkList);
        $repolistInsert = array();
        if(!empty($creatLinkList)) {
           
           Link::insert($creatLinkList);
           $newLinks = Link::where('getId', $token)->lists('id');
           
        }
        if(!empty($addLinkList)) {
            $searchLinkResult = Link::whereIn('id', $addLinkList)->get();
            foreach($searchLinkResult as $Link) {
                $repolistInsert[] = [
                    'repoid' => $repoId, 
                    'type' => 1, 
                    'itemid' => $Link->id,
                    'created_at' => date("Y-m-d H:i:s",time()),
                    'updated_at' => date("Y-m-d H:i:s",time())
                    ];
            }
        }
        if(!empty($addRepoList)) {
            deMul($addRepoList);
            $addRepo = array_diff($addRepoList, $haveRepo->toArray());
            $searchRepoResult = Repository::whereIn('id', $addRepo)->get();
            foreach($searchRepoResult as $Repo) {
                $repolistInsert[] = [
                    'repoid' => $repoId, 
                    'type' => 0, 
                    'itemid' => $Repo->id,
                    'created_at' => date("Y-m-d H:i:s",time()),
                    'updated_at' => date("Y-m-d H:i:s",time())
                    ];
            }
        }
        
        if(!empty($repolistInsert)) {
            Repolist::insert($repolistInsert);
        }
        
        return "add item success";
    }
    
    public function deleteItems(Request $request, $repoId) {
         $this->validate($request, [
            'tags'        => 'array|required',
            'tags.*'      => 'string',
            'name'        => 'string|required',
            'description' => 'string|required',
            'status'      => 'integer|required',
            'token'       => 'string|required'
        ]);
    }
}
