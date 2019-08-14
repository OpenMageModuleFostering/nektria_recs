<?php 

namespace Nektria\Recs\MerchantApi;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;


/**
 * Add price concept to a time window.
 * 
 * @author mika
 *
 */
class TimeWindowPrice extends TimeWindow
{	
	private $price;
	
	/**
	 * @param <string> array of ISO8601 strings $time_window
	 * 					with keys "start_time", "end_time" and "price"
	 * @param string $default_currency_code
	 * @throws RecsBookingException
	 */
	public function __construct($time_window_price, $default_currency_code=null)
	{
		parent::__construct($time_window_price);
		
		if( ! array_key_exists("price", $time_window_price))
			throw new ApiClientException("Incorrect format for new time window price (price).");
		
		if( ! array_key_exists("currency_code", $time_window_price))
			if(is_null($default_currency_code))
				throw new ApiClientException("Incorrect format for new time window price (currency_code).");
				
		$currency_code = array_key_exists("currency_code", $time_window_price) ?	
										$time_window_price["currency_code"] :
										$default_currency_code;
		$this->price = 	new Price($time_window_price["price"], $currency_code);		
	}
		
	public function getPrice(){ return $this->price; }
}