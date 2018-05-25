<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(string $provider)
    {
        $authUser = $this->findOrCreateUser(Socialite::driver($provider)->stateless()->user(), $provider);
//        dd($authUser->createToken($user->email)->accessToken);
        // $user->token;
    }

    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     * @param  $user Socialite user object
     * @param $provider Social auth provider
     * @return  User
     */
    public function findOrCreateUser(\Laravel\Socialite\Two\User $socialUser, string $provider)
    {
        $dataArr = [
            'name' => $socialUser->nickname,
            'email' => $socialUser->email,
            'provider' => $provider,
            'provider_id' => $socialUser->id,
            'avatar' => $socialUser->avatar,
            'details' => $socialUser->user
        ];
        $currentUser = User::where(['email' => $socialUser->getEmail()])->first();
        if ($currentUser) {
            $currentUser->update($dataArr);
            return $currentUser;
        } else {
            return User::create($dataArr);
        }
    }
}
