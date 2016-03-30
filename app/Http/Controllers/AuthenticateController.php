<?php

namespace App\Http\Controllers;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\EmailVerification;
use App\User;
use App\Repository;
use App\Tag;
use App\Repolist;
use App\Link;
use App\TagRepo;
use App\TagLink;
use App\Exceptions;
use App\Item;
use Hash;

class AuthenticateController extends Controller
{
    public function auth(Request $request) {
        
        $this->validate($request, [
            'email' => 'email|required',
            'password' => 'required'
        ]);
        
        $credentials = $request->only('email', 'password');
        
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        //dd(1);
        try {
            $user = User::where('email', $credentials['email'])->firstOrFail();
        } catch (\Exceptions $e) {
            return response()->json(['error' => 'no this user or wrong email'], 400);
        }
        //$starlist = array();
        //$starlist = [];
        //$starlist = ["nickname" => $user->nickname, "email" => $user->email];
        $user->starlist;
        //$user['starlist']
        addTagsToItems($user['starlist']);
        //getRecentItems($user['starlist']);
        //dd($user);
        //addTagsToItems($user['starlist']);
        $repoList = Item::where('creator_id', $user->id)->where('type', 0)->orderBy('updated_at')->get();
        foreach($repoList as $repo) {
            unset($repo->url);
        }
        addTagsToItems($repoList);
        //getRecentItems($repoList);
        // foreach( $user->starlist as $repodetail){
        //     循环查表设置名字，待优化
        //     setCreator($repodetail);
        //     $repodetail->tags;            
        //     $starlist[] = $repodetail->toArray();
        // }
        //if($starlist == null)
        //$userdetail = ["sign" => $user->sign, "starlist" =>$starlist, "nickname" => $user->nickname, "email" => $user->email];
        $package = [
            "token" => $token, 
            "expired_at" => time()+86400, 
            "user" => ['starlist' => getRecentItems($user['starlist'], 0),
                        'nickname' => $user->nickname, 
                        'email' => $user->email 
                      ],
            "repostories" => getRecentItems($repoList, 0)
            ];
        //dd(1);
        return response()->json($package);
    }
    
    public function refreshToken(Request $request) {
        $this->validate($request, [
            'token' => 'required'
        ]);
        
        //todo
        return JWTAuth::refresh($request->input('token'));
    }
    
    public function register(Request $request) {
        $this->validate($request, [
            'email'     => 'email|required|unique:users,email',
            'password'  => 'required|between:6,64',
            'nickname'  => 'required|between:1,16',
            'code'      => 'required'
        ]);
        $email = $request->input('email');
        $code = $request->input('code');
        $nickname = $request->input('nickname');
        $password = $request->input('password');
        
        try{
            $verification = EmailVerification::where('email',$email)->firstOrFail();
        }catch( \Exception  $e){
            return response()->json(['error' => "Your email needs to be authenticated."], 400);
        }
        
        if($code != $verification->code) {
            return response()->json(['error' => 'wrong code'], 400);
        }
        
        $user = new User;
        $user->email = $email;
        $user->nickname = $nickname;
        $user->password = Hash::make($password);
        $user->save();
        
        EmailVerification::where('email',$email)->delete();
        //dd(1);
        return $this->auth($request);
    }
    
    public function getUserinfo(Request $request, $id) {
        $this->validate($request, [
            'starListMax'     => 'integer',
            'repositoriesMax' => 'integer'
        ]);
        
        $starListMax = $request->input('starListMax', 3);
        $repositoriesMax = $request->input('repositoriesMax', 3);
        
        try {
            $user = User::findOrFail($id);
        } catch(\Exception $e) {
            return response()->json(['error' => 'no such user'], 400);
        }
        $userinfo = [
            'sign'     => $user->sign, 
            'email'    => $user->email, 
            'nickname' => $user->nickname,
            'id'       => $user->id
            ];
        $userinfo['repositories'] = array();
        //$userinfo['starlist'] = array();
        $starlist = $user->starlist()->where('status', 1)->orderBy('updated_at', 'DESC')->take($starListMax)->get();
        // dd(1);
        //searchCreatorFromObject($starlist);
        // foreach($starlist as $star) {
            //循环查表设置名字，待优化
        //     setCreator($star);
        addTagsToItems($starlist);
           // $star->tags;
        $userinfo['starlist'] = $starlist->toArray();
        //}
       
        $repolist = Item::where('creator_id', $id)
                        ->where('status', 1)
                        ->orderBy('updated_at', 'DESC')
                        ->take($repositoriesMax)
                        ->get();
        //searchCreatorFromObject($repolist);
        addTagsToItems($repolist);
        //foreach($repolist as $repo) {
         //   setCreatorName($repo, $user->nickname);
         //   $repo->tags;
        $userinfo['repositories'] = getRecentItems($repolist, 0);
        //}
        
        return response()->json($userinfo);
    }
    
