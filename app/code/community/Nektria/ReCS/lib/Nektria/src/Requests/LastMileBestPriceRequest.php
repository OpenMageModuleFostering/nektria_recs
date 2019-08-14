<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;
use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;

/**
 * Get assessment of best price for last mile service
 * (before we get shopper address)
 * 
 * @author mika
 *
 */
class LastMileBestPriceRequest extends BaseRequest
{
	/**
	 * (non-PHPdoc)
	 * @see \Nektria\Recs\MerchantApiMessages\BaseRequest::execute()
	 * @return \Nektria\Recs\MerchantApi\Responses\LastMileBestPriceResponse
	 * @throws ApiResponseException
	 */	
	protected function unsafe_execute(array $params)
	{
		//$params = $this->mergeRequestSettings($params);
		
		//$response = $this->client->lastMileValidation($params);
		//$wrapped_response = new ResponseBodyWrapper($response);
		//if( ! $wrapped_response->isSuccessfull())
		//	throw new ApiResponseException($response["httpStatus"], $wrapped_response);
		
		//$response_message = $wrapped_response->getContent("\\Nektria\\Recs\\MerchantApi\\Responses\\NullResponse");
		//return $response_message;

		return new \Nektria\Recs\MerchantApi\Responses\LastMileBestPriceResponse();
	}
	
}