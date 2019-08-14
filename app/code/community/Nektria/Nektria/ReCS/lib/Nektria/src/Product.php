<?php

namespace Nektria\Recs\MerchantApi;

/**
 * Product
 **/
class Product
{
    
    /**
     * @var string
     *
     */
    private $reference;
    
    /**
     * @var string
     *
     */
    private $name;
    
    /**
     * @var float
     */
    private $price;
    
    /**
     * @var integer     
     */
    private $height;

    /**
     * @var integer
     */
    private $width;

    /**
     * @var integer
     */
    private $depth;

    /**
     * @var float
     */
    private $weight;

    /**
     * Initialize ride collection
     * Initialized from array format if supplied
     * 		Note: we do NOT take into account origin_information, as this is another entity.
     * 
     * Constructor
     */
    public function __construct(array $params = null)
    {        
        if(is_null($params)) return;
        
        foreach(array("name", "reference") as $key)
        	if(! array_key_exists($key, $params))
        		throw new \Exception("key $key is compulsory in Product entity constructor.");
        	
        if(array_key_exists("size", $params))
        	foreach(array("height_cm", "width_cm", "depth_cm") as $key)
        		if(! array_key_exists($key, $params["size"]))
        			throw new \Exception("key $key is compulsory in Product Size entity constructor (whenever a size is specified).");
        
        $this->setName($params["name"]);
        $this->setReference($params["reference"]);
        
        if(array_key_exists("size", $params))
        {
        	$this->setHeight($params["size"]["height_cm"]);
        	$this->setWidth($params["size"]["width_cm"]);
        	$this->setDepth($params["size"]["depth_cm"]);
        }
        
        $this->setWeight($params["weight_kg"]);

        if(array_key_exists("price", $params))
        	$this->setPrice($params["price"]);
    }

	public function asArray()
	{
		$result = array(
				"name" => $this->getName(),
				"reference" => $this->getReference(),				
		);
		
		if($this->hasSize())
			$result["size"] = array(
						"height" => $this->getHeight(),
						"width" => $this->getWidth(),
						"depth" => $this->getDepth()
			);
			
		if($this->hasWeight())
			$result["weight"] = $this->getWeight();
		
		if($this->hasPrice())
		{
			$result["price"] = $this->getPrice();
			$result["currency_code"] = $this->getCurrencyCode();
		}
		
		return $result;
	}

    /**
     * Set product number
     *
     * @param string $number
     * @return Product
     */
    public function setReference($number)
    {
    	$this->reference = $number;
    
    	return $this;
    }
    
    /**
     * Get name
     *
     * @return string
     */
    public function getReference()
    {
    	return $this->reference;
    }
    
    /**
     * Set name
     *
     * @param string $name
     * @return Product
     */
    public function setName($name)
    {
    	$this->name = $name;
    
    	return $this;
    }
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
    	return $this->name;
    }
    
    /**
     * Set price
     *
     * @param float $price
     * @return Product
     */
    public function setPrice($price)
    {
    	$this->price = $price;
    
    	return $this;
    }
    
    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
    	return $this->price;
    }    
    
    public function hasPrice(){ return ! is_null($this->price); }
    
    /**
     * Set height
     *
     * @param integer $height
     * @return Product
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }    
    
    /**
     * Get height
     *
     * @return integer 
     */
    public function getHeight()
    {
        return $this->height;
    }

    private function hasHeight(){ return ! is_null($this->height); }
    
    /**
     * Set width
     *
     * @param integer $width
     * @return Product
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer 
     */
    public function getWidth()
    {
        return $this->width;
    }

    private function hasWidth(){ return ! is_null($this->width); }
    
    /**
     * Set depth
     *
     * @param integer $depth
     * @return Product
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Get depth
     *
     * @return integer 
     */
    public function getDepth()
    {
        return $this->depth;
    }
    
    private function hasDepth(){ return ! is_null($this->depth); }
    
    public function hasSize()
    {
    	if(!$this->hasHeight()) return false;
    	if(!$this->hasWidth()) return false;
    	if($this->hasDepth()) return false;
    	
    	return true;
    }

    /**
     * Set weight
     *
     * @param float $weight
     * @return Product
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return float 
     */
    public function getWeight()
    {
        return $this->weight;
    }
    
    public function hasWeight(){ return ! is_null($this->weight); } 
}
