<?php
/**
 * Created by PhpStorm.
 * User: yordangeorgiev
 * Date: 27.01.18
 * Time: 23:41
 */

namespace App\Http\Helpers;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;

class ExternalApi
{
	const API_URL = 'https://api-example.happiful.com/v1/';

	private $_headers;
	private $_url = null;
	private $_method = null;
	private $_body = null;
	private $_accessToken;
	private $_currentUser = null;

	public function __construct($user = null)
	{
		$this->setHeaders(array(
			'Content-Type' => 'application/vnd.api+json',
			'Accept' => 'application/vnd.api+json'
		));

		if(!is_null($user)) {
			$this->setAccessToken($user->access_token);
			$this->setCurrentUser($user);
		}
	}

	/**
	 * @return mixed
	 */
	public function getCurrentUser()
	{
		return $this->_currentUser;
	}

	/**
	 * @param mixed $currentUser
	 */
	public function setCurrentUser($currentUser)
	{
		$this->_currentUser = $currentUser;
	}



	public function addHeader($header) {
		$currentHeaders = $this->getHeaders();

		if(is_array($header)) {
			$currentHeaders = array_merge($currentHeaders, $header);
		}

		$this->setHeaders($currentHeaders);

	}

	/**
	 * @return mixed
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

	/**
	 * @param mixed $headers
	 */
	public function setHeaders($headers)
	{
		$this->_headers = $headers;
	}

	/**
	 * @return mixed
	 */
	public function getUrl()
	{
		return $this->_url;
	}

	/**
	 * @param mixed $url
	 */
	public function setUrl($path, $query = array())
	{
		$url = self::API_URL.$path;
		$normalizedQuery = $this->parseQuery($query);
		$url = $normalizedQuery !== false ?
			"{$url}{$normalizedQuery}" :
			$url;

		$this->_url = $url;
	}

	/**
	 * @return mixed
	 */
	public function getMethod()
	{
		return $this->_method;
	}

	/**
	 * @param mixed $method
	 */
	public function setMethod($method)
	{
		$this->_method = $method;
	}

	/**
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->_body;
	}

	/**
	 * @param mixed $body
	 */
	public function setBody($body)
	{
		$this->_body = json_encode($body);
	}

	/**
	 * @return bool
	 */

	/**
	 * @return mixed
	 */
	public function getAccessToken()
	{
		return $this->_accessToken;
	}

	/**
	 * @param mixed $accessToken
	 */
	public function setAccessToken($accessToken)
	{
		$this->_accessToken = $accessToken;
	}

	/**
	 * @param $query
	 * @return bool|string
	 */
	private function parseQuery($query) {
		$queryString = false;

		if(is_array($query) and count($query) > 0) {
			$queryString = '?'.http_build_query($query);
		}

		return $queryString;
	}

	private function validate() {
		$requiredFields = array(
			$this->getMethod(),
			$this->getUrl()
		);

		return !in_array(null, $requiredFields, true);
	}

	public function refreshToken($refreshToken) {
		$this->setMethod('POST');
		$this->setUrl('auth/refresh');
		$this->setBody(array('refresh_token' => $refreshToken));

		try {
			return $this->execute();
		} catch(ExternalApiException $apiException) {
			throw new ExternalApiException(
				$apiException->getMessage(),
				$apiException->getData()
			);
		}
	}

	public function register($firstName, $lastName, $email, $password, $confirmPwd) {
		$this->setUrl('register');
		$this->setMethod('POST');
		$this->setBody(
			array(
				'data' => array(
					'type' => 'users',
					'attributes' => array(
						'first_name' => $firstName,
						'last_name' => $lastName,
						'email' => $email,
						'password' => $password,
						'password_confirmation' => $confirmPwd
					)
				)
			)
		);
		try {
			return $this->execute();
		} catch(ExternalApiException $apiException) {
			throw new ExternalApiException(
				$apiException->getMessage(),
				$apiException->getData()
			);
		}
	}

	public function login($username, $password) {
		$this->setMethod('POST');
		$this->setUrl('auth');
		$this->setBody(array(
			'username' => $username,
			'password' => $password
		));

		try {
			return $this->execute();
		} catch(ExternalApiException $apiException) {
			throw new ExternalApiException(
				$apiException->getMessage(),
				$apiException->getData()
			);
		}
	}

	public function getMe($accessToken) {
		$this->setAuthenticated(true);
		$this->setMethod('GET');
		$this->setBody(null);
		$this->setUrl('me');
		$this->setAccessToken($accessToken);

		try {
			return $this->execute();
		} catch(ExternalApiException $apiException) {
			throw new ExternalApiException(
				$apiException->getMessage(),
				$apiException->getData()
			);
		}
	}


	public function getCategories($pageNumber = null, $size = null) {
		$this->setMethod('GET');
		$this->setUrl('categories', array(
			'page' => array(
				'number' => is_null($pageNumber) ? 1 : $pageNumber,
				'size' => is_null($size) ? 287 : $size
			)
		));

		try {
			return $this->execute();
		} catch(ExternalApiException $apiException) {
			throw new ExternalApiException(
				$apiException->getMessage(),
				$apiException->getData()
			);
		}
	}

	private function getFilterType($type, $filters) {
		$filter = null;
		$types = is_string($type) ?
			array($type) :
			$type;
		foreach($filters as $item) {
			$foundIndex = array_search($item['attributes']['name'], $types);
			if($foundIndex !== false) {
				$filter[$types[$foundIndex]] = $item['attributes']['options'];
			}
		}

		return $filter;
	}

	public function getFilters($type = null) {
		$this->setMethod('GET');
		$this->setUrl('listings/filters');
		try {
			$result = $this->execute()->getData();

			return is_null($type) ?
				$result :
				$this->getFilterType($type, $result);
		} catch(ExternalApiException $apiException) {
			throw new ExternalApiException(
				$apiException->getMessage(),
				$apiException->getData()
			);
		}
	}

	public function getListings($url = '', $query = array(), $pageField = 'page[number]' ) {
		try {
			$this->setMethod('GET');
			$this->setUrl('listings', $query);
			$result = $this->execute();
			return $result->getPagination($url, $query, $pageField);
		} catch(ExternalApiException $apiException) {
			throw new ExternalApiException(
				$apiException->getMessage(),
				$apiException->getData()
			);
		}


	}

	/**
	 * @param bool $getFullData
	 * @return GenericApiModel
	 * @throws \App\Http\Helpers\ExternalApiException
	 */
	public function execute(): GenericApiModel {
		if(!$this->validate()) {
			throw new ExternalApiException('Required data not supplied', null);
		}
		if(is_null($this->getBody())) $this->addHeader(array('Content-Length' => 0));
		// check authentication requirements
		if(!is_null($this->getAccessToken())) {
			$this->addHeader(array('Authorization' => 'Bearer ' . $this->getAccessToken()));
		}

		$client = guzzle();
		$request = new Request(
			$this->getMethod(),
			$this->getUrl(),
			$this->getHeaders(),
			$this->getBody()
		);

		try {
			$response = $client->send($request);
			$body = (string)$response->getBody();
			return new GenericApiModel(json_decode($body, true));
		} catch(ClientException $clientException) {
			$errBody = (string)$clientException->getResponse()->getBody();
			throw new ExternalApiException(
				$clientException->getMessage(),
				new GenericApiModel(json_decode($errBody, true))
			);
		}


	}



}