<?php
/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
 
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 
Copyright (C) 2007 Marco Aurelio Graciotto Silva <magsilva@gmail.com>
*/

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
		        setcookie($name, '', time() - 1000);
		        setcookie($name, '', time() - 1000, '/');
		    }
		}
	}    
}
?>