<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Repository;
use App\Tag;
use App\Link;
use App\Repolist;
use App\TagRepo;
use App\TagLink;
use App\TagItem;
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
        $guest = 1;
        try{
            $oneRepo = Repository::findOrFail($id);
        } catch(\Exception $e) {
            return response()->json(['error' => 'no this repository'], 404);
        }
        if( !empty($token) ) {
            try{
                $user = JWTAuth::authenticate($token);
            } catch(\Exception $e) {
                $guest = 1;
            }
            if( !empty($user) ) {
                if($user->id == $oneRepo->creator_id) {
                    $guest = 0;
                }
            }
        }
        if(!$oneRepo->status){
            if($guest) {
                return response()->json(['error' => 'This repository is invisible'], 403);
            }
        }
        $result = array();
        $oneRepo->tags;
        $result['repository'] = $oneRepo;
        $result['items'] = array();
        $searchRepo = array();
        $searchLink = array();
        
        $maxLen = Repolist::where('repo_id', $id)->count();
        if($showlist) {
            $itemlist = Repolist::where('repo_id', $id)
                                ->orderBy('updated_at', 'DESC')
                                ->skip($offset)
                                ->take($limit)
                                ->get();
            foreach ($itemlist as $item) {
                //1为links，0为repositaries
                if($item->type == 0) {
                    if(!$item->status && $guest) {
                        continue;
                    }
                    $searchRepo[] = $item->item_id;
                }
                if($item->type == 1) {
                    if(!$item->status && $guest) {
                        continue;
                    }
                    $searchLink[] = $item->item_id;
                }
            }
            if( !empty($searchLink) ) {
                $item_link = Link::where('id', $searchLink)->orderBy('updated_at', 'DESC')->get();
                addTagsToLink($item_link);
                foreach( $item_link as $link) {
                    $result['items'][] = ['link' => $link, 'type' => 1];
                }
            }
            if( !empty($searchRepo) ) {
                $item_Repo = Repository::where('id', $searchRepo)->orderBy('updated_at', 'DESC')->get();
                addTagsToRepo($item_Repo);
                foreach( $item_Repo as $repo) {
                    $result['items'][] = ['repository' => $repo, 'type' => 0];
                }
            }
        }
        $result['maxlen'] = $maxLen;
        $result['len'] = count($searchRepo) + count($searchLink);
        return response()->json($result);
    }

    public function update(Request $request, $id) {
        $this->validate($request, [
            'token' => 'string|required',
            'tags'  => 'array',
            'tags.*' => 'string',
            'status' => 'integer',
            'description' => 'string',
            'title' => 'string'
        ]);
        
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        try{ 
            $thisRepo = Repository::findOrFail($id);
        } catch(\Exceptions $e) {
            return response()->json(['error' => 'repository not found'], 404);
        }    
        if($thisRepo->creator_id != $user->id) {
            return response()->json(['error' => 'wrong user'], 403);
        }
        if( empty($request->tags) && 
            empty($request->status) && 
            empty($request->description) && 
            empty($request->name) ) {
                return response()->json(['error' => 'nothing was input,please check your input'], 400);
            }
        if( !empty($request->status) ) {
            $thisRepo->status = $request->status;
        }
        if( !empty($request->description) ) {
            $thisRepo->description = $request->description;
        }
        if( !empty($request->title) ) {
            $thisRepo->title = $request->title;
        }
        $thisRepo->save();
        $thisRepo->tags;
        return response()->json($thisRepo);
    }

    public function destroy(Request $request, $id) {
        //dd($request->toArray());
         $this->validate($request, [
            'token' => 'string|required',
            'repoName' => 'string|required' 
        ]);
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        try{
            $thisRepo = Repository::where('id', $id)->where('name', $request->repoName)->firstOrFail();
        } catch(\Exceptions $e) {
            return response()->json(['error' => 'repository not found'], 404);
        }
        if($thisRepo->creator_id != $user->id) {
            return response()->json(['error' => 'wrong user'], 403);
        }
        $thisRepo->delete();
        
        return response()->json(['delete success']);
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'tags'        => 'array|required',
            'tags.*'      => 'string',
            'title'        => 'string|required',
            'description' => 'string|required',
            'status'      => 'integer|required',
            'token'       => 'string|required'
        ]);
        $repoTags = $request->input('tags');
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        
        $newRepo = new Repository;
        $newRepo->title = $request->input('title');
        $newRepo->status = $request->input('status');
        $newRepo->description = $request->input('description');
        $newRepo->creator_id = $user->id;
        $newRepo->creator_name = $user->nickname;
        $newRepo->save();
        
        $tags = $request->input('tags');
        $useTags = Tag::whereIn('text', $tags)->lists('text');
        $tagsCreator = array();
        $tagRepoCreator = array();
        $createTagsText = array_diff($tags, $useTags->toArray());
        if( !empty($createTagsText) ){
            foreach($createTagsText as $createTagText) {
                $tagsCreator[] = array('text' => $createTagText, 
                                       'used' => 1, 
                                       'created_at' => date("Y-m-d H:i:s",time()),
                                       'updated_at' => date("Y-m-d H:i:s",time())
                                      );
            }
        }
        if(!empty($tagsCreator)) {
            Tag::insert($tagsCreator);
        }
        $addTags = Tag::whereIn('text', $tags)->get();
        if( !empty($addTags) ) {
            foreach($addTags as $addTag) {
                $tagRepoCreator[] = array('tag_id' => $addTag->id, 
                                          'item_id' => $newRepo->id, 
                                          'tagitems_type' => 'App\Repository',
                                          'created_at' => date("Y-m-d H:i:s",time()),
                                          'updated_at' => date("Y-m-d H:i:s",time())
                                          );
            }
            TagItem::insert($tagRepoCreator);
        }
        
        $newRepo->tags;
        $newRepo['repoNum'] = 0;
        $newRepo['linkNum'] = 0;
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
         $this->validate($request, [
            'token' => 'string|required',
            'items' => 'array|required' 
        ]);
        $getIdByToken = str_random(6);
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        $thisRepo = Repository::findOrFail($repoId);
        if($thisRepo->creator_id != $user->id) {
            return response()->json(['error' => "wrong user not allowed"], 403);
        }
        $haveRepo = Repolist::where('repo_id', $repoId)->where('type', 0)->lists('item_id');
        $addItems = $request->input('items');
        $creatLinkList = array();
        foreach ($addItems as $addItem) {
            $v = Validator::make($addItem, [
                'type' => 'integer|required|between:0,1'
            ]);
             if ($v->fails()) {
                return  response()->json(['error' => "something wrong in type"], 400);
            }
            $itemType = $addItem['type'];
            if($itemType == 0) {
                $v1 = Validator::make($addItem, ['repoId' => 'integer|required']);
                if ($v1->fails()) {
                    return  response()->json(['error' => "something wrong in repoId"], 400);
                }
                $addRepoList[] = $addItem['repoId'];
            }
            elseif($itemType == 1) {
                $v2 = Validator::make($addItem, [
                'link.tags'    => 'array',
                'link.tags.*.text'  => 'string',
                'link.tags.*.id' => 'integer',
                'link.tags.*.used' => 'integer',
                'link.id'      => 'integer'
                ]);
                //需测试没有id的情况]
                if ($v2->fails()) {
                     return  response()->json(['error' => "something wrong in link input"], 400);
                }
                if(!empty( $addItem['link']['id'] )) {
                    $addLinkList[] = $addItem['link']['id'];
                }
                else {
                    $v_linkDetail = Validator::make($addItem, [
                        'link.description'  => 'string|required',
                        'link.title'        => 'string|required',
                        'link.url'          => 'string|required'
                    ]);
                    if ($v_linkDetail->fails()) {
                     return  response()->json(['error' => "if you don't know link id please input title url and description"], 400);
                    }
                    $creatLinkList[] = [ 
                        'title' => $addItem['link']['title'],
                        'repo_id' => $repoId, 
                        'description' => $addItem['link']['description'], 
                        'url' => $addItem['link']['url'], 
                        'created_at' => date("Y-m-d H:i:s",time()),
                        'updated_at' => date("Y-m-d H:i:s",time()),
                        'getId' => $getIdByToken
                        ];
                }
            }
            else {
                 return response()->json(['error' => "no such type"], 400);
            }
        } 
        $repolistInsert = array();
        if(!empty($addLinkList)) {
            $searchLinkResult = Link::whereIn('id', $addLinkList)->get();
            foreach($searchLinkResult as $Link) {
                $creatLinkList[] = [ 
                        'title' => $Link->title, 
                        'repo_id' => $repoId,
                        'description' => $Link->description, 
                        'url' => $Link->url, 
                        'created_at' => date("Y-m-d H:i:s",time()),
                        'updated_at' => date("Y-m-d H:i:s",time()),
                        'getId' => $getIdByToken
                        ];
            }
        }
        if(!empty($creatLinkList)) {
           Link::insert($creatLinkList);
           $newLinks = Link::where('getId', $getIdByToken)->lists('id');
           Link::where('getId', $getIdByToken)->update(array('getId' => null));
           foreach($newLinks as $Link) {
                $repolistInsert[] = [
                    'repo_id' => $repoId, 
                    'type' => 1, 
                    'item_id' => $Link,
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
                if($Repo->status == 1 || ($Repo->status == 0 && $Repo->creator_id == $user->id) ) {
                    $repolistInsert[] = [
                        'repo_id' => $repoId, 
                        'type' => 0, 
                        'item_id' => $Repo->id,
                        'status' => $Repo->status,
                        'created_at' => date("Y-m-d H:i:s",time()),
                        'updated_at' => date("Y-m-d H:i:s",time())
                        ];
                }
            }
        }
        
        if(!empty($repolistInsert)) {
            Repolist::insert($repolistInsert);
        }
        
        return "add item success";
    }
    
    public function deleteItems(Request $request, $repoId) {
         $this->validate($request, [
            'token' => 'string|required',
            'items' => 'array|required' 
        ]);
        
         $token = $request->input('token');
         $delItems = $request->input('items');
         $user = JWTAuth::authenticate($token);
         $thisRepo = Repository::findOrFail($repoId);
         if($thisRepo->creator_id != $user->id) {
            return response()->json(["wrong user not allowed"],403);
        }
        $repoDelList = array();
        $linkDelList = array();
        foreach($delItems as $delItem) {
            $v = Validator::make($delItem, [
                'type' => 'integer|required|between:0,1',
                'id'   => 'integer|required'
            ]);
            if ($v->fails()) {
                return  response()->json([ "something wrong in type:".$addItem['type']." or id:".$addItem['id'] ],400);
            }
            
            if($delItem['type'] == 0) {
                $repoDelList[] = $delItem['id']; 
            }
            
            if($delItem['type'] == 1) {
                $linkDelList[] = $delItem['id'];
            }
        }
        
        Repolist::where('repo_id', $repoId)->where('type', 0)->whereIn('item_id', $repoDelList)->delete();
        Repolist::where('repo_id', $repoId)->where('type', 1)->whereIn('item_id', $linkDelList)->delete();
        Link::whereIn('id', $linkDelList)->delete();
        TagItem::whereIn('item_id', $linkDelList)->where('tagitems_type', 'App\Link')->delete();
        
        return "items delete success";
    }
    
    public function addTags(Request $request, $repoId) {
         $this->validate($request, [
            'token' => 'string|required',
            'tags' => 'array|required' ,
            'tags.*' => 'string|required'
        ]);
        
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        try{ 
            $thisRepo = Repository::findOrFail($repoId);
        } catch(\Exceptions $e) {
            return response()->json(['error' => 'please input right repoid'], 400);
        }
       
        if($thisRepo->creator_id != $user->id) {
            return response()->json(['error' => 'wrong user'], 403);
        }
        $hadTags = array();
        foreach($thisRepo->tags as $tag) {
            $hadTags[] = $tag->text;
        }
        foreach($tags = $request->input('tags') as $tag) {
            $requestTags[] = str_replace(' ', '',$tag);
        }
        $searchTagsByText = array_diff($requestTags, $hadTags);
        $haveTags = Tag::whereIn('text', $searchTagsByText)->lists('text');
        $creatTags = array_diff($searchTagsByText, $haveTags->toArray());
        $tagInsert = array();
        foreach($creatTags as $creatTag) {
            $tagInsert[] = [
                'text' => $creatTag,
                'used' => 0,
                'created_at' => date("Y-m-d H:i:s",time()),
                'updated_at' => date("Y-m-d H:i:s",time())
            ];
        }
        Tag::insert( $tagInsert );
        Tag::whereIn('text', $searchTagsByText)->increment('used');
        $tagsDetail = Tag::whereIn('text', $searchTagsByText)->get();
        $tagRepoList = array();
        foreach ($tagsDetail as $tag) {
            $tagRepoList[] = [
                'item_id' => $repoId, 
                'tag_id' => $tag->id,
                'tagitems_type' =>  'APP\Repository',
                'created_at' => date("Y-m-d H:i:s",time()),
                'updated_at' => date("Y-m-d H:i:s",time())
            ];
        }
        TagItem::insert( $tagRepoList );
        return response()->json($tagsDetail);
    }
    
    public function delTags(Request $request, $repoId) {
        $this->validate($request, [
            'token' => 'string|required',
            'tags' => 'array|required' ,
            'tags.*' => 'string|required'
        ]);
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        try{ 
            $thisRepo = Repository::findOrFail($repoId);
        } catch(\Exceptions $e) {
            return response()->json(['error' => 'please input right repoid'], 400);
        }
       
        if($thisRepo->creator_id != $user->id) {
            return response()->json(['error' => 'wrong user'], 403);
        }
        
        foreach($tags = $request->input('tags') as $tag) {
            $searchTagsByText[] = str_replace(' ', '',$tag);
        }
        $delTags = Tag::whereIn('text', $searchTagsByText)->get();
        Tag::whereIn('text', $searchTagsByText)->decrement('used');
        $delTagItem = array();
        foreach ($delTags as $delTag) {
            $delTagItem[] = $delTag->id;
        }
        if( !empty($delTagItem) ) {
            TagItem::whereIn('tag_id', $delTagItem)
                   ->where('item_id', $repoId)
                   ->where('tagitems_type', 'App\Repository')
                   ->delete();
        }
        return response()->json($delTags);
    }
}
