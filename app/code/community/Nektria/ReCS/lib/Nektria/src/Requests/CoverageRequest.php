<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;
use Nektria\Recs\MerchantApi\Responses\CoverageResponse;
use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;

/**
 * 
 * @author mika
 *
 */
class CoverageRequest extends BaseRequest
{
	/**
	 * Force empty API key, because our base client requires it :/
	 * 		(and we need none)
	 * @param array settings
	 */
	public function __construct(array $settings=array())
	{
		$settings["APIKEY"] = "";
		parent::__construct($settings);
	}

	/**
	 * @see BaseRequest::execute()
	 * @return CoverageResponse
	 * @throws ApiResponseException
	 */	
	protected function unsafe_execute(array $params)
	{
		$params = $this->mergeRequestSettings($params);

		$response = $this->client->coverage($params);
		$wrapped_response = new ResponseBodyWrapper($response);
		if( ! $wrapped_response->isSuccessfull())
			throw new ApiResponseException($response["httpStatus"], $wrapped_response);

		$response_message = $wrapped_response->getContent(CoverageResponse::class);
		return $response_message;
	}
	
}