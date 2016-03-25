<?php

namespace App\Http\Controllers;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use App\EmailVerification;
use App\User;
use App\Repository;
use App\Tag;
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
        
        $user = User::where('email', $credentials['email'])->firstOrFail();
        
        $starlist = ["nickname" => $user->nickname, "email" => $user->email];
        $starlist = array();
        
        $user->stars;
        
        foreach( $user->stars as $repodetail){
            setCreator($repodetail);
            $repodetail->tags;            
            $starlist[] = $repodetail->toArray();
        }
        
        $userdetail = ["sign" => $user->sign, "starlist" =>$starlist, "nickname" => $user->nickname, "email" => $user->email];
        $package = ["token" => $token, "expired_at" => time()+86400, "user" => $userdetail];
        
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
        
        $verification = EmailVerification::where('email',$email)->firstOrFail();
        
        if($code != $verification->code) {
            return "wrong code Registration reject";
        }
        
        $user = new User;
        $user->email = $email;
        $user->nickname = $nickname;
        $user->password = Hash::make($password);
        $user->save();
        
        EmailVerification::where('email',$email)->delete();
        
        return $user->nickname." regist successful";
    }
    
    public function getUserinfo(Request $request, $id) {
        $this->validate($request, [
            'starListMax'     => 'integer',
            'repositoriesMax' => 'integer'
        ]);
        
        $starListMax = $request->input('starListMax', 3);
        $repositoriesMax = $request->input('repositoriesMax', 3);
        
        $user = User::findOrFail($id);
        $userinfo = [
            'sign'     => $user->sign, 
            'email'    => $user->email, 
            'nickname' => $user->nickname
            ];
        $userinfo['repositories'] = array();
        $userinfo['starlist'] = array();
        $starlist = $user->stars()->where('status', 1)->orderBy('updated_at', 'DESC')->take($starListMax)->get();
        foreach($starlist as $star) {
            setCreator($star);
            $star->tags;
            $userinfo['starlist'][] = $star->toArray();
        }
        $repolist = Repository::where('creator', $id)
                                                  ->where('status', 1)
                                                  ->orderBy('updated_at', 'DESC')
                                                  ->take($repositoriesMax)
                                                  ->get();
        foreach($repolist as $repo) {
            setCreator($repo);
            $repo->tags;
            $userinfo['repositories'][] = $repo->toArray();
        }
        
        return response()->json($userinfo);
    }
}