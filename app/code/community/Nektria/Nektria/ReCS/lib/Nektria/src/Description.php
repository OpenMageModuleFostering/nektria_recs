<?php

namespace Nektria\Recs\MerchantApi;

use GuzzleHttp\Command\Guzzle\Description as GuzzleDescription;

class Description extends GuzzleDescription
{
	public function setBaseUrl($url)
	{
		$this->baseUrl = $url;
	}
}
