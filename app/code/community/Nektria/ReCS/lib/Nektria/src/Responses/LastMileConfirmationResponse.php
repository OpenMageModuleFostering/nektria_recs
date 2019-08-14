<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;
use Nektria\Recs\MerchantApi\Price;
use Nektria\Recs\MerchantApi\TimeWindow;

class lastMileConfirmationResponse
{		
	private $price;
	private $delivery_windows;
	
	public function getFormattedPrice(){ return $this->price->getAmount()."€"; }
	public function getDeliveryWindows(){ return $this->delivery_windows; }
	
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
			throw new ApiClientException("Incorrect Json Format in InAndOut message response");
		
		$this->price = new Price($a_content["total_price"], $a_content["currency_code"]);
		$this->delivery_windows = array();
		foreach($a_content["delivery_windows"] as $a_window)
			$this->delivery_windows[] = new TimeWindow($a_window);
	}
}