<?php

namespace Mazeo\Logging;

use Mazeo\Database\Database as DB;
use Mazeo\Main\Entity\Entity;
use Mazeo\Main\Manager\Manager;
use Mazeo\Util\Util\MazeoException;
use Mazeo\Util\Util\Reflector;

/**
 * Class AuthManager
 * @author Macky Dieng
 * @package Mazeo\Logging
 */
class AuthManager
{
    /**
     * The class unique instance variable
     * @var AuthManager
     */
    private static $auth = null;

    /**
     * Contain the current user information
     * @var array
     */
    private $user;

    /**
     * @var PDO : PDO instance variable
     */
    private $db;

    private $provider;

    /**
     * Class constructor
     */
    private function __construct()
    {
      $this->user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
      $this->db = DB::getInstance()->getConnexion();
      $provider = $this->getAuthTableInfo()['provider'];
      if (class_exists($provider)) {
        $this->provider = new $provider();
        if (!($this->provider instanceof Manager)) {
          throw new MazeoException("The provider class {$provider}
          specified in app/auth_parameters.yml must be a type of Mazeo\\Main\\Manager\\Manager
          type of " . gettype($this->provider) . " found"); exit;
        }
      } else {throw new MazeoException("The provider class {$provider} specified in app/auth_parameters.yml not found"); exit; }
    }

    private function __clone()
    {
    }

    /**
     * Returns unique instance of the AuthManager
     * @return AuthManager
     */
    public static function getInstance()
    {
        if (null === self::$auth) self::$auth = new self();
        return self::$auth;
    }

    /**
     * Checks whether the current user exist in database
     * @param $password - the given password
     * @param $login - the given login
     * @throws MazeoException
     */
    public function checkAuthentication($password, $login)
    {
        $provider = $this->getProvider($login);
        if (sizeof($provider) >  0) {
            $user = $provider['user'];
            if (!method_exists($user,'getPassword')) {
                throw new MazeoException("The current provider class must have an \"getPassword\" method for perform password access check");
            } else {
                $isExistUser = PasswordSecurityAgent::verify($password,$provider['user']->getPassword());
                if ($isExistUser) {
                    $this->user = $provider['user'];
                    $this->synchronize();
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Checks whether the user is connected
     * @return boolean
     */
    public function isConnected()
    {
        return !is_null($this->user);
    }

    /**
     * Returns current user information
     * @return Entity
     */
    public final function getUser()
    {
        return $this->user;
    }

    /**
     * Sets current user information
     * @param Entity $user - the new user to assign
     * @throws MazeoException
     */
    public function setUser(Entity $user)
    {
        if ($this->isConnected()) {
            if (!($user instanceof Entity)) {
                throw new MazeoException("The type of method \"setUser\" parameter in the class ".__CLASS__."
                must be a subclass of Mazeo\\Main\\Entity\\Entity type of ".gettype($user). " given");
                exit;
            }
            $this->user = $user;
            $this->synchronize();
        }
    }

    /**
     * Logout the user and destroy session information
     */
    public function logout()
    {
        $this->user = null;
        $this->synchronize();
        unset($_SESSION);
    }

    /**
     * Synchronize current user information into a global variable
     * @return void
     */
    private function synchronize()
    {
        $_SESSION['user'] = $this->user;
    }

    /**
     * Checks whether the current user has some
     * permission, before executing certain action
     * @param string $role  - required role for the action
     * @return boolean
     * @throws MazeoException
     */
    public function HasRole($role)
    {
        if ($this->isConnected()) {
            $reflector = new Reflector($this->user);
            if (!method_exists($this->user,'getRoles')) {
                throw new MazeoException("Class {$reflector->getName()} must have an \"getRoles\" method for perform role access check");
            }
            return in_array($role, $this->user->getRoles()) ? true : false;
        } else return false;
    }

    private final function getAuthTableInfo()
    {
        $config = yaml_parse_file('app/auth_parameters.yml');
        $info = array();
        if (is_array($config) && sizeof($config)) {
            if (array_key_exists('auth', $config)) {
                if (array_key_exists('provider',$config['auth'])) $info['provider'] = $config['auth']['provider'];
                else { throw new MazeoException("The key \"provider\" missing in auth_parameter.yml file"); exit; }
                if (array_key_exists('passwordColumn',$config['auth'])) $info['passwordColumn'] = $config['auth']['passwordColumn'];
                else { throw new MazeoException("The key \"passwordColumn\" missing in auth_parameter.yml file"); exit; }
                if (array_key_exists('usernameColumn',$config['auth'])) $info['usernameColumn'] = $config['auth']['usernameColumn'];
                else { throw new MazeoException("The key \"usernameColumn\" missing in auth_parameter.yml file"); exit; }
            } else throw new MazeoException("The key \"auth\" missing in the configuration file app/auth_parameters.yml");
        } else throw new MazeoException("Authentication configuration file app/auth_parameters.yml is empty or not defined");
        return $info;
    }
    private final function getAuthData(array $info, $login)
    {
        $query = $this->db->prepare('select * from '.$info['table'].' where '.$info['usernameColumn'].' = :login');
        $query->bindValue(':login', $login);
        $query->execute();
        return $query->fetch();
    }

    /**
     * @param $login
     * @return Entity|null
     * @throws MazeoException
     */
    private final function getProvider($login) {
      $info = $this->getAuthTableInfo();
      $user = $this->provider->findOneBy(array(array($info['usernameColumn'] => $login,"eq")));
      $getter = 'get'.ucfirst($info['passwordColumn']);
      $result = array();
      if (!is_null($user)) {
        if ($user instanceof Entity) {
          if (method_exists($user, $getter)) {
            $result['getter'] = $getter;
            $result['user'] =  $user;
          } else throw new MazeoException("The provider class {$info['provider']} specified in app/auth_parameters.yml must have {$getter} method");
        } else {
          throw new MazeoException("Type of \"user provider\" in the class ".__CLASS__."
                  must be an subclass of Mazeo\\Main\\Entity\\Entity type of ".gettype($user). " given");
          exit;
        }
      }
      return $result;
    }
    public final function loginExist($login)
    {
      $provider = $this->getProvider($login)['user'];
      $exist = false;
      if ($provider instanceof Entity) $exist = true;
      return $exist;
    }
    /**
     * [getToken description]
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    public function getTokenObj($field, $value)
    {
      $query = $this->db->prepare('select * from reset_password where '.$field.' = :'.$field);
      $query->bindValue(':'.$field, $value);
      $query->execute();
      return $query->fetch();
    }
    public function removeToken($token)
    {
      $query = $this->db->prepare('delete from reset_password where token = :token');
      $query->bindValue(':token', $token);
      return $query->execute();
    }
    /**
     * @param $login
     * @return bool|string
     * @throws MazeoException
     */
    public final function getResetedPwdUser($token)
    {
      $data = $this->getTokenObj('token',$token);
      $user = null;
      if ($data) {
        $user = $this->provider->findOne($data['user_id']);
        if (!is_null($user)) {
          $this->removeToken($token);
          return $user;
        }
      }
      return $user;
    }

    /**
     * @param $login
     * @return bool|string
     * @throws MazeoException
     */
    public final function getResetPasswordToken($login)
    {
      $provider = $this->getProvider($login)['user'];
      $code = sha1(uniqid(mt_rand(), true));
      if ($provider instanceof Entity) {
        $class = new Reflector($provider);
        if (method_exists($provider, 'getId')) {
          $encoded_code = "'".$code."'";
          $token = $this->getTokenObj('user_id',$provider->getId())['token'];
          if (!is_null($token)) { /*Delete token if one existe for the current user*/
            $this->removeToken($token);
          }
          $this->db->query('insert into reset_password (user_id, token) values ('.$provider->getId().','.$encoded_code.')');
          return $code;
        } else { throw new MazeoException("AuthManager provider entity class {$class->getName()} must have the \"getId\" method"); exit; }
      } else return false;
    }
}
