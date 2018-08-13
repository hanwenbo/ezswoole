<?php

namespace ezswoole\exception;

use ezswoole\Response;

class HttpResponseException extends \RuntimeException {
	/**
	 * @var Response
	 */
	protected $response;

	public function __construct(Response $response) {
		$this->response = $response;
	}

	public function getResponse() {
		return $this->response;
	}

}
