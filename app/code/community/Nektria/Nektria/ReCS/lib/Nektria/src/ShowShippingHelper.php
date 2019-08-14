<?php

namespace Nektria\Recs\MerchantApi;

use Nektria\Recs\MerchantApi\Responses\ShowShippingResponse;

/**
 * Class ShowShippingHelper: ShowShippingResponse wrapper to display things
 * @package Nektria\Recs\MerchantApi
 */
class ShowShippingHelper
{
    private $response;
    private $locale;

    public function __construct(ShowShippingResponse $response, $locale)
    {
        $this->response = $response;
        $this->locale = $locale;
    }


    public function getResponse(){ return $this->response; }
    public function isLastMile()
    {
        return $this->response->isLastMile();
    }

    /**
     * Get string explaining status of the shipping resposne
     * @return string human readable status
     */
    public function getStatus()
    {
        // if($this->locale != "ES_es") $this->warning !;

        switch($this->response->getStatus())
        {
            case "pending": $wording = "Servicio previsto. Pendiente de asignación a operador logístico de RECS";break;
            case "in-process": $wording = "Servicio asignado a operador logístico de RECS";break;
            case "completed": $wording = "Entregado a consumidor";break;
            case "cancelled": $wording = "Cancelado" ;break;
            default: $wording = "Estado desconocido.";
        }
        return $wording;
    }

    public function getDeliveryWindows()
    {
        // if($this->locale != "ES_es") $this->warning !;
        setlocale(LC_TIME, "ES_es");

        $delivery_windows = array();
        foreach($this->response->getDeliveryWindows() as $time_window)
        {
            $formatted_time = strftime("%A %e %B", $time_window->getStartTime()->getTimestamp());
            $formatted_time .= " entre las ".$time_window->getStartTime()->format("H")."h";
            $formatted_time .= " y las ".$time_window->getEndTime()->format("H")."h";
            $delivery_windows[] = $formatted_time;
        }
        return $delivery_windows;
    }
}