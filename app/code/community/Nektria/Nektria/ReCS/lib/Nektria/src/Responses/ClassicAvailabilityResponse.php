<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\MerchantApi\Price;
use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

class ClassicAvailabilityResponse extends BaseResponse
{	
	private $available;
	private $price;
	
	/**
	 * 
	 * @param array or false $content array with response
	 * 					false if request was not shot
	 */
	public function __construct($content)
	{
		if($content === false)
		{
			$this->available = false;
			return;
		}
		$this->available = true;	

		if(is_string($content))
			$a_content = json_decode($content, true);
		else
			$a_content = $content;
		
		if(is_null($a_content))
			throw new ApiClientException("Incorrect Json Format in Confirmation Response message response");
				
		$this->checkCompulsoryFields(["price", "currency_code"], $content);		
		$this->price = new Price($content["price"], $content["currency_code"]);
	}
	
	public function isAvailable()
	{
		return $this->available ? true : false;
	}
	
	/**
	 * @todo pick up real price from webservice
	 * @return Ambigous <number, NULL>
	 */
	public function getPrice()
	{
		return $this->isAvailable() ? $this->price : null;
	}
}