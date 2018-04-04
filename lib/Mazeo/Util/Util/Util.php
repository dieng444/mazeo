<?php

namespace Mazeo\Util\Util;

/**
 * Class Util
 * @package Mazeo\Util\Util
 * @author Macky Dieng
 */
class Util {

    /**
     * Checks whether two arrays are equal or not
     * @param $array1 - the first array in the comparison
     * @param $array2 - the second array in the comparison
     * @return bool
     */
    public static final function isEqualArray($array1, $array2)
    {
        if(sizeof($array1) !== sizeof($array2) || sizeof(array_diff($array1, $array2)) > 0) return false;
        else return true;
    }
    public static final function arrayKeysExists(array $keys, array $arr)
    {
       return !array_diff_key(array_flip($keys), $arr);
    }
}
