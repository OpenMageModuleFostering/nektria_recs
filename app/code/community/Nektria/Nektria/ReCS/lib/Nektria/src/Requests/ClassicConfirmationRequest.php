<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;
use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;

class ClassicConfirmationRequest extends BaseRequest
{
	/**
	 * (non-PHPdoc)
	 * @see \Nektria\Recs\MerchantApiMessages\BaseRequest::execute()
	 * @return \Nektria\Recs\MerchantApi\Responses\ClassicConfirmationResponse
	 * @throws ApiResponseException
	 */	
	protected function unsafe_execute(array $params)
	{	
		$params = $this->mergeRequestSettings($params);			
		
		$response = $this->client->classicConfirmation($params);
		$wrapped_response = new ResponseBodyWrapper($response);		
		if( ! $wrapped_response->isSuccessfull())
			throw new ApiResponseException($response["httpStatus"], $wrapped_response);
		
		$response_message = $wrapped_response->getContent("\\Nektria\\Recs\\MerchantApi\\Responses\\ClassicConfirmationResponse");
		return $response_message;								
	}
}