<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class UsersController extends Controller
{
    public function redirect() {
        try {
            $scopes = [
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
            ];

            $response['url'] = Socialite::driver('google')->scopes($scopes)->redirect()->getTargetUrl();
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
        }
        return response()->json($response);
    }

    public function callback() {
        try {
            $user = Socialite::driver('google')->stateless()->user();

            if(!User::where('email', $user->email)->first()) {
                $student = new User();
                $student->name = $user->name;
                $student->email = $user->email;
                $student->google_id = $user->id;
                $student->image_url = $user->avatar;
                $student->save();

                $response['msg'] = 'User created properly';
            } else {
                $response['msg'] = 'User already exists';
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
        }
        return response()->json($response);
    }
}
