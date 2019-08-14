<?php

namespace Nektria\Recs\MerchantApi\Responses;
use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;
use Nektria\Recs\MerchantApi\Price;

class ClassicConfirmationResponse extends BaseResponse
{	
	private $price;
	
	public function getPrice(){ return $this->price; }
	
	/**
	 * Instanciate message variables
	 * @param string $content in json format or array format
	 * @throws ApiClientException
	 */
	public function __construct($content)
	{
		if(is_string($content))
			$a_content = json_decode($content, true);
		else
			$a_content = $content;
	
		if(is_null($a_content))
			throw new ApiClientException("Incorrect Json Format in Confirmation Response message response");
		
		$this->checkCompulsoryFields(["total_price", "currency_code"], $content);
		
		$this->price = new Price($content["total_price"], $content["currency_code"]);
	}
}