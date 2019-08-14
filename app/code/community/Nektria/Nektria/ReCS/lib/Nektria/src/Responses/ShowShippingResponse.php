<?php

namespace Nektria\Recs\MerchantApi\Responses;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;
use Nektria\Recs\MerchantApi\Price;
use Nektria\Recs\MerchantApi\Address;
use Nektria\Recs\MerchantApi\Product;
use Nektria\Recs\MerchantApi\TimeWindow;

class ShowShippingResponse
{	
	private $merchant_name;
	private $service_type;
	
	private $shipping_cost;
	private $order_number;
	private $order_date;
		
	private $status;
	
	private $delivery_windows;
	private $shopper_address;
	private $products;		
	
	public function getMerchantName(){ return $this->merchant_name; }
	public function getServiceType(){ return $this->service_type; }
	public function isLastMile(){ return $this->service_type == "last-mile"; }
	
	public function getShippingCost(){ return $this->shipping_cost; }
	public function getOrderNumber(){ return $this->order_number; }
	public function getOrderDate(){ return $this->order_date; }
	
	public function getStatus(){ return $this->status; }
	
	public function getDeliveryWindows(){ return $this->delivery_windows; }
	public function getShopperAddress(){ return $this->shopper_address; }
	
	private function hasProducts(){ return ! is_null($this->products); }
	public function getProducts(){ return $this->products; }	
	
	private function isLastMileDelivery(){ return $this->service_type == "last-mile"; } 
	private function hasDeliveryWindows(){ return ! is_null($this->delivery_windows);}
	private function addDeliveryWindow($window)
	{ 
		if(! $this->hasDeliveryWindows()) $this->delivery_windows = array();
		$this->delivery_windows[] = is_array($window) ? new TimeWindow($window) : $window;
	}
	
	private function addProduct($product)
	{
		if(! $this->hasProducts()) $this->products = array();
		$this->products[] = is_array($product) ? new Product($product) : $product;
	}
	
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
		
		$this->merchant_name = $a_content["merchant"];
		$this->service_type = $a_content["delivery_type"];
		$this->order_number = $a_content["number"];
		$this->order_date = new \DateTime($a_content["date"]);
		
		$this->status = $a_content["status"];
		
		if(array_key_exists("shipping_cost", $a_content))
			$this->shipping_cost = new Price($a_content["shipping_cost"], $a_content["currency_code"]);
		
		if($this->isLastMileDelivery())	
			foreach($a_content["windows"] as $window)
				$this->addDeliveryWindow($window);
						
		$this->shopper_address = new Address($a_content["destination"]);
		
		foreach($a_content["products"] as $product)
			$this->addProduct($product);
	}
	
	public function asArray()
	{	 
		$result = array(
				"merchant" => $this->getMerchantName(),
				"service_type" => $this->getServiceType(),
				"shipping_cost" => (string)$this->getShippingCost(),
				"order_number" => $this->getOrderNumber(),
				"order_date" => $this->getOrderDate()->format("Y-m-d"),
				"status"=> $this->getStatus(),
				"shopper_address" => $this->getShopperAddress()->asArray()
		);
		
		$result["products"] = array();
		foreach($this->getProducts() as $product)
			$result["products"][] = $product->asArray();
		
		if($this->isLastMileDelivery())
		{
			$result["windows"] = array();
			foreach($this->getDeliveryWindows() as $window)
				$result["windows"][] = $window->asArray();
		}
		
		return $result; 
	}
}