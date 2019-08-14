<?php

namespace Nektria\Recs\MerchantApi;

/**
 * Address
 *
 */
class Address
{
    /**
     * @var string
     */
    private $streetType;
    
    /**
     * @var string    
     */
    private $streetName;

    /**
     * @var string    
     */
    private $streetNumber;

    /**
     * @var string     
     */
    private $stairBuilding;

    /**
     * @var string     
     */
    private $floor;

    /**
     * @var string     
     */
    private $door;

    /**
     * @var string     
     */
    private $city;

    /**
     * @var string    
     */
    private $province;
    
    /**
     * @var string    
     */
    private $country_code;

    /**
     * @var string     
     */
    private $postalCode;

    /**
     * Build this Location from a array
     * @param array $params
     */
    public function __construct(array $params = null)
    {
    	if(is_null($params)) return;
    	    	
    	foreach(array("postal_code", "street_name", "city", "country_code") as $key)
    		if(! array_key_exists($key, $params))
    			throw new \Exception("key $key is compulsory in Address constructor.");
    	
    	$this->setPostalCode($params["postal_code"])
    		 ->setStreetType($params["street_type"])
    		 ->setStreetName($params["street_name"])
    		 ->setCity($params["city"])
    		 ->setCountryCode($params["country_code"]);
    		     	
    	if(array_key_exists("street_type", $params))
    		$this->setStreetType($params["street_type"]);
    	if(array_key_exists("street_number", $params))
    		$this->setStreetNumber($params["street_number"]);
    	if(array_key_exists("floor", $params))
    		$this->setFloor($params["floor"]);
    	if(array_key_exists("door", $params))
    		$this->setDoor($params["door"]);
    }	
    
    public function asArray()
    {
    	$result = array(    			
    		"postal_code" => $this->getPostalCode(),
    		"street_type" => $this->getStreetType(),
    		"street_name" => $this->getStreetName(),
    		"city" => $this->getCity(),
    		"country_code" => $this->getCountryCode()
    	);

    	if($this->hasStreetNumber())
    		$result["street_number"] = $this->getStreetNumber();
    		
    	if($this->hasFloor())
    		$result["floor"] = $this->getFloor();
    	
    	if($this->hasDoor())
    		$result["door"] = $this->getDoor();
    		    	
    	return $result;
    }

    /**
     * Set streetName
     *
     * @param string $streetName
     * @return Address
     */
    public function setStreetName($streetName)
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function hasStreetName()
    {
    	return ! is_null($this->streetName);
    }
    
    /**
     * Get streetName
     *
     * @return string 
     */
    public function getStreetName()
    {
        return $this->streetName;
    }
    
    /**
     * Set streetType
     *
     * @param string $streetType
     * @return Address
     */
    public function setStreetType($streetType)
    {
    	$this->streetType = $streetType;
    
    	return $this;
    }
    
    public function hasStreetType()
    {
    	return ! is_null($this->streetType);
    }
    
    /**
     * Get streetType
     *
     * @return string
     */
    public function getStreetType()
    {
    	return $this->streetType;
    }

    /**
     * Set streetNumber
     *
     * @param string $streetNumber
     * @return Address
     */
    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    /**
     * Get streetNumber
     *
     * @return string 
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }
    
    private function hasStreetNumber(){ return ! is_null($this->streetNumber); }

    /**
     * Set stairBuilding
     *
     * @param string $stairBuilding
     * @return Address
     */
    public function setStairBuilding($stairBuilding)
    {
        $this->stairBuilding = $stairBuilding;

        return $this;
    }

    /**
     * Get stairBuilding
     *
     * @return string 
     */
    public function getStairBuilding()
    {
        return $this->stairBuilding;
    }
    
    public function hasStairBuilding(){ return ! $this->stairBuilding == ""; }
    /**
     * Set floor
     *
     * @param string $floor
     * @return Address
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * Get floor
     *
     * @return string 
     */
    public function getFloor()
    {
        return $this->floor;
    }
    
    private function hasFloor(){ return ! is_null($this->floor); }

    /**
     * Set door
     *
     * @param string $door
     * @return Address
     */
    public function setDoor($door)
    {
        $this->door = $door;

        return $this;
    }

    /**
     * Get door
     *
     * @return string 
     */
    public function getDoor()
    {
        return $this->door;
    }
    
    private function hasDoor(){ return ! is_null($this->door); }

    /**
     * Set city
     *
     * @param string $city
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }
    
    public function hasCity()
    {
        return ! is_null($this->city);
    }

    /**
     * Set province
     *
     * @param string $province
     * @return Address
     */
    public function setProvince($province)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return string 
     */
    public function getProvince()
    {
        return $this->province;
    }
    
    public function hasProvince()
    {
        return ! is_null($this->province);
    }
    
    /**
     * Set country_code
     *
     * @param string $country_code
     * @return Address
     */
    public function setCountryCode($country_code)
    {
    	$this->country_code = $country_code;
    
    	return $this;
    }
    
    /**
     * Get country_code
     *
     * @return string
     */
    public function getCountryCode()
    {
    	return $this->country_code;
    }
    
    public function hasCountryCode()
    {
    	return ! is_null($this->country_code);
    }

    /**
     * Set postalCode
     *
     * @param string $postalCode
     * @return Address
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string 
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }
    
    public function hasPostalCode()
    {
    	return ! is_null($this->postalCode);
    }       
}
