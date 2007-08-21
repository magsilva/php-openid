<?php

/**
 * This is the HMACSHA1 implementation for the OpenID library.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @access private
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

requice_once('common/String.php');

/**
 * SHA1_BLOCKSIZE is this module's SHA1 blocksize used by the fallback
 * implementation.
 */
define('HASH_SHA1_BLOCKSIZE', 64);

class Hash_SHA
{
	function hashSHA1($text)
	{
	    if (function_exists('hash') && function_exists('hash_algos') && in_array('sha1', hash_algos())) {
	        // PHP 5 case (sometimes): 'hash' available and 'sha1' algo supported.
	        return hash('sha1', $text, true);
	    } else if (function_exists('sha1')) {
	        // PHP 4 case: 'sha1' available.
	        $hex = sha1($text);
	        $raw = '';
	        for ($i = 0; $i < 40; $i += 2) {
	            $hexcode = substr($hex, $i, 2);
	            $charcode = (int) base_convert($hexcode, 16, 10);
	            $raw .= chr($charcode);
	        }
	        return $raw;
	    } else {
	        // Explode.
	        trigger_error('No SHA1 function found', E_USER_ERROR);
	    }
	}
	
	/**
	 * Compute an HMAC/SHA1 hash.
	 *
	 * @access private
	 * @param string $key The HMAC key
	 * @param string $text The message text to hash
	 * @return string $mac The MAC
	 */
	function hashHMACSHA1($key, $text)
	{
	    if (StringUtil::bytes($key) > HASH_SHA1_BLOCKSIZE) {
	        $key = Auth_OpenID_SHA1($key, true);
	    }
	
	    $key = str_pad($key, HASH_SHA1_BLOCKSIZE, chr(0x00));
	    $ipad = str_repeat(chr(0x36), HASH_SHA1_BLOCKSIZE);
	    $opad = str_repeat(chr(0x5c), HASH_SHA1_BLOCKSIZE);
	    $hash1 = HashSHA::hashSHA1(($key ^ $ipad) . $text, true);
	    $hmac = HashSHA::hashSHA1(($key ^ $opad) . $hash1, true);
	    return $hmac;
	}
	
	function supportHashSHA256()
	{
		if (function_exists('hash') && function_exists('hash_algos') && in_array('sha256', hash_algos())) {
			return true;
		}
		return false;
	}

	function hashSHA256($key, $text)
	{
	    // Return raw MAC (not hex string).
	    return hash_hmac('sha256', $key, $text, true);
	}

	function supportHashHmacSHA256()
	{
		if (function_exists('hash_hmac') && function_exists('hash_algos') && in_array('sha256', hash_algos())) {
			return true;			
		}
		return false;
	}
	
	function hashHmacSHA256($key, $text)
	{
	    // Return raw MAC (not hex string).
	    return hash_hmac('sha256', $key, $text, true);
	}
}

?>