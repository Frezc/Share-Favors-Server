<?php

namespace App\Http\Controllers;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use App\EmailVerification;
use App\User;
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
        
        //todo 
        return $token;
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
        $verification = EmailVerification::where('email',$email)->firstOrFail();
        if($code != $verification->code) {
            return "wrong code Registration reject";
        }
        
        $user = new User;
        $user->email = $request->input('email');
        $user->nickname = $request->input('nickname');
        $user->password = Hash::make($request->input('password'));
        $user->save();
        
        return $user->nickname." regist successful";
    }
}