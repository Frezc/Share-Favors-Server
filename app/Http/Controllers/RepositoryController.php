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
use App\Item;
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
        try{
            $oneRepo = Item::findOrFail($id);
        } catch(\Exception $e) {
            return response()->json(['error' => 'no this repository'], 400);
        }
        if(!$oneRepo->status){
            if (!$token) {
                return response()->json(['error' => 'This repository is invisible'], 403);
            }
            $user = JWTAuth::authenticate($token);
            if($user->id != $oneRepo->creator) {
                return response()->json(['error' => 'This repository is invisible'], 403);
            }
        }
        //dd($oneRepo);
        formatAnObject($oneRepo);
        $result = array();
        $oneRepo->tags;
        $result['repository'] = $oneRepo;
        $result['items'] = array();
        if($showlist) {
            $itemlist = Item::where('repo_id', $id)->orderBy('updated_at', 'DESC')->skip($offset)->take($limit)->get();
            foreach($itemlist as $item) {
                if($item->type == 0) {
                    unset($item->url);
                    unset($item->type);
                    unset($item->repo_id);
                    $item->link_num = 0;
                    $item->repo_num = 0;
                    $result['items'][] = ['repository' => $item, 'type' => 0];
                }
                if($item->type ==1) {
                
                unset(
                    $item->repo_id, 
                    $item->type, 
                    $item->creator_id,
                    $item->creator_name,
                    $item->status,
                    $item->link_num,
                    $item->repo_num
                ); 
                $result['items'][] = ['link' => $item, 'type' => 1]; 
                }
            }
            $result['items'] = $itemlist;
        }
        $result['maxlen'] = Item::where('repo_id', $id)->count();
        $result['len'] = count(isset($itemlist) ? $itemlist :[] );
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
            $thisRepo = Item::where('type', 0)->findOrFail($id);
        } catch(\Exceptions $e) {
            return response()->json(['error' => 'please input right repoid'], 400);
        } 
        if($thisRepo->creator_id != $user->id) {
            return response()->json(['error' => 'wrong user'], 403);
        }
        $searchTagsByText = array();
        if( empty($request->tags) && 
            empty($request->status) && 
            empty($request->description) && 
            empty($request->title) ) {
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
        formatAnObject($thisRepo);
        return response()->json($thisRepo);
    }    

    public function destroy(Request $request, $id) {
         $this->validate($request, [
            'token' => 'string|required',
            'repoName' => 'string|required' 
        ]);
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        $thisRepo = Item::where('id', $id)
                              ->where('type', 0)
                              ->where('title', $request->repoName)
                              ->firstOrFail();
        if($thisRepo->creator_id != $user->id) {
            return response()->json(['error' => 'wrong user']);
        }
        $thisRepo->delete();
        
        return "delete success";
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'tags'       => 'array|required',
            'tags.*'      => 'string',
            'title'        => 'string|required',
            'description' => 'string|required',
            'status'      => 'integer|required',
            'token'       => 'string|required'
        ]);
        $repoTags = $request->input('tags');
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        
        $newRepo = new item;
        $newRepo->title = $request->input('title');
        $newRepo->status = $request->input('status');
        $newRepo->description = $request->input('description');
        $newRepo->type = 0;
        $newRepo->creator_id = $user->id;
        $newRepo->creator_name = $user->nickname;
        $newRepo->save();
        
        $tags = $request->input('tags');
        $useTags = Tag::whereIn('text', $tags)->get();
        Tag::whereIn('text',$tags)->increment('used');
        $haveTags = array();
        $tagsCreator = array();
        $tagRepoCreator = array();
        foreach($useTags as $oneTag) {
            $haveTags[] = $oneTag->text;
            $tagRepoCreator[] = array('tagid' => $oneTag->id, 
                                      'itemid' => $newRepo->id, 
                                      'created_at' => date("Y-m-d H:i:s",time()), 
                                      'updated_at' => date("Y-m-d H:i:s",time()) 
                                      );
        }
        $createTagsText = array_diff($tags, $haveTags);
        foreach($createTagsText as $createTagText) {
            $tagsCreator[] = array('text' => $createTagText, 
                                   'used' => 1, 
                                   'created_at' => date("Y-m-d H:i:s",time()), 
                                   'updated_at' => date("Y-m-d H:i:s",time())
                                   );
        }
        if(!empty($tagsCreator)) {
            Tag::insert($tagsCreator);
        }
        $createTags = Tag::whereIn('text', $createTagsText)->get();
        foreach($createTags as $createTag) {
            $tagRepoCreator[] = array('tagid' => $createTag->id, 
                                      'itemid' => $newRepo->id, 
                                      'created_at' => date("Y-m-d H:i:s",time()), 
                                      'updated_at' => date("Y-m-d H:i:s",time()));
        }
        TagItem::insert($tagRepoCreator);
        $newRepo->tags;
        formatAnObject($newRepo);
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
        $thisRepo = Item::where('type', 0)->findOrFail($repoId);
        if($thisRepo->creator_id != $user->id) {
            return response()->json(['error' => "wrong user not allowed"], 403);
        }
        $haveRepo = Item::where('repo_id', $repoId)->where('type', 0)->lists('id');
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
                 $v1 = Validator::make($addItem, [
                                'repoId' => 'integer|required'
                                ]);
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
                'link.id'      => 'integer',
                'link.description'  => 'string|required',
                'link.title'        => 'string|required',
                'link.url'          => 'string|required'
                ]);
                //需测试没有id的情况]
                if ($v2->fails()) {
                return  response()->json(['error' => "something wrong in link input"], 400);
                }
                if(!empty( $addItem['link']['id'] )) {
                    $addLinkList[] = $addItem['link']['id'];
                }
                else {
                    $creatLinkList[] = [ 
                        'title' => $addItem['link']['title'], 
                        'description' => $addItem['link']['description'], 
                        'url' => $addItem['link']['url'], 
                        'created_at' => date("Y-m-d H:i:s",time()),
                        'updated_at' => date("Y-m-d H:i:s",time()),
                        'repo_id' => $repoId,
                        'type' => 1
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
                    'repoid' => $repoId, 
                    'type' => 1, 
                    'itemid' => $Link,
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
            'token' => 'string|required',
            'items' => 'array|required' 
        ]);
        
         $token = $request->input('token');
         $delItems = $request->input('items');
         $user = JWTAuth::authenticate($token);
         $thisRepo = Repository::findOrFail($repoId);
         if($thisRepo->creator != $user->id) {
            return "wrong user not allowed";
        }
        $repoDelList = array();
        $linkDelList = array();
        foreach($delItems as $delItem) {
            $v = Validator::make($delItem, [
                'type' => 'integer|required|between:0,1',
                'id'   => 'integer|required'
            ]);
            if ($v->fails()) {
                return  "something wrong in type:".$addItem['type']." or id:".$addItem['id'];
            }
            
            if($delItem['type'] == 0) {
                $repoDelList[] = $delItem['id']; 
            }
            
            if($delItem['type'] == 1) {
                $linkDelList[] = $delItem['id'];
            }
        }
        
        Repolist::where('repoid', $repoId)->where('type', 0)->whereIn('itemid', $repoDelList)->delete();
        Repolist::where('repoid', $repoId)->where('type', 1)->whereIn('itemid', $linkDelList)->delete();
        Link::whereIn('id', $linkDelList)->delete();
        TagLink::whereIn('linkid', $linkDelList)->delete();
        
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
            $thisRepo = Repository::firstOrFail($repoId);
        } catch(\Exceptions $e) {
            return response()->json(['error' => 'please input right repoid'], 400);
        }
       
        if($thisRepo->creator != $user->id) {
            return response()->json(['error' => 'wrong user'], 403);
        }
        
        foreach($tags = $request->input('tags') as $tag) {
            $searchTagsByText[] = str_replace(' ', '',$tag);
        }
        $haveTags = Tag::whereIn('text', $searchTagsByText)->get();
        $creatTags = array_diff($searchTagsByText, $haveTags);
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
        $tagsDetail = Tag::whereIn('text', $searchTagsByText)->increment('used')->get();
        $tagRepoList = array();
        foreach ($tagsDetail as $tag) {
            $tagRepoList[] = [
                'repoid' => $repoId, 
                'tagid' => $tag->id, 
                'created_at' => date("Y-m-d H:i:s",time()),
                'updated_at' => date("Y-m-d H:i:s",time())
            ];
        }
        TagRepo::insert( $tagRepoList );
        return response()->json($tagsDetail);
    }
    
    public function delTags(Request $request, $repoId) {
        
    }
}
