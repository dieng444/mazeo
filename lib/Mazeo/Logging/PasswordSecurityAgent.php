<?php

namespace Mazeo\Logging;

use Mazeo\Util\Util\MazeoException;

/**
 * Interface PasswordSecurityAgentInterface
 * @author Macky Dieng
 * @package Mazeo\Logging
 */
class PasswordSecurityAgent implements PasswordSecurityAgentInterface
{
    /**
     * Allows to hash a given password string
     * @param string $password - the password to be hashed
     * @throws MazeoException
     * @return string
     */
    public static function hash($password)
    {

        $hashedPassword  = \password_hash($password, PASSWORD_BCRYPT, array('cost' => 10 ));
        if (!$hashedPassword) throw new MazeoException("An error are occurred during password hashing");
        return $hashedPassword;
    }

    /**
     * Checks whether user password match stored password
     * @param string $password - the password to verify
     * @param string $hash - the hashed password
     * @return boolean
     */
    public static function verify($password, $hash)
    {
        return \password_verify($password, $hash);
    }

    /**
     * Returns hashed password information
     * @param string $hashedPassword - the hashed password
     * @throws MazeoException
     * @return array
     */
    public static function getHashedPasswordInfo($hashedPassword)
    {
        $passwordInfo = \password_get_info($hashedPassword);
        if (!$passwordInfo) throw new MazeoException("Error occurred during geting current password information");
        return $passwordInfo;
    }
}
