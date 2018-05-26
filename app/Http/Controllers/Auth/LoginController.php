<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use function trim;

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
     * @param string $provider Third party provider, github, twitter, facebook ,etc..
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function handleProviderCallback(string $provider)
    {
        $user = $this->findOrCreateUser(Socialite::driver($provider)->stateless()->user(), $provider);
        $spaAuthURL = trim(env('SPA_AUTH_URL', false));
        if (!$spaAuthURL) {
            throw new \Exception("SAP_AUTH_URL variable not set");
        }

        return redirect($spaAuthURL . '/' . $user->createToken('PERSONAL_ACCESS_TOKEN')->accessToken);
    }

    /**
     * If a user has registered before using social auth, return the user
     * else, create a new user object.
     * @param  $user Socialite user object
     * @param $provider Social auth provider
     * @return  User
     */
    public function findOrCreateUser(\Laravel\Socialite\Two\User $socialUser, string $provider): User
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
