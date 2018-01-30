<?php
/**
 * Created by PhpStorm.
 * User: yordangeorgiev
 * Date: 28.01.18
 * Time: 4:51
 */

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Helpers\ExternalApi;

class HasValidSession
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @param  string|null $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = null)
	{

		$hasValidSession = Auth::guard($guard)->check();
		if($hasValidSession) {
			$user = $request->user();
			$expiresAt = $user->expires_at;
			$expirationLimit = Carbon::now()->addMinutes(10)->timestamp;
			$expiresAt = Carbon::parse($expiresAt)->timestamp;

			if($expirationLimit > $expiresAt) {
				// try to reset token
				$externalApi = new ExternalApi();
				$externalApi->setMethod('POST');
				$externalApi->setUrl('auth/refresh');
				$externalApi->setAuthenticated(false);
				$externalApi->setBody(array(
					'refresh_token' => $user->refresh_token
				));

				try {
					$response = $externalApi->execute();
					$refreshTokenResponse = $response->getData();
					$time = Carbon::parse(Carbon::now());
					$time->addSeconds($refreshTokenResponse['expires_in']);

					$user->access_token = $refreshTokenResponse['access_token'];
					$user->refresh_token = $refreshTokenResponse['refresh_token'];
					$user->expires_at = $time->toDateString();
					$user->save();

				} catch(\Exception $e) {
					Auth::logout();
					return redirect('/');
				}

			}

			return $next($request);

		}

		Auth::logout();
		return redirect('/');

	}
}