<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;
use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;

class ServiceCreationRequest extends BaseRequest
{
	/**
	 * (non-PHPdoc)
	 * @see \Nektria\Recs\MerchantApiMessages\BaseRequest::execute()
	 * @param array $params: array with following keys
	 * 		- shopper: shopper details as described in the docs
	 * 		- destination_address: shopper address as described in the docs
	 * 		- products: shopping basket products as described in the docs (optional)
	 *		- currency_code: 3 letters code for the currency that should be used for all following requests (optional)
	 * 		- session_timeout: duration of the session in the store, in seconds
	 *
	 * @return \Nektria\Recs\MerchantApi\Responses\ServiceCreationResponse
	 * @throws ApiResponseException
	 */	
	protected function unsafe_execute(array $params=array())
	{			
		$params = $this->buildParams($params);

		$response = $this->client->serviceCreation($params);
		$wrapped_response = new ResponseBodyWrapper($response);
		if( ! $wrapped_response->isSuccessfull())
			throw new ApiResponseException($response["httpStatus"], $wrapped_response);
		
		$response_message = $wrapped_response->getContent("\\Nektria\\Recs\\MerchantApi\\Responses\\ServiceCreationResponse");
		return $response_message;								
	}

	/**
	*	Set up request for last mile and classic services
	*	Cast session_timeout to int
	*/
	private function buildParams($params)
	{
		array_merge($params, array("services" => ["classic", "last-mile"]));			

		if(array_key_exists("session_timeout", $params))
			$params["session_timeout"] = (int)$params["session_timeout"];

		return $params;
	}
}