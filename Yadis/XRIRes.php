<?php

/**
 * Code for using a proxy XRI resolver.
 *
 * @package Yadis
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */
 
require_once('Yadis/XRDS.php');
require_once('Yadis/XRI.php');


class Yadis_ProxyResolver
{
    function Yadis_ProxyResolver(&$fetcher, $proxy_url = null)
    {
        $this->fetcher =& $fetcher;
        $this->proxy_url = $proxy_url;
        if (!$this->proxy_url) {
            $this->proxy_url = Yadis_XRI::getDefaultProxy();
        }
    }

    function queryURL($xri, $service_type = null)
    {
        // trim off the xri:// prefix
        $qxri = substr(Yadis_XRI::toURINormal($xri), 6);
        $hxri = $this->proxy_url . $qxri;
        $args = array('_xrd_r' => 'application/xrds+xml');

        if ($service_type) {
            $args['_xrd_t'] = $service_type;
        } else {
            // Don't perform service endpoint selection.
            $args['_xrd_r'] .= ';sep=false';
        }

        $query = Yadis_XRI::appendArgs($hxri, $args);
        return $query;
    }

    function query($xri, $service_types, $filters = array())
    {
        $services = array();
        $canonicalID = null;
        foreach ($service_types as $service_type) {
            $url = $this->queryURL($xri, $service_type);
            $response = $this->fetcher->get($url);
            if ($response->status != 200) {
                continue;
            }
            $xrds = Yadis_XRDS::parseXRDS($response->body);
            if (!$xrds) {
                continue;
            }
            $canonicalID = Yadis_XRI::getCanonicalID($xri, $xrds);

            if ($canonicalID === false) {
                return null;
            }

            $some_services = $xrds->services($filters);
            $services = array_merge($services, $some_services);
            // TODO:
            //  * If we do get hits for multiple service_types, we're
            //    almost certainly going to have duplicated service
            //    entries and broken priority ordering.
        }
        return array($canonicalID, $services);
    }
}

?>