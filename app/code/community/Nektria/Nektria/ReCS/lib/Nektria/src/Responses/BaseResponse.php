<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

/**
 * Temporary response message. It displays request class name and response class name.
 * 
 * @author mika
 *
 */
class BaseResponse
{		
	protected function checkCompulsoryFields($list, $content)
	{
		if(is_null($content))
			throw new ApiClientException("Incorrect Json Format in InAndOut message response");
	
		foreach($list as $element)
			if(! array_key_exists($element, $content))
				throw new ApiClientException(ucfirst($element)." field is compulsory in ".get_class($this)." message response.");
	}
}