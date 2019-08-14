<?php 

namespace Nektria\Recs\MerchantApi;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

class TimeWindow
{
	private $start_time;
	private $end_time;
	
	/**
	 * @param <string> array of ISO8601 strings $time_window
	 * 					with keys "start_time" and "end_time"
	 * @throws ApiClientException
	 */
	public function __construct($time_window)
	{
		if( ! array_key_exists("start_time", $time_window))
			throw new ApiClientException("Incorrect format for new time window price (start_time).");
		
		if( ! array_key_exists("end_time", $time_window))
			throw new ApiClientException("Incorrect format for new time window price (end_time).");				
		
		$this->start_time = new \DateTime($time_window["start_time"]);
		$this->end_time = new \DateTime($time_window["end_time"]);		
		
		if($this->start_time > $this->end_time)
			throw new ApiClientException("Start time should be previous end time.");
	}
	
	public function asArray()
	{
		return array(
				"start_time" => $this->getStartTime()->format(\DateTime::ATOM),
				"end_time" => $this->getEndTime()->format(\DateTime::ATOM)
		);
	}
	
	public function getStartTime(){ return $this->start_time; }
	public function getEndTime(){ return $this->end_time; }
	
	/**
	 * return list of days involved in this time window (usually one).
	 * @return DateTime[]
	 */
	public function getDays()
	{
		$days = array();
		if($this->start_time == $this->end_time) return $days;
		
		$current_time = clone $this->start_time;
		$current_time->setTime(0,0,0);
		while($current_time < $this->end_time)
			$days[] = $current_time;
		
		return $days;
	}
}