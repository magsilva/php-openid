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
 * Authentication mechanism that store the user's credentials into an LDAP
 * directory.
 * 
 * @package OpenID
 * @author Marco Aurélio Graciotto Silva <magsilva@gmail.com>
 */
class Auth_OpenID_LDAPAuth extends Auth_OpenID_OpenIDAuth {

	function get_username($openid_url) {
		$openid_url = preg_replace('/^https:\/\//', '', $openid_url);
		$openid_url = preg_replace('/^http:\/\//', '', $openid_url);
	
		list($username) = sscanf($openid_url, $this->principal_format);
		return $username;
	}

	function get_user($openid_url)	{
    	$username = $this->get_username($openid_url);
    	if ($username == NULL) {
    		trigger_error('Auth_OpenID_LDAPAuth::get_user() - Invalid credentials', E_USER_ERROR);
    		return NULL;
    	}
    	$filter = str_replace("%USERNAME%", $username, $this->user_filter);
    	
    	$sr = ldap_search($this->server, $this->base_dn, $filter);

    	if (ldap_count_entries($this->server, $sr) != 1) {
    		return null;
    	}
    	$userinfo = ldap_get_entries($this->server, $sr);
    	$userinfo = $userinfo[0];
    	return $userinfo;
	}

	/**
	 * @param $principal_format String format for the credentials this
	 * principal accepts.
	 */
	function Auth_OpenID_LDAPAuth($principal_format, $server_name, $base_dn, $bind_username = null, $bind_password = null, $user_filter = '(uid=%USERNAME%)')
	{
		$this->principal_format = $principal_format;
    	$this->server = ldap_connect($server_name);
    	$this->base_dn = $base_dn;
    	$this->user_filter = $user_filter;

    	ldap_set_option($this->server, LDAP_OPT_PROTOCOL_VERSION, 3);
    	ldap_set_option($this->server, LDAP_OPT_DEREF, LDAP_DEREF_ALWAYS);
    	@ldap_start_tls($this->server);
    	$this->binding = ldap_bind($this->server, $bind_username, $bind_password);
    	if ($this->binding == FALSE) {
    		trigger_error('Auth_OpenID_LDAPAuth::__construct() - Authentication error',	E_USER_ERROR);
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
		$user = $this->get_user($openid_url);

		return ldap_bind($this->server, $user['dn'], $password);
	}
}
?>
