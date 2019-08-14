<?php

namespace Nektria\Recs\MerchantApi\Requests;

use Nektria\Recs\MerchantApi\Responses\ResponseBodyWrapper;
use Nektria\Recs\MerchantApi\Exceptions\ApiResponseException;
use Nektria\Recs\MerchantApi\Responses\NullResponse;

class TestRequest extends BaseRequest
{	
	protected function unsafe_execute(array $params=null)
	{
		$response = $this->client->test();
		$wrapped_response = new ResponseBodyWrapper($response);

		if (! $wrapped_response->isSuccessfull())
			throw new ApiResponseException($response["httpStatus"], $wrapped_response);

		return new NullResponse();
	}
}