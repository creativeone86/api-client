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
		$requestQuery = array_merge(
			array(
				'filters' => array(
					'category' => 'addictions',
					'distance' => '5mi',
					'location' => 'camberley'
				),
				'page' => array(
					'number' => 1,
					'size' => 10
				),
				'sort' => '-distance'

			),
			$request->query()
		);
		$data = array();

		try {
			$data['categories'] = $api->getCategories()->getData();
			$filters = $api->getFilters('distance');
			$data['distanceFilters'] = $filters['distance'];
			$data = array_merge($data, array(
				'selectedCategory' => $requestQuery['filters']['category'],
				'selectedDistance' => $requestQuery['filters']['distance'],
				'selectedLocation' => $requestQuery['filters']['location']
			));
			$data['pagination'] = $api->getListings($url, $requestQuery);
		} catch(ExternalApiException $externalApiException) {
			$data['err'] = $externalApiException->getData()->getErrors();
		}

        return view('home', $data);
    }
}
