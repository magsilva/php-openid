<?php

class OpenID_Server {

    function OpenID_Server()
	{
    }
    
    function createCookie($value)
    {
    	$timeout = time() + 60 *60 * 24 * 1;
    	$path = '/';
    	$domain = $_SERVER['SERVER_NAME'];
    
    	// http://br.php.net/manual/en/function.setcookie.php#27819
    	// header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
    	setcookie('openid', $value, $timeout, $path, $domain);
    }

	/**
	 * Cookies must be deleted with the same parameters as they were set with.
	 * If the value argument is an empty string, or FALSE, and all other
	 * arguments match a previous call to setcookie, then the cookie with the
	 * specified name will be deleted from the remote client.
	 * 
	 * Because  setting a cookie with a value of FALSE will try to delete the
	 * cookie, you should not use boolean values. Instead, use 0 for FALSE and
	 * 1 for TRUE.
	 * 
	 * About the delete part, I found that Firefox only remove the cookie when
	 * you submit the same values for all parameters, except the date, which
	 * sould be in the past. Submiting blank values didn't work for me.
	 * 
	 * http://br.php.net/manual/en/function.setcookie.php#75343
	 */
    function removeCookie()
	{
		// unset cookies
		if (isset($_SERVER['HTTP_COOKIE'])) {
		    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		    foreach($cookies as $cookie) {
		        $parts = explode('=', $cookie);
		        $name = trim($parts[0]);
		        setcookie($name, '', time()-1000);
		        setcookie($name, '', time()-1000, '/');
		    }
		}
	}    
}
?>