<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;
use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;
use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;

class ClassicAvailabilityRequest extends BaseRequest
{
	/**
	 * (non-PHPdoc)
	 * @see \Nektria\Recs\MerchantApiMessages\BaseRequest::execute()
	 * @return \Nektria\Recs\MerchantApi\Responses\ClassicAvailabilityResponse
	 * @throws ApiClientException
	 */	
	protected function unsafe_execute(array $params)
	{			
		if( ! $this->checkServiceType($params) )
			return $this->thereIsNoAvailability();		
		
		$rq_params = $this->getRequestSettings();		
		$response = $this->client->classicAvailability($rq_params);
		$wrapped_response = new ResponseBodyWrapper($response);		
		if( ! $wrapped_response->isSuccessfull())
			throw new ApiResponseException($response["httpStatus"], $wrapped_response);
		
		$response_message = $wrapped_response->getContent("\\Nektria\\Recs\\MerchantApi\\Responses\\ClassicAvailabilityResponse");
		return $response_message;								
	}
	
	/**
	 * Service type has to be here, and to be classic.
	 * 
	 * @param array $params
	 * @throws ApiClientException if missing service_type parameter
	 * @return boolean true if we have availability for classic delivery
	 */
	private function checkServiceType(array $params)
	{
		if(! array_key_exists("service_type", $params))
			throw new ApiClientException("You need to specify the service type in Classic Availability Request. Service type is returned in ServiceCreationResponse.");
			
		if($params["service_type"] != "classic") return false;
		
		return true;
	}
	
	private function thereIsNoAvailability()
	{
		return new \Nektria\Recs\MerchantApi\Responses\ClassicAvailabilityResponse(false);
	}
}