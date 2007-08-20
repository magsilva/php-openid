<?php

/**
 * This is the PHP OpenID library by JanRain, Inc.
 *
 * This module contains core utility functionality used by the
 * library.  See Consumer.php and Server.php for the consumer and
 * server implementations.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package HTTP
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

class HTTP
{
	/**
     * Implements the PHP 5 'http_build_query' functionality.
     *
     * @access private
     * @param array $data Either an array key/value pairs or an array
     * of arrays, each of which holding two values: a key and a value,
     * sequentially.
     * @return string $result The result of url-encoding the key/value
     * pairs from $data into a URL query string
     * (e.g. "username=bob&id=56").
     */
    function buildQuery($data)
    {
        if (function_exists('http_build_query')) {
        	return http_build_query($data);
        }
        
        $pairs = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $pairs[] = urlencode($value[0]) . '=' . urlencode($value[1]);
            } else {
                $pairs[] = urlencode($key) . '=' . urlencode($value);
            }
        }
        return implode('&', $pairs);
    }
    
    /**
     * Find the redirect URL into a HTTP header.
     */
    function findRedirect($headers)
    {
        foreach ($headers as $line) {
            if (strpos($line, 'Location: ') === 0) {
                $parts = explode(' ', $line, 2);
                return $parts[1];
            }
        }
        return null;
    }
    
}

?>
