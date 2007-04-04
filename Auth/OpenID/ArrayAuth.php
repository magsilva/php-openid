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

/**
 * Authentication mechanism that store the user's credentials into an array.
 * 
 * @package OpenID
 * @author Marco Aurélio Graciotto Silva <magsilva@gmail.com>
 */
class Auth_OpenID_ArrayAuth extends Auth_OpenID_OpenIDAuth {

	/**
	 * Return a hashed form of the user's password
	 */
	function hashPassword($password) {
		switch ($this->hash_function) {
			case 'SHA':
			default:
				return bin2hex(Auth_OpenID_SHA1($password));		
		}
	}

	function Auth_OpenID_ArrayAuth($hash_function = 'SHA', $credentials = array()) {
		$this->hash_function  = $hash_function;
		$this->credentials = $credentials;
	}
	
	function add($openid_url, $password) {
		$this->credentials[$openid_url] = $this->hashPassword($password);
	}
	
	function del($openid_url, $password) {
		if (isset($this->credentials[$openid_url])) {
			unset($this->credentials[$openid_url]);
		}
	}

	/**
	 * Check user login
	 * 
	 * @param $openid_url User's OpenID URL.
	 * @param $password User's password.
	 * 
	 * @return True if the password is correct, False otherwise.
	 */
	function checkLogin($openid_url, $password) {
		if (!isset($this->credentials[$openid_url])) {
			return false;
		}
		
		return ($this->credentials[$openid_url] == $this->hashPassword($password));
	}
}



?>
