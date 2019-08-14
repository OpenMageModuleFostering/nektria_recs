<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\Exceptions;

class BackendAccessResponse
{
	private $uri;

	/**
	 * Instantiate message variables
	 * @param string $content in json format or array format
	 * @throws ApiClientException
	 */
	public function __construct($content)
	{
		if(is_string($content))
			$a_content = json_decode($content, true);
		else
			$a_content = $content;
	
		if(is_null($a_content))
			throw new ApiClientException("Incorrect Json Format in BackendAccess message response");
	
		if(! array_key_exists("uri", $a_content))
			throw new ApiClientException("Uri field is compulsory in BackendAccess message response");
	
		$this->uri = $a_content["uri"];
	}
	
	public function getBackendUrl(){ return $this->uri; }
}