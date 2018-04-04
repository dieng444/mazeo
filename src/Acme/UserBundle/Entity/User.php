<?php

namespace Acme\UserBundle\Entity;

use Mazeo\Main\Entity\Entity;

/**
* class	User
* @package	Acme\UserBundle\Entity
**/
class User extends Entity {

	/**
	* @var $id
	*/
	private	$id;

	/**
	* @var $name
	*/
	private	$name;

	/**
	* @var $login
	*/
	private	$login;

	/**
	* @var $password
	*/
	private	$password;

	/**
	* User constructor
	* @param array $data
	*/
	public function __construct(array $data=array())
	{
		parent::__construct($data);
	}
	
	/**
	* @param $id
	*/
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	* @param $id
	*/
	public function getId()
	{
		return $this->id;
	}

	/**
	* @param $name
	*/
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	* @param $name
	*/
	public function getName()
	{
		return $this->name;
	}

	/**
	* @param $login
	*/
	public function setLogin($login)
	{
		$this->login = $login;
		return $this;
	}

	/**
	* @param $login
	*/
	public function getLogin()
	{
		return $this->login;
	}

	/**
	* @param $password
	*/
	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	/**
	* @param $password
	*/
	public function getPassword()
	{
		return $this->password;
	}
}
