<?php

namespace Nektria\Recs\MerchantApi;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

class Price
{
	private $amount;
	private $currency;
	
	public function __construct($amount, $currency)
	{
		if($currency != "EUR")
			throw new ApiClientException("Our only accepted currency is EUR.");
		
		$this->amount = $amount;
		$this->currency = $currency;
	}
	
	public function getAmount(){ return $this->amount; }
	public function getCurrency(){ return $this->currency; }
	public function getCurrencySign(){ return "€"; }
	
	public function __toString(){ return $this->getAmount().$this->getCurrencySign(); }
}