<?php
/**
 * Created by PhpStorm.
 * User: yordangeorgiev
 * Date: 27.01.18
 * Time: 23:41
 */

namespace App\Http\Helpers;

use GuzzleHttp\Psr7\Request;

class ExternalApi
{
	const API_URL = 'https://api-example.happiful.com/v1/';

	private $_headers;
	private $_url = null;
	private $_method = null;
	private $_body = null;
	private $_authenticated = true;
	private $_accessToken;

	public function __construct()
	{
		$this->setHeaders(array(
			'Content-Type' => 'application/vnd.api+json',
			'Accept' => 'application/vnd.api+json'
		));
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
			"{$url}?{$normalizedQuery}" :
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
//		$rawData = $this->parseBody($data);
		$this->_body = json_encode($body);
	}

	/**
	 * @return bool
	 */
	public function isAuthenticated()
	{
		return $this->_authenticated;
	}

	/**
	 * @param bool $authenticated
	 */
	public function setAuthenticated($authenticated)
	{
		$this->_authenticated = $authenticated;
	}

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



	private function parseQuery($query) {
		$queryString = false;

		if(is_array($query) and count($query) > 0) {
			foreach($query as $key => $value) {
				$key = urlencode($key);
//				$value = urlencode($value);
				$queryString .= "{$key}={$value}";

				if($key !== count($query) - 1) {
					$queryString .= '&';
				}
			}
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

	/**
	 * @throws \Exception
	 */
	public function execute($getFullData = false) {
		if(!$this->validate()) throw new \Exception('Method or path not supplied.');
		if(is_null($this->getBody())) $this->addHeader(array('Content-Length' => 0));
		// check authentication requirements
		if($this->isAuthenticated()) {
			// todo get beader token
			$this->addHeader(array('Authorization' => 'Bearer '.$this->getAccessToken()));
		}

		$client = guzzle();
		$request = new Request(
			$this->getMethod(),
			$this->getUrl(),
			$this->getHeaders(),
			$this->getBody()
		);

		$response = $client->send($request);
		$statusCode = (int)$response->getStatusCode();

		if(!in_array($statusCode, array(200, 201))) {
			throw new \Exception($statusCode);
		}

		$body = (string)$response->getBody();
		$hasValidData = strpos($body, 'data') !== false;

		if(!$hasValidData) {
			throw new \Exception('No valid data from response.');
		}

		$encodedResponse = json_decode($body, true);

		return $getFullData ?
			$encodedResponse :
			$encodedResponse['data'];

	}



}