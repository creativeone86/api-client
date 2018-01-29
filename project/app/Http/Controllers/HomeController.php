<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Helpers\ExternalApi;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
	protected $externalApi;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ExternalApi $externalApi)
    {
    	$this->externalApi = $externalApi;
        $this->middleware('auth');
        $this->middleware('valid');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    	$currentUser = Auth::user();
		$categories = array();
		$distanceFilters = array();
    	// prepare search data
		$this->externalApi->setMethod('GET');
		$this->externalApi->setUrl('categories', array(
			'page[number]' => 1,
			'page[size]' => 287
		));
		$this->externalApi->setAccessToken($currentUser->access_token);
		try {
			$categories = $this->externalApi->execute();

			// TODO get query params from request and make it more dynamic
			$this->externalApi->setUrl('listings/filters');
			$filters = $this->externalApi->execute();
			$this->externalApi->setUrl('listings', array(
				'filters[category]' => 'addictions',
				'filters[distance]' => '5mi',
				'filters[location]' => 'camberley',
				'page[number]' => 1,
				'page[size]' => 10,
				'sort' => '-distance'

			));
			$listings = $this->externalApi->execute(true);

			foreach($filters as $filter) {
				switch($filter['attributes']['name']) {
					case 'distance': {
						$distanceFilters = $filter['attributes']['options'];
						break;
					}
				}
			}

		} catch(\Exception $e) {
			// todo handle exception
		}


        return view('home', array(
        	'categories' => $categories,
			'distanceFilters' => $distanceFilters,
			'listings' => $listings
		));
    }
}
