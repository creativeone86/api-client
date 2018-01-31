<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ExternalApiException;
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
    public function index(Request $request)
    {

    	$currentUser = Auth::user();
		$categories = array();
		$distanceFilters = array();
    	// prepare search data
		$this->externalApi->setMethod('GET');
		$this->externalApi->setUrl('categories', array(
			'page' => array(
				'number' => 1,
				'size' => 287
			)
		));
		$this->externalApi->setAccessToken($currentUser->access_token);
		try {
			$result = $this->externalApi->execute();
			$categories = $result->getData();
			$requestQuery = $request->query();
			$defaultQuery = array(
				'filters[category]' => 'addictions',
				'filters[distance]' => '5mi',
				'filters[location]' => 'camberley',
				'page[number]' => 1,
				'page[size]' => 10,
				'sort' => '-distance'

			);


			if(count($requestQuery) === 0) {
				$requestQuery = $defaultQuery;
			}

			$this->externalApi->setUrl('listings/filters');
			$result = $this->externalApi->execute();
			$filters = $result->getData();

			$this->externalApi->setUrl('listings', $requestQuery);
			$result = $this->externalApi->execute();
			$url = $request->url();
			$pagination = $result->getPagination($url, $requestQuery, 'page[number]');
			foreach($filters as $filter) {
				switch($filter['attributes']['name']) {
					case 'distance': {
						$distanceFilters = $filter['attributes']['options'];
						break;
					}
				}
			}

		} catch(ExternalApiException $externalApiException) {
			$errors = $externalApiException->getData()->getErrors();
			// todo handle exception
		}

        return view('home', array(
        	'categories' => $categories,
			'distanceFilters' => $distanceFilters,
			'pagination' => $pagination
		));
    }
}
