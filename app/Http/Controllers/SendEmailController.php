<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Http\Requests;
use App\EmailVerification;

class SendEmailController extends Controller
{
    //发送邮件给
    public function sendMail(Request $request){
        $this->validate($request, [
            'email' => 'email|required|unique:users,email'
        ]);
        $email = $request->input('email');
        $code=str_random(6);
        EmailVerification::where('email', $email)->delete();
        EmailVerification::create(['email' => $email, 'code' => $code]);
        
        Mail::send('emails.reminder', ['email' => $email, 'code' => $code], function ($m) use ($email) {
            $m->to($email)->subject('Your  verification code is ');
         });
        
        return 'sendsuccess';
    }
}
