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
        try {
            $user = User::where('email', $credentials['email'])->firstOrFail();
        } catch (\Exceptions $e) {
            return response()->json(['error' => 'no this user or wrong email'], 400);
        }
        addTagsToRepo($user->starlist);
        addTagsToRepo($user->repositories);
        $package = [
                    "token" => $token, 
                    "expired_at" => time()+86400, 
                    "user" => [
                                'starlist' => getRecentItems($user->starlist, 1, 10),
                                'repositories' => getRecentItems($user->repositories, 1, 10)
                              ]
                    ];
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
        
        $starlist = $user->starlist()->where('status', 1)->orderBy('updated_at', 'DESC')->take($starListMax)->get();
        $repositories = $user->repositories()->where('status', 1)->orderBy('updated_at', 'DESC')->take($repositoriesMax)->get();
        addTagsToRepo($user->starlist);
        addTagsToRepo($user->repositories);
        $userinfo = [
                    'sign'     => $user->sign, 
                    'email'    => $user->email, 
                    'nickname' => $user->nickname,
                    'id' => $user->id,
                    'starlist' => getRecentItems($user->starlist, 1, 10),
                    'repositories' => getRecentItems($user->repositories, 1, 10)
                    ];
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
                $repoList = Repository::where('creator_id', $userId)
                                    ->orderBy('updated_at', 'DESC')
                                    ->skip($offset)
                                    ->take($limit)
                                    ->get();
                $response = ['showAll' => 1];
             }
             else{
                $repoList = Repository::where('creator_id', $userId)->where('status', 1)
                                  ->orderBy('updated_at', 'DESC')
                                  ->skip($offset)
                                  ->take($limit)
                                  ->get();
                $response = ['showAll' => 0];
                try{
                    $user = User::findOrFail($userId);
                } catch(\Exception $e) {
                    return response()->json(['error' => 'user not find', 404]);
                }
             }
        }
        else{
             $repoList = Repository::where('creator_id', $userId)->where('status', 1)
                                  ->orderBy('updated_at', 'DESC')
                                  ->skip($offset)
                                  ->take($limit)
                                  ->get();
             $response = ['showAll' => 0];
             try{
                    $user = User::findOrFail($userId);
             } catch(\Exception $e) {
                return response()->json(['error' => 'user not find', 404]);
             }
        }
        $count = count($repoList->toArray());
        $response['repoList'] = array();
        $response['repoNumAll'] = Repository::where('creator_id', $userId)->count();
        addTagsToRepo( $repoList );
        $response['repoList'] = getRecentItems($repoList, $response['showAll'], $recentItems);
        $response['repoNum'] = $count;
        return response()->json($response);
    }
    
    public function showUserStarlist(Request $request, $userId) {
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
                $response = ['showAll' => 1];
             }
             else{
                $response = ['showAll' => 0];
                try{
                    $user = User::findOrFail($userId);
                } catch(\Exception $e) {
                    return response()->json(['error' => 'user not find', 400]);
                }
             }
        }
        else{
            $response = ['showAll' => 0];
            try{
               $user = User::findOrFail($userId);
            } catch(\Exception $e) {
               return response()->json(['error' => 'user not find', 400]);
            }
        }
        if($response['showAll'] == 0) {
            $starlist = $user->starlist()
                             ->where('status', 1)
                             ->orderBy('updated_at', 'DESC')
                             ->skip($offset)
                             ->take($limit)
                             ->get();
        }
        if($response['showAll'] == 1) {
            $starlist = $user->starlist()
                             ->orderBy('updated_at', 'DESC')
                             ->skip($offset)
                             ->take($limit)
                             ->get();
        }
        addTagsToRepo($starlist);
        $response['repoNumAll'] = $user->starlist()->count();
        $response['repoNum'] = count($starlist);
        $response['repoList'] = getRecentItems($starlist, $response['showAll'], $recentItems);
        return response()->json($response);
    }
}