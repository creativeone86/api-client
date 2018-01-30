<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
    protected $externalApi;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
    	ExternalApi $externalApi
	)
    {
    	$this->externalApi = $externalApi;
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

		$this->externalApi->setAuthenticated(false);
		$this->externalApi->setMethod('POST');
		$this->externalApi->setUrl('auth');
		$this->externalApi->setBody(array(
			'username' => $email,
			'password' => $password
		));
		try {
			$result = $this->externalApi->execute();
			$loginResponse = $result->getData();
			// check if user exist locally
			$localUser = User::where('email', $email)->first();

			if(is_null($localUser)) {
				// get user from external api
				$this->externalApi->setAuthenticated(true);
				$this->externalApi->setMethod('GET');
				$this->externalApi->setBody(null);
				$this->externalApi->setUrl('me');
				$this->externalApi->setAccessToken($loginResponse['access_token']);
				$result = $this->externalApi->execute();
				$apiResponse = $result->getData();
				$time = Carbon::parse(Carbon::now());
				$time->addSeconds($loginResponse['expires_in']);
				$firstName = $apiResponse['attributes']['first_name'];
				$lastName = $apiResponse['attributes']['last_name'];

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
		} catch(\Exception $e) {
			redirect($this->redirectPath());
		}

	}
}
