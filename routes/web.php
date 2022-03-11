<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', function () {
    return redirect()->route('loginRedirect');
});

Route::get('/auth/redirect', function () {
    $scopes = [
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
    ];

    $response['url'] = Socialite::driver('google')->scopes($scopes)->redirect()->getTargetUrl();
    return response()->json($response);
})->name('loginRedirect');

Route::get('/auth/callback', function (Request $request) {
    try {
        $user = Socialite::driver('google')->stateless()->user();

        $student = new User();
        $student->name = $user->name;
        $student->email = $user->email;
        $student->google_id = $user->id;
        $student->image_url = $user->avatar;

        $student->save();

        $response['msg'] = 'User created properly';
    } catch (\Throwable $th) {
        $response['response'] = "An error has occurred: ".$th->getMessage();
    }

    return response()->json($response);
});
