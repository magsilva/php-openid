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

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

require_once('Auth/OpenID/Consumer.php');
require_once('Auth/OpenID/FileStore.php');
require_once('Auth/OpenID/SReg.php');

class OpenIDClient
{
	/**
	 * This is where the OpenID information will be stored.
	 */
	var $store_path = "/tmp/_php_consumer_test";

	var $store;

	var $consumer;

	function isAuthRequestConditionOk()
	{
		return true;
	}

	function isAuthResponseConditionOk()
	{
		if (! isset($_REQUEST['openid_identity']) && ! isset($_REQUEST['openid_mode'])) {
			return false;
		}
		if ($_REQUEST['openid_mode'] !== 'id_res') {
			return false;
		}
		return true;
	}
	
	function initialize()
	{
		if (!file_exists($this->store_path) && !mkdir($this->store_path)) {
			return null;
		}

		$this->store = new Auth_OpenID_FileStore($this->store_path);
		$this->consumer = new Auth_OpenID_Consumer($this->store);
	}

	function OpenIDClient()
	{
	}

	function doAuthRequest($login, $returnto_url)
	{
		if (! $this->isAuthRequestConditionOk()) {
			return false;
		}
		
		if (! isset($login) || empty($login)) {
			return false;
		}
			
		$this->initialize();
	
		$scheme = 'http';
		if (isset ($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
			$scheme .= 's';
		}

		if (! isset($returnto_url) || empty($returnto_url)) {
			$returnto_url = sprintf("$scheme://%s:%s%s?%s",
				$_SERVER['SERVER_NAME'],
				$_SERVER['SERVER_PORT'],
				$_SERVER['PHP_SELF'],
				$_SERVER['QUERY_STRING']);
		}
		
		$trusted_root = sprintf("$scheme://%s:%s%s",
			$_SERVER['SERVER_NAME'],
			$_SERVER['SERVER_PORT'],
			dirname($_SERVER['PHP_SELF']));

		// Begin the OpenID authentication process.
		$auth_request = $this->consumer->begin($login);
	
		// Handle failure status return values.
		if (! $auth_request) {
			return false;
		}

		$sreg_request = Auth_OpenID_SRegRequest::build(
			// Required
			array('nickname'),
			// Optional
			array('fullname', 'email'));

		if ($sreg_request) {
			$auth_request->addExtension($sreg_request);
		}
		
		// Redirect the user to the OpenID server for authentication.
		// Store the token for this authentication so we can verify the
		// response.
		
		// For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
		// form to send a POST request to the server.
		$redirect_url = $auth_request->redirectURL($trusted_root, $returnto_url);

		// If the redirect URL can't be built, display an error message.
		if (Auth_OpenID::isFailure($redirect_url)) {
			return false;
		} else {
			// Send redirect.
			// session_write_close();
			// ob_end_flush();
			header("Location: ".$redirect_url);
			exit();
		}
	}
	
	
	function handleAuthResponse()
	{
		if (! $this->isAuthResponseConditionOk() ) {
			return false;
		}
		$this->initialize();
		
		$response = $this->consumer->complete();
		
		if ($response->status == Auth_OpenID_CANCEL) {
	    	return false;
		} else if ($response->status == Auth_OpenID_FAILURE) {
	    	return false;
		} else if ($response->status == Auth_OpenID_SUCCESS) {
	    	// This means the authentication succeeded.
	    	$openid = $response->identity_url;
	    	
	    	/*	
        	$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);

        	$sreg = $sreg_resp->contents();

        	if (@$sreg['email']) {
            	$success .= "  You also returned '".$sreg['email']."' as your email.";
	        }

    	    if (@$sreg['nickname']) {
        	    $success .= "  Your nickname is '".$sreg['nickname']."'.";
        	    }

        	if (@$sreg['fullname']) {
            	$success .= "  Your fullname is '".$sreg['fullname']."'.";
        	}
        	*/
        	
        	return true;
		}

		return false;
	}	
}

?>
