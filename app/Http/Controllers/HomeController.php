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
					'location' => 'camberley',
					'practitioner' => ''
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
			$filters = $api->getFilters(array('distance', 'practitioner'));
			$data['distanceFilters'] = $filters['distance'] ?? array();
			$data['practitionerFilters'] = $filters['practitioner'] ?? array();
			$data = array_merge($data, array(
				'selectedCategory' => $requestQuery['filters']['category'],
				'selectedDistance' => $requestQuery['filters']['distance'],
				'selectedLocation' => $requestQuery['filters']['location'],
				'selectedPractitioners' => explode(',', $requestQuery['filters']['practitioner'] ?? ''),
				'selectedMemberProfessionalBody' => $requestQuery['filters']['professional_body'] ?? false,
				'selectedKeywords' => $requestQuery['filters']['keywords'] ?? ''
			));
			$data['pagination'] = $api->getListings($url, $requestQuery);
		} catch(ExternalApiException $externalApiException) {
			$data['err'] = $externalApiException->getData()->getErrors();
		}

        return view('home', $data);
    }

    public function addBookmark(Request $request) {
		$api = new ExternalApi(Auth::user());
    	$uuid = $request->input('uuid');
    	$data = array();

    	try {
			$data['response'] = $api->addBookmark($uuid)->getData();
		} catch(ExternalApiException $externalApiException) {
			$data['err'] = $externalApiException->getData()->getErrors();
		}


		return response()->json($data);
	}
}