    public function showUserRepo(Request $request, $userId) {
        $this->validate($request, [
            'offset'      => 'integer',
            'limit'       => 'integer',
            'recentItems' => 'integer|max:12',
            'token'       => 'string'
        ]);
        $token = $request->input('token');
        $limit = $request->input('limit', 12);
        $offset = $request->input('offset', 0);
        $recentItems = $request->input('recentItems', 3);
        if(!empty($token)) {
            $user = JWTAuth::authenticate($token);
             if($user->id == $userId) {
                $repoList = Item::where('creator_id', $userId)
                                    ->orderBy('updated_at', 'DESC')
                                    ->take($limit)
                                    ->get();
                $response = ['showAll' => 1];
             }
             else{
                $repoList = Item::where('creator_id', $userId)->where('status', 1)
                                  ->orderBy('updated_at', 'DESC')
                                  ->take($limit)->get();
                $response = ['showAll' => 0];
                $user = User::findOrFail($userId);
             }
        }
        else{
             $repoList = Item::where('creator_id', $userId)->where('status', 1)
                                  ->orderBy('updated_at', 'DESC')
                                  ->take($limit)->get();
             $response = ['showAll' => 0];
             $user = User::findOrFail($userId);
        }
       
        $response['repolist'] = array();
        $response['repoNumAll'] = Item::where('creator_id', $userId)->count();
        $count = 0;
        //$repodetail = array();
        foreach($repoList as $repo) {
            $repoIdList[] = $repo['id'];
             $count = $count+1;
        }
        
        $recentItemList = Item::whereIn('repo_id', $repoIdList)
                                  ->orderBy('updated_at', 'DESC')->get();
        //施工到了这里！！！！！！
        //$repodetail = array();
        $searchRepo = array();
        $searchLink = array();
        //dd($recentItemList->toArray());
        foreach($recentItemList as $item) {
            if($item->type == 0) {
                $searchRepo[] = $item->itemid;
            }
            if($item->type == 1) {
                $searchLink[] = $item->itemid;
            }
        }
        //dd($searchLink);
        $ReposResult = Repository::whereIn('id', deMul($searchRepo))->orderBy('updated_at', 'DESC')->get();
        $LinksResult = Link::whereIn('id', deMul($searchLink))->orderBy('updated_at', 'DESC')->get();
        //dd($ReposResult);
        addTagsToRepo($ReposResult);
        addTagsToLink($LinksResult);
        //dd($LinksResult);
        $getLinkById = array();
        $getRepoById = array();
        searchCreatorFromObject($ReposResult);
        //searchCreatorFromObject($LinksResult);
        foreach($ReposResult as $Repo) {
            $getRepoById[$Repo->id] = $Repo;
            //setCreatorName($repo, $user->nickname);
        }
        foreach($LinksResult as $Link) {
            $getLinkById[$Link->id] = $Link;
        }
        //dd($getRepoById);
        foreach($recentItemList as $item) {
            $length = count( isset($getLinkById[$item->repoid]) ? $getLinkById[$item->repoid] : null ) 
                      + count( isset($getRepoById[$item->repoid]) ? $getRepoById[$item->repoid] : null);
            if($length >= $recentItems){ 
                continue;
            }
            //仓库为0 link是1
            //施工中
            if($item->type == 0){
                $itemList[$item->repoid][] = ['repository' => isset($getRepoById[$item->itemid])?$getRepoById[$item->itemid] : null, 'type' => 0];
            }
            if($item->type == 1){
                $itemList[$item->repoid][] = ['link' => isset($getLinkById[$item->itemid])?$getLinkById[$item->itemid] : null, 'type'=> 1];
            }
        }
        //json_encode
        // dd(json_encode($itemList));
        foreach($repoList as $repo) {
            //$repo->tags;
            //setCreatorName($repo, $user->nickname);
            $response['repolist'][]= [ 'repostory' => $repo, 'recentItems' => isset($itemList[ $repo['id'] ]) ? $itemList[ $repo['id'] ]: [] ];
            //$count = $count+1;
        }
        //dd($response['repolist']);
        $response['repoNum'] = $count;
        // $recentItemList = Repolist::where('repoid', $userId)
        //                           ->orderBy('updated_at', 'DESC')
        //                           ->take($recentItems)->get();
        // $searchRepo = array();
        // $searchLink = array();
        // foreach($recentItemList as $reItem) {
        //     仓库为0 link是1
        //     if($reItem->type == 0) {
        //         $searchRepo[] = $reItem->itemid;
        //     }
        //     else{
        //         $searchLink[] = $reItem->itemid;
        //     }
        // }
        //dd($searchRepo);
        //$ReposResult = Repository::whereIn('id', $searchRepo)->orderBy('updated_at', 'DESC')->get();
        //$LinksResult = Link::whereIn('id', $searchLink)->orderBy('updated_at', 'DESC')->get();
        //dd($ReposResult);
        //$response['recentItems'] = array();
        //$response['recentItems'] = $itemList;
        return response()->json($response);
    }
}