<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;
use Nektria\Recs\MerchantApi\Price;

class LastMileBestPriceResponse
{
	private $price;

	public function getPrice(){ return $this->price; }

	/**
	 * @deprecated use getPrice to retrieve price object instead
	 * @return float
	 */
	public function getBestPrice(){ return $this->price->getAmount(); }

	/**
	 * @deprecated use getPrice to retrieve price object instead
	 * @return string
	 */
	public function getBestPriceCurrency(){ return $this->price->getCurrencyCode(); }

	/**
	 * @deprecated use getPrice to retrieve price object instead
	 * @return string
	 */
	public function getBestPriceCurrencySign(){ return $this->price->getCurrencySign(); }
	
	/**
	 * Instantiate message variables
	 * @param string $content in json format or array format
	 * @throws ApiClientException
	 */
	public function __construct($content=null)
	{
		if(is_string($content))
			$a_content = json_decode($content, true);
		else
			$a_content = $content;
	
		if(is_null($a_content))
			throw new ApiClientException("Incorrect Json Format in LastMileBestPrice message response");
		
		$this->price = new Price($a_content["price"], $a_content["currency_code"]);
	}
}