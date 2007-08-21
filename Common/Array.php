<?php

/**
 * @package Array
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */
class ArrayUtil
{
	function scramble($arr)
	{
	    $result = array();
	    while (count($arr)) {
	        $index = array_rand($arr, 1);
	        $result[] = $arr[$index];
	        unset($arr[$index]);
	    }
	
	    return $result;
	}
}

?>
