<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;
use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;

/**
 * We encapsulate the last mile and the transit requests,
 * transit request is shot only when needed, 
 * and returned prices are combined results.
 * 
 * @author mika
 *
 */
class LastMileConfirmationRequest extends BaseRequest
{
	/**
	 * (non-PHPdoc)
	 * @see \Nektria\Recs\MerchantApiMessages\BaseRequest::execute()
	 * @return \Nektria\Recs\MerchantApi\Responses\LastMileConfirmationResponse
	 * @throws ApiResponseException
	 */	
	protected function unsafe_execute(array $params=array())
	{	
		$params = $this->mergeRequestSettings($params);
		
		$response = $this->client->lastMileConfirmation($params);
		$wrapped_response = new ResponseBodyWrapper($response);
		if( ! $wrapped_response->isSuccessfull())
			throw new ApiResponseException($response["httpStatus"], $wrapped_response);
		
		$response_message = $wrapped_response->getContent("\\Nektria\\Recs\\MerchantApi\\Responses\\LastMileConfirmationResponse");
		return $response_message;
	}
}