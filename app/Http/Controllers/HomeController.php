<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ExternalApiException;
use Illuminate\Http\Request;
use App\Http\Helpers\ExternalApi;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('valid');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
    	$api = new ExternalApi(Auth::user());
		$url = $request->url();
		$requestQuery = count($request->query()) ?
			$request->query() :
			null;
		$data = array();

		try {
			$data['categories'] = $api->getCategories()->getData();
			$filters = $api->getFilters('distance');
			$data['distanceFilters'] = $filters['distance'];
			$data['pagination'] = $api->getListings($url, $requestQuery);
		} catch(ExternalApiException $externalApiException) {
			$data['errors'] = $externalApiException->getData()->getErrors();
		}

        return view('home', $data);
    }
}
