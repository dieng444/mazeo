<?php

namespace Mazeo\Database;

use \PDO as PDO;

/**
 * Class Database  - Singleton providing connection to the database.
 * @author Macky Dieng
 */
class Database
{
    /**
    * @var Database  - Class instance variable
    */
    private static $instance;

    /**
    * PDO instance variable
    */
    protected $connexion;

    /**
    * Class constructor
    */
    private function __construct()
    {
        /***
         * Configuration of PDO Statement to decide how
         * the data will be retrieved
         */
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );
        $dbInfo = yaml_parse_file('app/parameters.yml');
        $driver = $dbInfo['database']['driver'];
        $host = $dbInfo['database']['host'];
        $port = $dbInfo['database']['port'];
        $name = $dbInfo['database']['name'];
        $user = $dbInfo['database']['user'];
        $password = $dbInfo['database']['password'];
        $dsn = null;
        if(!is_null($port)) $dsn = "{$driver}:host={$host};port={$port};dbname={$name};";
        else $dsn = "{$driver}:host={$host};dbname={$name};";
        if (empty($host) || empty($name) || empty($user) || empty($password)) $this->connexion = null;
        else {
            $this->connexion = new PDO($dsn, $user, $password, $options);
        }
    }
    /**
    * Disable cloning
    */
    private function __clone()
    {
    }

    /**
     * @return Database
     */
    public static function getInstance()
    {
        if (! (self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns the unique instance of the current database
     * @return PDO
     */
    public function getConnexion()
    {
        return $this->connexion;
    }
}
