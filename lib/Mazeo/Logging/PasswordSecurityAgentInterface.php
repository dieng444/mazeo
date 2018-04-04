<?php

namespace Mazeo\Logging;

/**
 * Interface PasswordSecurityAgentInterface
 * @author Macky Dieng
 * @package Mazeo\Logging
 */
interface PasswordSecurityAgentInterface
{
    /**
     * Allows to hash a given password string
     * @param string $password - the password to be hashed
     * @return string
     */
    public static function hash($password);

    /**
     * Checks whether user password match stored password
     * @param string $password - the password to verify
     * @param string $hash - the hashed password
     * @return boolean
     */
    public static function verify($password, $hash);

    /**
     * Returns hashed password information
     * @param $hashedPassword - the hashed password
     * @return array
     */
    public static function getHashedPasswordInfo($hashedPassword);
}
