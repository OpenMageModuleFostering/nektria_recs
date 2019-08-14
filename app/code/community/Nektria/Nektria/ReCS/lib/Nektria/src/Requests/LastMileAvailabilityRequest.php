<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;
use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;
use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;

/**
 * We encapsulate the last mile and the transit requests,
 * transit request is shot only when needed, 
 * and returned prices are combined results.
 * 
 * @author mika
 *
 */
class LastMileAvailabilityRequest extends BaseRequest
{
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
			throw new ApiClientException("You need to specify the service type in LastMileAvailabilityRequest. Service type is returned in ServiceCreationResponse.");
	
		if($params["service_type"] == "last-mile-with-transit") return true;
		if($params["service_type"] == "last-mile-only") return true;
	
		return false;
	}
	
	/**
	 * Is this a service with transit ?
	 * @param array $params
	 * @return boolean
	 */
	private function checkTransit($params)
	{
		if($params["service_type"] == "last-mile-with-transit") return true;
		if($params["service_type"] == "last-mile-only") return false;
	}
	
	private function thereIsNoAvailability()
	{
		return new \Nektria\Recs\MerchantApi\Responses\LastMileAvailabilityResponse(false);
	}
	
	/**
	 * Set up the logic to combine the two slots whenever this is needed
	 * 
	 * @see \Nektria\Recs\MerchantApiMessages\BaseRequest::execute()
	 * @return \Nektria\Recs\MerchantApi\Responses\LastMileAvailabilityResponse
	 * @throws ApiResponseException
	 */	
	protected function unsafe_execute(array $params)
	{	
		if( ! $this->checkServiceType($params) )
			return $this->thereIsNoAvailability();		

		$params = $this->mergeRequestSettings($params);
		
		$response = $this->client->lastMileAvailability($params);
		$wrapped_response = new ResponseBodyWrapper($response);
		if( ! $wrapped_response->isSuccessfull())
			throw new ApiResponseException($response["httpStatus"], $wrapped_response);
		
		$response_message = $wrapped_response->getContent("\\Nektria\\Recs\\MerchantApi\\Responses\\LastMileAvailabilityResponse");
		
		return $response_message;
	}
}