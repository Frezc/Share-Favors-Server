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
        
        $stars = json_decode($user->starlist);
        foreach( $stars as $repoId){
            $repodetail = Repository::findorFail($repoId);
            $repodetail->tags;
            $repodetail->creatorId = $repodetail->creator;
            unset($repodetail->creator);
            $creatordetail = User::findorFail($repodetail->creatorId);
            $repodetail->creatorName = $creatordetail->nickname;
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
            'nickname'  => 'required|max:32',
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
}