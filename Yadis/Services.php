<?php
/**
 * Yadis supported services.
 * 
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package Yadis
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL *
 */

require_once('Yadis/Manager.php');

/**
 * A concrete loader implementation for OpenID_ServiceEndpoints.
 *
 * @package Yadis
 */
class Yadis_OpenID_ServiceEndpointLoader extends Yadis_SessionLoader
{
    function newObject($data)
    {
        return new OpenID_ServiceEndpoint();
    }

    function requiredKeys()
    {
        $obj = new OpenID_ServiceEndpoint();
        $data = array();
        foreach ($obj as $k => $v) {
            $data[] = $k;
        }
        return $data;
    }

    function check($data)
    {
        return is_array($data['type_uris']);
    }
}


?>
