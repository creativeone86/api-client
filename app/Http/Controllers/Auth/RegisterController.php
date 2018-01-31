<?php

namespace App\Http\Controllers\Auth;

use App\Http\Helpers\ExternalApiException;
use App\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use App\Http\Helpers\ExternalApi;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
    }

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function register(Request $request)
	{
		$user = null;
		$requestData = $request->all();
		$this->validator($requestData)->validate();

		try {
			$api = new ExternalApi();
			$explodedName = explode(' ', $requestData['name'], 2);
			$firstName = $explodedName[0];
			$lastName = isset($explodedName[1]) ?
				$explodedName[1] :
				'';
			$api->register(
				$firstName,
				$lastName,
				$requestData['email'],
				$requestData['password'],
				$requestData['password_confirmation']
			);

			// after successful registration login the user
			$loginResponse = $api->login($requestData['email'], $requestData['password'])->getData();
			// check if user exist locally
			$localUser = User::where('email', $requestData['email'])->first();

			if(is_null($localUser)) {
				// get user from external api
				$me = $api->getMe($loginResponse['access_token'])->getData();
				$time = Carbon::parse(Carbon::now());
				$time->addSeconds($loginResponse['expires_in']);
				$firstName = $me['attributes']['first_name'];
				$lastName = $me['attributes']['last_name'];

				$userData = array(
					'name' => "{$firstName} {$lastName}",
					'email' => $requestData['email'],
					'password' => bcrypt($requestData['password']),
					'access_token' => $loginResponse['access_token'],
					'refresh_token' => $loginResponse['refresh_token'],
					'expires_at' => $time->toDateString()
				);

				$user = User::create($userData);

			} else {
				$localUser->password = bcrypt($requestData['password']);
				$localUser->access_token = $loginResponse['access_token'];
				$localUser->refresh_token = $loginResponse['refresh_token'];
				$time = Carbon::parse(Carbon::now());
				$time->addSeconds($loginResponse['expires_in']);
				$localUser->expires_at = $time->toDateString();
				$user = $localUser->save();
			}

			event(new Registered($user));

			$this->guard()->login($user);

			return $this->registered($request, $user)
				?: redirect($this->redirectPath());

		} catch(ExternalApiException $externalApiException) {
			return redirect('register')->with('err', $externalApiException->getData()->getErrors());
		}

	}

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
    	$createData = array(
			'name' => $data['name'],
			'email' => $data['email'],
			'password' => bcrypt($data['password'])
		);
        return User::create($createData);
    }
}
