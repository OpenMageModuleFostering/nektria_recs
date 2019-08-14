<?php

namespace Nektria\Recs\MerchantApi\Requests;
use Nektria\Recs\MerchantApi\Responses\SandboxApiKeyResponse;

/**
 * Retrieve api key for testing.
 * @author mika
 *
 */
class SandboxApiKeyRequest extends BaseRequest
{
	/**
	 * Force empty API key, because our base client requires it :/
	 * @param array settings
	 */
	public function __construct(array $settings=array())
	{
		$settings["APIKEY"] = "";
		parent::__construct($settings);
	}

	/**
	 * (non-PHPdoc)
	 * @see BaseRequest::unsafe_execute()
	 * @return SandboxApiKeyResponse
	 */	
	protected function unsafe_execute(array $params)
	{
		$response_message = new SandboxApiKeyResponse();
		return $response_message;
	
	}
	
}