<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;
use Nektria\Recs\MerchantApi\Price;
use Nektria\Recs\MerchantApi\TimeWindow;

class LastMileBestPriceResponse
{		
	//private $best_price;
	//private $best_price_currency;
	
	public function getBestPrice(){ return 12.49; }
	public function getBestPriceCurrency(){ return "EUR"; }
	public function getBestPriceCurrencySign(){ return "€"; }
	
	/**
	 * Instanciate message variables
	 * @param string $content in json format or array format
	 * @throws ApiClientException
	 */
	public function __construct($content=null)
	{
		//if(is_string($content))
		//	$a_content = json_decode($content, true);
		//else
		//	$a_content = $content;
	
		//if(is_null($a_content))
		//	throw new ApiClientException("Incorrect Json Format in InAndOut message response");
		
		//$this->price = new Price($a_content["total_price"], $a_content["currency_code"]);
		//$this->delivery_windows = array();
		//foreach($a_content["delivery_windows"] as $a_window)
		//	$this->delivery_windows[] = new TimeWindow($a_window);
	}
}