<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;
use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;

/**
 * Retrieve url to access merchant configuration parameters.
 * @author mika
 *
 */
class BackendAccessRequest extends BaseRequest
{
	/**
	 * (non-PHPdoc)
	 * @see \Nektria\Recs\MerchantApiMessages\BaseRequest::execute()
	 * @return \Nektria\Recs\MerchantApi\Responses\BackendAccessResponse
	 * @throws ApiResponseException
	 */	
	protected function unsafe_execute(array $params)
	{	
		$params = $this->mergeRequestSettings($params);
		
		$response = $this->client->backendAccess($params);
		$wrapped_response = new ResponseBodyWrapper($response);
		if( ! $wrapped_response->isSuccessfull())
			throw new ApiResponseException($response["httpStatus"], $wrapped_response);
		
		$response_message = $wrapped_response->getContent("\\Nektria\\Recs\\MerchantApi\\Responses\\BackendAccessResponse");
		return $response_message;
	
	}
	
}