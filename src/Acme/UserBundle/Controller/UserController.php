<?php

namespace Acme\UserBundle\Controller;

use Mazeo\Main\Controller\Controller;
use Acme\UserBundle\Entity\User;
use Acme\UserBundle\Form\UserForm;
use Acme\UserBundle\Manager\UserManager;
use Mazeo\Logging\PasswordSecurityAgent;
use Mazeo\Logging\AuthManager;
use Mazeo\Util\Util\Session;
/**
 *
 */
class UserController extends Controller
{
  private $userManager;
  private $session;

  /**
   * FrontController constructor.
   */
  public function __construct()
  {
    $this->userManager = new UserManager();
    $this->session = Session::getInstance();

    parent::__construct();
  }
  public function homeAction()
  {
    $this->display('AcmeUserBundleUser:home.twig', array());
  }
  public function signupAction()
  {
    $user = new User();
    $form  = new UserForm($user);
    if($this->request->isMethod('post')) {
      $form->bindRequest($this->request);
      if ($form->isValid()) {
        $msg = 'Compte créé avec succès, vous pouvez vous connecter avec vos nouveaux identifiants dès à présent.';
        $password = PasswordSecurityAgent::hash($user->getPassword());
        $user->setPassword($password);
        $user->setPhone(trim(chunk_split($user->getPhone(),2,' ')));
        $this->userManager->save($user);
        $this->session->add('userSuccessMsg',$msg);
        Util::saveActivity($user->getEmail(), 'user', 'signup');
        $this->redirect('/login');
      } else {
        $this->display('AcmeUserBundleUser:signup.html.twig', array('form'=> $form->buildView()));
      }
    } else {
      $this->display('AcmeUserBundleUser:signup.html.twig', array('form'=> $form->buildView()));
    }
  }
}
