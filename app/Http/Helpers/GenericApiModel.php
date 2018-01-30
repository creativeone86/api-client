<?php
/**
 * Created by PhpStorm.
 * User: yordangeorgiev
 * Date: 30.01.18
 * Time: 0:24
 */

namespace App\Http\Helpers;


class GenericApiModel
{
	private $_responseBody;
	private $_data = null;
	private $_errors = null;
	private $_pagination = null;

	/**
	 * GenericApiModel constructor.
	 * @param array $responseBody
	 */
	public function __construct(Array $responseBody)
	{
		$this->_responseBody = $responseBody;
		$this->_data = $this->getFieldFromBody('data');
		$this->_errors = $this->getFieldFromBody('errors');

		if(isset($this->_responseBody['meta']) and isset($this->_responseBody['meta']['pagination'])) {
			$this->_pagination = $this->_responseBody['meta']['pagination'];
		}
	}

	/**
	 * @param $key
	 * @return mixed|null
	 */
	private function getFieldFromBody($key) {
		return isset($this->_responseBody[$key]) ?
			$this->_responseBody[$key] :
			null;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function has($key) {
		return isset($this->_responseBody[$key]);
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * @return mixed|null
	 */
	public function getErrors()
	{
		return isset($this->_errors[0]) ? $this->_errors[0] : null;
	}

	/**
	 * @return null
	 */
	public function getPagination()
	{
		return $this->_pagination;
	}








}