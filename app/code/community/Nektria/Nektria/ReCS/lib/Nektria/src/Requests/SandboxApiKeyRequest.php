<?php

namespace Nektria\Recs\MerchantApi\Requests;
use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;
use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;
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
	 * @throws ApiResponseException
	 */	
	protected function unsafe_execute(array $params)
	{
		$params = $this->mergeRequestSettings($params);

		$response = $this->client->sandboxApiKey($params);
		$wrapped_response = new ResponseBodyWrapper($response);
		if( ! $wrapped_response->isSuccessfull())
			throw new ApiResponseException($response["httpStatus"], $wrapped_response);

		$response_message = $wrapped_response->getContent("\\Nektria\\Recs\\MerchantApi\\Responses\\SandboxApiKeyResponse");
		return $response_message;
	}
}