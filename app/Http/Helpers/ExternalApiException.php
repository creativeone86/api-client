<?php
/**
 * Created by PhpStorm.
 * User: yordangeorgiev
 * Date: 30.01.18
 * Time: 0:15
 */

namespace App\Http\Helpers;


class ExternalApiException extends \Exception
{
	private $_data = null;

	public function __construct(String $message, GenericApiModel $data)
	{
		$this->_data = $data;
		parent::__construct($message);
	}

	public function getData():GenericApiModel
	{
		return $this->_data;
	}

}