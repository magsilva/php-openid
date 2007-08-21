<?php

/**
 * The OpenID library's Diffie-Hellman implementation.
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

require_once('common/String.php');
require_once('common/BigMath.php');
require_once('common/HashSHA.php');
require_once('OpenID/OpenID.php');

/**
 * The Diffie-Hellman key exchange class.  This class relies on
 * {@link MathLibrary} to perform large number operations.
 *
 * @access private
 * @package OpenID
 */
class DiffieHellman {

    var $mod;
    var $gen;
    var $private;
    var $lib = null;

	function getDefaultMod()
	{
	    return '155172898181473697471232257763715539915724801'.
	        '966915404479707795314057629378541917580651227423'.
	        '698188993727816152646631438561595825688188889951'.
	        '272158842675419950341258706556549803580104870537'.
	        '681476726513255747040765857479291291572334510643'.
	        '245094715007229621094194349783925984760375594985'.
	        '848253359305585439638443';
	}
	
	function getDefaultGen()
	{
	    return '2';
	}


    function DiffieHellman($mod = null, $gen = null, $private = null, $lib = null)
    {
        if ($lib === null) {
            $this->lib =& BigMath::getMathLib();
        } else {
            $this->lib =& $lib;
        }

        if ($mod === null) {
            $this->mod = $this->lib->init(OpenID_DiffieHellman::getDefaultMod());
        } else {
            $this->mod = $mod;
        }

        if ($gen === null) {
            $this->gen = $this->lib->init(OpenID_DiffieHellman::getDefaultGen());
        } else {
            $this->gen = $gen;
        }

        if ($private === null) {
            $r = $this->lib->rand($this->mod);
            $this->private = $this->lib->add($r, 1);
        } else {
            $this->private = $private;
        }

        $this->public = $this->lib->powmod($this->gen, $this->private,
                                           $this->mod);
    }

    function getSharedSecret($composite)
    {
        return $this->lib->powmod($composite, $this->private, $this->mod);
    }

    function getPublicKey()
    {
        return $this->public;
    }

	// TODO: Remove the code below
    /**
     * Generate the arguments for an OpenID Diffie-Hellman association
     * request.
     * 
     */
    /*
    function getAssocArgs()
    {
        $cpub = $this->lib->longToBase64($this->getPublicKey());
        $args = array(
                      'openid.dh_consumer_public' => $cpub,
                      'openid.session_type' => 'DH-SHA1'
                      );

        if ($this->lib->cmp($this->mod, OpenID_DiffieHellman::getDefaultMod()) ||
            $this->lib->cmp($this->gen, OpenID_DiffieHellman::getDefaultGen())) {
            $args['openid.dh_modulus'] = $this->lib->longToBase64($this->mod);
            $args['openid.dh_gen'] = $this->lib->longToBase64($this->gen);
        }

        return $args;
    }
	*/
	
    function usingDefaultValues()
    {
        return ($this->mod == OpenID_DiffieHellman::getDefaultMod() &&
                $this->gen == OpenID_DiffieHellman::getDefaultGen());
    }

    function xorSecret($composite, $secret, $hash_func)
    {
        $dh_shared = $this->getSharedSecret($composite);
        $dh_shared_str = $this->lib->longToBinary($dh_shared);
        $hash_dh_shared = $hash_func($dh_shared_str);

        $xsecret = '';
        for ($i = 0; $i < StringUtil::bytes($secret); $i++) {
            $xsecret .= chr(ord($secret[$i]) ^ ord($hash_dh_shared[$i]));
        }

        return $xsecret;
    }
}
