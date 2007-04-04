<?php

/**
 * This file supplies a dumb store backend for OpenID servers and
 * consumers.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

/**
 * Import the interface for creating a new store class.
 */
require_once 'Auth/OpenID/Interface.php';

/**
 * @package OpenID
 */
class Auth_OpenID_LDAPStore extends Auth_OpenID_OpenIDStore
{
	static $auth_key;
	
	/**
     * @param server_name ldaps://hostname/
     */
    function Auth_OpenID_LDAPStore($server_name, $base_dn, $bind_username = null, $bind_password = null, $user_filter = null)
    {
    	$this->server = ldap_connect($server_name);
    	ldap_set_option($this->server, LDAP_OPT_PROTOCOL_VERSION, 3);
    	ldap_set_option($this->server, LDAP_OPT_DEREF, LDAP_DEREF_ALWAYS);
    	ldap_start_tls($this->server);
    	$this->binding = ldap_bind($this->server, $bind_username, $bind_password);
    	
    	$this->auth_key = Auth_OpenID_CryptUtil::randomString($this->AUTH_KEY_LEN);
    }

	function destroy()
	{
		ldap_close($this->server);
	}

	function get_username($url)
	{
	}

	function get_user($url)
	{
    	$username = $this->get_username($server_url);
    	$sr = ldap_search($this->server, $username);
    	if (ldap_count_entries($this->server, $sr) != 1) {
    		return null;
    	}
    	$userinfo = ldap_get_entries($this->server, $sr);
    	$userinfo = $userinfo[0];
    	return $userinfo;
	}

    /**
     * This method puts an Association object into storage,
     * retrievable by server URL and handle.
     *
     * @param string $server_url The URL of the identity server that
     * this association is with. Because of the way the server portion
     * of the library uses this interface, don't assume there are any
     * limitations on the character set of the input string. In
     * particular, expect to see unescaped non-url-safe characters in
     * the server_url field.
     *
     * @param Association $association The Association to store.
     */
    function storeAssociation($server_url, $association)
    {
    	$user = $this->get_user($server_url);
    	$username = $user['dn'];
    	$attrs = array();
    	$attrs['association'] = $association;
    	ldap_mod_add($this->server, $username, $attrs);
    	
    	/*
    	$server_url,
                                            $association->handle,
                                            $this->blobEncode(
                                                  $association->secret),
                                            $association->issued,
                                            $association->lifetime,
                                            $association->assoc_type
                                            */
    }

    /**
     * This method returns an Association object from storage that
     * matches the server URL and, if specified, handle. It returns
     * null if no such association is found or if the matching
     * association is expired.
     *
     * If no handle is specified, the store may return any association
     * which matches the server URL. If multiple associations are
     * valid, the recommended return value for this method is the one
     * that will remain valid for the longest duration.
     *
     * This method is allowed (and encouraged) to garbage collect
     * expired associations when found. This method must not return
     * expired associations.
     *
     * @param string $server_url The URL of the identity server to get
     * the association for. Because of the way the server portion of
     * the library uses this interface, don't assume there are any
     * limitations on the character set of the input string.  In
     * particular, expect to see unescaped non-url-safe characters in
     * the server_url field.
     *
     * @param mixed $handle This optional parameter is the handle of
     * the specific association to get. If no specific handle is
     * provided, any valid association matching the server URL is
     * returned.
     *
     * @return Association The Association for the given identity
     * server.
     */
    function getAssociation($server_url, $handle = null)
    {
    	$userinfo = $this->get_user($server_url);
    	
    	$association = new Auth_OpenID_Association(
    		$assoc_row['handle'],
    		$assoc_row['secret'],
    		$assoc_row['issued'],
    		$assoc_row['lifetime'],
    		$assoc_row['assoc_type']);
    	
    	if ($association->getExpiresIn() == 0) {
    		$this->removeAssociation($server_url, $assoc->handle);
    	}
    	return $association;
    }

    /**
     * This method removes the matching association if it's found, and
     * returns whether the association was removed or not.
     *
     * @param string $server_url The URL of the identity server the
     * association to remove belongs to. Because of the way the server
     * portion of the library uses this interface, don't assume there
     * are any limitations on the character set of the input
     * string. In particular, expect to see unescaped non-url-safe
     * characters in the server_url field.
     *
     * @param string $handle This is the handle of the association to
     * remove. If there isn't an association found that matches both
     * the given URL and handle, then there was no matching handle
     * found.
     *
     * @return mixed Returns whether or not the given association existed.
     */
    function removeAssociation($server_url, $handle)
    {
    	$userinfo = $this->get_user($server_url);
    	if (isset($userinfo['association'])) {
    		$attrs = array();
    		$attrs['association'] = array();
    		ldap_mod_del($this->server, $userinfo['dn'], $attrs);
    		return true;
    	} else {
    		return false;
    	}
    }

    /**
     * Stores a nonce. This is used by the consumer to prevent replay
     * attacks.
     *
     * @param string $nonce The nonce to store.
     *
     * @return null
     */
    function storeNonce($nonce)
    {
    }

    /**
     * This method is called when the library is attempting to use a
     * nonce. If the nonce is in the store, this method removes it and
     * returns a value which evaluates as true. Otherwise it returns a
     * value which evaluates as false.
     *
     * This method is allowed and encouraged to treat nonces older
     * than some period (a very conservative window would be 6 hours,
     * for example) as no longer existing, and return False and remove
     * them.
     *
     * @param string $nonce The nonce to use.
     *
     * @return bool Whether or not the nonce was valid.
     */
    function useNonce($nonce)
    {
        return true;
    }

  	/**
     * This method returns a key used to sign the tokens, to ensure
     * that they haven't been tampered with in transit. It should
     * return the same key every time it is called. The key returned
     * should be {@link AUTH_KEY_LEN} bytes long.
     *
     * @return string The key. It should be {@link AUTH_KEY_LEN} bytes in
     * length, and use the full range of byte values. That is, it
     * should be treated as a lump of binary data stored in a string.
     */
    function getAuthKey()
    {
    	if (strlen($this->auth_key) != $this->AUTH_KEY_LEN) {
            $fmt = "Expected %d-byte string for auth key. Got key of length %d";
            trigger_error(sprintf($fmt, $this->AUTH_KEY_LEN, strlen($this->auth_key)), E_USER_WARNING);
            return null;
        }
        return $this->auth_key;
    }
    
    /**
     * Removes all entries from the store; implementation is optional.
     */
    function reset()
    {
    }
    
}

?>