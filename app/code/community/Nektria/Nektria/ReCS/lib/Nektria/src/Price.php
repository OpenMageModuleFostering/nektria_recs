<?php

namespace Nektria\Recs\MerchantApi;

use Nektria\Recs\MerchantApi\Exceptions\ApiClientException;

class Price
{
	private $amount;
	private $currency_code;
	private $currency_sign;
	
	public function __construct($amount, $currency_code)
	{
		if($currency_code != "EUR")
			throw new ApiClientException("Our only accepted currency is EUR.");
		
		$this->amount = $amount;
		$this->currency_code = $currency_code;
		$this->currency_sign = "€";
	}
	
	public function getAmount(){ return $this->amount; }
	public function getCurrencyCode(){ return $this->currency_code; }
	public function getCurrency(){ return $this->getCurrencyCode(); }  // deprecated
	public function getCurrencySign(){ return $this->currency_sign; }
	
	public function __toString(){ return $this->getAmount().$this->getCurrencySign(); }
}