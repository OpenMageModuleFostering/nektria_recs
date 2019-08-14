<?php

namespace Nektria\Recs\MerchantApi\Requests;

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
		$response_message = new \Nektria\Recs\MerchantApi\Responses\CoverageResponse();
		return $response_message;
	}
	
}