<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repository;
use App\Tag;
use App\Link;
use App\Repolist;

use JWTAuth;

class RespositoryController extends Controller
{
    public function __construct() {
        $this->middleware('jwt.auth');
    }
    
    public function index(Request $request) {
        
    }
    
    public function show(Request $request, $id) {
        $this->validate($request, [
            'showList' => 'boolean',
            'offset'   => 'integer|min:0',
            'limit'    => 'integer|min:1',
            'token'    => 'string',
        ]);
        $limit    = $request->input('limit', 50);
        $offset   = $request->input('offset', 0);
        $showlist = $request->input('showlist', true);
        $token    = $request->input('token');
        
        $oneRepo = Repository::findOrFail($id);
        
        if(!$oneRepo->status){
            //验证用户
            $user = JWTAuth::parseToken()->authenticate();
            if($user->id != $oneRepo->creator){
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
        // foreach ($oneRepo->haveLists as $alist) {
        //     $result['lists'][] = $alist->toArray();
        // } 
        //type 0是仓库 1是link
        //获取仓库列表
        $repolist = Repolist::where('type', 0)->where('repoid', $id)->get();
        foreach ($repolist as $childrepo) {
            $repodetail = Repository::findOrFail($childrepo->itemid);
            // foreach ($oneRepodetail->tags as $repotag){
            //     $repodetail['tags'][] = $repotag;
            // }
            if(!$repodetail->status){
                continue;
            }
            $repodetail->tags;
            $item = ['type' => 0, 'repository' => $repodetail];
            $result['list'][]=$item;
        }
        //获取链接列表
        $linklist = Repolist::where('type', 1)->where('repoid', $id)->get();
        foreach ($linklist as $childlink) {
            $linkdetail = Link::findOrFail($childlink->itemid);
            $linkdetail->tags;
            $item = ['type' => 1, 'link' => $linkdetail];
            $result['list'][] = $item;
        }
        return response()->json($result);

    }

    public function store(Request $request, $id) {
        
    }

    public function update(Request $request) {
        
    }

    public function destroy(Request $request) {
        
    }
    
    public function create() {
        
    }
}
