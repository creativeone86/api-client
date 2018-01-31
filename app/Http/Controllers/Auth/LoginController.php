<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ExternalApiException;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Helpers\ExternalApi;
use App\User;

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
    protected $redirectTo = '/';

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
	 * Attempt to log the user into the application.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return bool
	 */
	protected function attemptLogin(Request $request)
	{
		$credentials = $this->credentials($request);

		// try to authenticate external api first
		$email = $credentials['email'];
		$password = $credentials['password'];


		try {
			$api = new ExternalApi();
			$loginResponse = $api->login($email, $password)->getData();
			$localUser = User::where('email', $email)->first();

			if(is_null($localUser)) {
				// get user from external api
				$me = $api->getMe($loginResponse['access_token'])->getData();
				$time = Carbon::parse(Carbon::now());
				$time->addSeconds($loginResponse['expires_in']);
				$firstName = $me['attributes']['first_name'];
				$lastName = $me['attributes']['last_name'];

				$userData = array(
					'name' => "{$firstName} {$lastName}",
					'email' => $email,
					'password' => bcrypt($password),
					'access_token' => $loginResponse['access_token'],
					'refresh_token' => $loginResponse['refresh_token'],
					'expires_at' => $time->toDateString()
				);

				User::create($userData);

			} else {
				$localUser->password = bcrypt($password);
				$localUser->access_token = $loginResponse['access_token'];
				$localUser->refresh_token = $loginResponse['refresh_token'];
				$time = Carbon::parse(Carbon::now());
				$time->addSeconds($loginResponse['expires_in']);
				$localUser->expires_at = $time->toDateString();
				$localUser->save();
			}

			return $this->guard()->attempt(
				$credentials, $request->filled('remember')
			);
		} catch(ExternalApiException $externalApiException) {
			$data = $externalApiException->getData()->getErrors();
			redirect('login')->with('err', $data);
		}

	}
}
