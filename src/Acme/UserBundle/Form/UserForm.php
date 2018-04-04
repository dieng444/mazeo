<?php

namespace Acme\UserBundle\Form;

use Mazeo\Main\Form\Form;

use Acme\UserBundle\Entity\User;

/**
* class	UserForm
* @package	Loura\UserBundle\Form
**/
class UserForm extends Form {

	/**
	* UserForm constructor
	* @param User $user
	*/
	public function __construct(User $user)
	{
		parent::__construct($user);
	}
}
