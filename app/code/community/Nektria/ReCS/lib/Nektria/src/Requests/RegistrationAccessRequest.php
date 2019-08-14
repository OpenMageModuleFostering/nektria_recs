<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;
use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;

/**
 * Retrieve url to access merchant configuration parameters.
 * @author mika
 *
 */
class RegistrationAccessRequest extends BaseRequest
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
	 * @see \Nektria\Recs\MerchantApiMessages\BaseRequest::execute()
	 * @return \Nektria\Recs\MerchantApi\Responses\BackendAccessResponse
	 * @throws ApiResponseException
	 */	
	protected function unsafe_execute(array $params)
	{
		$params = $this->mergeRequestSettings($params);
		
		$response = $this->client->registrationAccess($params);
		$wrapped_response = new ResponseBodyWrapper($response);
		if( ! $wrapped_response->isSuccessfull())
			throw new ApiResponseException($response["httpStatus"], $wrapped_response);
		
		$response_message = $wrapped_response->getContent("\\Nektria\\Recs\\MerchantApi\\Responses\\RegistrationAccessResponse");
		return $response_message;
	
	}
	
}