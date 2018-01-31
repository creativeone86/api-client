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

	private function getDataFromArray($key, $array) {
		return $array[$key] ?? null;
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
	 * @param $url
	 * @param $query
	 * @return array
	 */
	private function paginate($url, $query, $pageField) {
		$pagination = $this->_pagination;
		$totalPages = $this->getDataFromArray('total_pages', $pagination);
		$currentPage = $this->getDataFromArray('current_page', $pagination);
		$paginateLimit = 2;
		$query[$pageField] = $pagination['current_page'] - 1;
		$previousPageQuery = http_build_query($query);
		$query[$pageField] = $pagination['current_page'] + 1;
		$nextPageQuery = http_build_query($query);
		$pageArray = array(
			'onFirstPage' => $pagination['current_page'] === 1,
			'currentPage' => $pagination['current_page'],
			'previousPageUrl' => $pagination['current_page'] === 1 ?
				false :
				"{$url}?{$previousPageQuery}",
			'hasMorePages' => $pagination['current_page'] < $pagination['total_pages'],
			'nextPageUrl' => $pagination['current_page'] < $pagination['total_pages'] ?
				"{$url}?{$nextPageQuery}" :
				false,
			'pages' => array(),
			'data' => $this->getData()
		);
		$dotsShow = true;
		for($i = 1; $i <= $totalPages; $i++) {
			if($i == 1 || $i == $totalPages ||
				($i >= $currentPage - $paginateLimit &&
					$i <= $currentPage + $paginateLimit)) {
				$dotsShow = true;
				if($i != $currentPage) {
					$query[$pageField] = $i;
					$currentQuery = http_build_query($query);
					$pageArray['pages'][$i]['url'] = $url . "?{$currentQuery}";
					$pageArray['pages'][$i]['page'] = $i;
				} else {
					$pageArray['pages'][$i]['page'] = $i;
				}

			}

			else if($dotsShow == true) {
				$dotsShow = false;
				$pageArray['pages'][$i]['text'] = "...";
			}
		}

		return $pageArray;
	}

	/**
	 * @param $url
	 * @param $query
	 * @return array|bool
	 */
	public function getPagination($url, $query, $pageField)
	{
		if(is_null($this->getData()) or is_null($this->_pagination)) return false;

		$data = $this->paginate($url, $query, $pageField);
		return $data;
	}








}