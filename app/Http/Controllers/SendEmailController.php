<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use App\Http\Requests;

class SendEmailController extends Controller
{
    //发送邮件给
    public function sendMail(Request $request){
        $this->validate($request, [
            'email' => 'email|required'
        ]);
        $email = $request->input('email');
        Mail::send('emails.reminder', ['email' => $email], function ($m) use ($email) {
            $m->to($email)->subject('Your Reminder!');
         });
        return 'sendsuccess';
    }
}
