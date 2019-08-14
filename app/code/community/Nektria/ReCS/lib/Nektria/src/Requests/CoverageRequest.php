<?php

namespace Nektria\Recs\MerchantApi\Requests;
use Nektria\Recs\MerchantApi\Responses\CoverageResponse;

/**
 * 
 * @author mika
 *
 */
class CoverageRequest extends BaseRequest
{
	/**
	 * (non-PHPdoc)
	 * @see \Nektria\Recs\MerchantApiMessages\BaseRequest::execute()
	 * @return \Nektria\Recs\MerchantApi\Responses\CoverageResponse
	 */	
	protected function unsafe_execute(array $params)
	{
		$response_message = new CoverageResponse();
		return $response_message;
	}
	
}