<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            $google_user = Socialite::driver('google')->stateless()->user();

            if(!User::where('email', $google_user->email)->first()) {
                $user = new User();
                $user->name = $google_user->name;
                $user->email = $google_user->email;
                $user->google_id = $google_user->id;
                $user->image_url = $google_user->avatar;
                $user->save();
            }
            if($user = User::where('email', $google_user->email)->first()) {
                $user->api_token = Hash::make(now().$user->id.$user->email);
                $user->save();
                $response['api_token'] = $user->api_token;
            } else {
                $response['response'] = "User doesn't have api token";      //Nunca se lanzará, pero se deja por si hay algun fallo humano
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
        }
        return response()->json($response);
    }
}
