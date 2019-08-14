<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\MerchantApi\Price;
use Nektria\Recs\MerchantApi\TimeWindowPrice;
use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

class LastMileAvailabilityResponse
{	
	private $available;
	private $best_price;
	private $highest_price;
	private $time_window_prices;
	
	public function isAvailable(){ return $this->available ? true : false; }
	public function getHighestPrice(){ return $this->highest_price; }
	public function getBestPrice(){ return $this->best_price; }
	
	private function addTimeWindowPrice(TimeWindowPrice $tw)
	{
		$this->time_window_prices[] = $tw;
	}
	/**
	 *
	 * @param array or false $content array with response
	 * 					false if request was not shot
	 */
	public function __construct($content)
	{
		if($this->processUnavailable($content)) return;
		$a_content = $this->formatAndCheck($content);
		$this->checkCompulsoryFields(["best_price","highest_price", "currency_code","time_window_prices"], $a_content);
		
		$currency_code = $a_content["currency_code"];
		$this->best_price = new Price($a_content["best_price"], $currency_code);
		$this->highest_price = new Price($a_content["highest_price"], $currency_code);	
		
		$this->time_window_prices = array();
		foreach($a_content["time_window_prices"] as $twp)
			$this->addTimeWindowPrice(new TimeWindowPrice($twp, $currency_code));
	}
	
	
	private function getTimeWindowPrices(){return $this->time_window_prices;	}
	
	/**
	 * if param is set to boolean value false, we have no availability whatsoever.
	 * 
	 * @param mixed $content
	 * @return boolean
	 */
	private function processUnavailable($content)
	{
		$unavailable = ($content === false) ? true : false;
		$this->available = !$unavailable;
		return $unavailable;
	}
	
	/**
	 * 
	 * @param mixed $content
	 * @return array
	 * @throws ApiClientException if invalid json
	 */
	protected function formatAndCheck($content)
	{
		if(is_string($content))
		{
			$a_content = json_decode($content, true);
			if(json_last_error() != JSON_ERROR_NONE)
				throw new ApiClientException("Incorrect Json Format in InAndOut message response");
		}			
		else
			$a_content = $content;		
		
		return $a_content;
	}
	
	protected function checkCompulsoryFields($compulsory_list, $message)
	{
		foreach($compulsory_list as $field)
			if(! array_key_exists($field, $message))
			throw new ApiClientException("$field field is compulsory in Last Mile Availability message response");
	}	
	
	/**
	 * Get information about prices and calendar windows
	 * We return the original response plus additional information
	 * to format the widget, and which is made available in time_window_ranges
	 *
	 * @return string in json format
	 */
	public function getPriceMatrix()
	{		
		$result = array(
				"best_price" => $this->getBestPrice()->getAmount(),
				"highest_price" => $this->getHighestPrice()->getAmount(),
				"currency_code" => "EUR",
				"time_window_ranges" =>
				array(
					"min_hour" => $this->getMinHour(),
					"max_hour" => $this->getMaxHour(),
					"days" => $this->getDays()
					
				),
				"time_window_prices" => $this->getTimeWindowPricesArray()
		);

		return json_encode($result);
	}
	
	private function getTimeWindowPricesArray()
	{
		$result = array();
		foreach($this->getTimeWindowPrices() as $twp)
		{
			$element = array(
				"start_time" => $twp->getStartTime()->format(\DateTime::ATOM),
				"end_time" => $twp->getEndTime()->format(\DateTime::ATOM),
				"price" => $twp->getPrice()->getAmount()
			);
			$result[] = $element;
		}
		return $result;
	}
	
	/**
	 * @return string hour in format H:i:s with leading 0
	 */
	private function getMinHour()
	{
		$min_hour = 23;
		foreach($this->getTimeWindowPrices() as $twp)
			$min_hour = min($twp->getStartTime()->format("H"), $min_hour);
		return $min_hour.":00:00";
	}
	
	/**
	 * @return string hour in format H:i:s with leading 0
	 */
	private function getMaxHour()
	{
		$max_hour = 0;
		foreach($this->getTimeWindowPrices() as $twp)
			$max_hour = max($twp->getEndTime()->format("H"), $max_hour);
		return $max_hour.":00:00";
	}
	
	/**
	 * @return string[] array of days informat Y-m-d, ordered chronologically
	 */
	private function getDays()
	{
		$days = array();
		foreach($this->getTimeWindowPrices() as $twp)
			$days[$twp->getStartTime()->format("Y-m-d")] = null;
		
		$days = array_keys($days);
		sort($days);
		
		return $days;
	}
}