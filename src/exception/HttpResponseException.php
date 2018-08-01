<?php

namespace fashop\exception;

use fashop\Response;

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
