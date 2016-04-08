<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User;
class UserController extends Controller
{
    public function update(Request $request) {
        $this->validate($request, [
            'token' => 'string|required',
            'nickname' => 'string|max:16',
            'sign' => 'string'
        ]);
        $token = $request->input('token');
        $user = JWTAuth::authenticate($token);
        if(!empty(  $request->input('nickname') ) ) {
            $user->nickname = $request->input('nickname');
        }
        if(!empty(  $request->input('sign') ) ) {
            $user->sign = $request->input('sign');
        }
        $user->save();
    } 
}
