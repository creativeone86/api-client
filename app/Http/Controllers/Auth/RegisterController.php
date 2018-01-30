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

    protected $externalApi;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ExternalApi $externalApi)
    {
    	$this->externalApi = $externalApi;
        $this->middleware('guest');
    }

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function register(Request $request)
	{
		$user = null;
		$this->validator($request->all())->validate();

		try {
			$requestData = $request->all();
			$explodedName = explode(' ', $requestData['name'], 2);
			$firstName = $explodedName[0];
			$lastName = isset($explodedName[1]) ?
				$explodedName[1] :
				'';
			// try to register in external api
			$this->externalApi->setAuthenticated(false);
			$this->externalApi->setUrl('register');
			$this->externalApi->setMethod('POST');
			$this->externalApi->setBody(
				array(
					'data' => array(
						'type' => 'users',
						'attributes' => array(
							'first_name' => $firstName,
							'last_name' => $lastName,
							'email' => $requestData['email'],
							'password' => $requestData['password'],
							'password_confirmation' => $requestData['password_confirmation']
						)
					)
				)
			);
			// do the registration
			$this->externalApi->execute();
			// after successful registration login the user
			$this->externalApi->setAuthenticated(false);
			$this->externalApi->setMethod('POST');
			$this->externalApi->setUrl('auth');
			$this->externalApi->setBody(array(
				'username' => $requestData['email'],
				'password' => $requestData['password']
			));

			$result = $this->externalApi->execute();
			$loginResponse = $result->getData();
			// check if user exist locally
			$localUser = User::where('email', $requestData['email'])->first();

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
