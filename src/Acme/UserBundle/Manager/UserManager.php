<?php	

namespace Acme\UserBundle\Manager;

use Mazeo\Main\Manager\Manager;

/**
* class	UserManager
* @package	Acme\UserBundle\Manager
**/
class UserManager extends Manager {

	/**
	* @var string TABLE_NAME
	*/
	const TABLE_NAME = 'users';

	/**
	* @var string ID
	*/
	const ID = 'id';

	/**
	* @var array COLUMN_EXCEPTION
	*/
	const COLUMN_EXCEPTION = array(false,'');
}
