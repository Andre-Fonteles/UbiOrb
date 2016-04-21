<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\User;
use AppBundle\Form\Type\RecoverPasswordType;
use Symfony\Component\Security\Core\SecurityContextInterface;
use AppBundle\Form\Type\UserType;
use Symfony\Component\Security\Core\Util\SecureRandom;
use AppBundle\Entity\EmailConfCode;
use AppBundle\Entity\ResetPassCode;
use AppBundle\Form\Type\ResetPasswordType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use AppBundle\Form\Type\ChangePasswordType;
use AppBundle\Entity\ChangePassword;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
	
    public function signUpAction()
    {
    	// If the user is already logged in, redirect to index
    	if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
    		return $this->redirect($this->generateUrl('index'));
    	}
    	
        $user = new User();
        $form = $this->createForm(new UserType(), $user, array(
            'action' => $this->generateUrl('create_account'),
        ));

        return $this->render(
            'account/register.html.twig',
            array('form' => $form->createView())
        );
    }
    
    public function createAction(Request $request)
    {
    	$form = $this->createForm(new UserType(), new User());
    	$form->handleRequest($request);
    
    	$em = $this->getDoctrine()->getManager();
    
    	if ($form->isValid()) {
    		
    		$user = $form->getData();
    		$factory = $this->get('security.encoder_factory');
    		$encoder = $factory->getEncoder($user);
    		$user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));
			$user->setActive(true);
			
    		$emailConfirmationCode = new EmailConfCode($user);
    		$em->persist($user);
    		$em->persist($emailConfirmationCode);
    		$em->flush();
    
    		$link= $this->generateUrl('confirm_email', array('code' => $emailConfirmationCode->getCode()), true);
    		
    		$this->get('email_manager')->sendEmailConfirmation($user, $link);
    		
			// Automatically login the user
	    	$token = new UsernamePasswordToken($user, null, "default", $user->getRoles());
	    	$this->get("security.context")->setToken($token);
	    	$event = new InteractiveLoginEvent($request, $token);
	    	$this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
			
    		return $this->render(
    			'account/check-email-confirmation.html.twig',
    			array('user' => $user)
    		);
    	}
    
    	return $this->render(
    			'account/register.html.twig',
    			array('form' => $form->createView())
    	);
    }
    
    public function confirmEmailAction(Request $request, $code)
    {
    	$emailConfirmationCode = $this->getDoctrine()
    	->getRepository('AppBundle:EmailConfCode')
    	->findOneBy(array('code' => $code));

    	$user = $emailConfirmationCode->getUser();
    	
    	// Automatically login the user
    	$token = new UsernamePasswordToken($user, null, "default", $user->getRoles());
    	$this->get("security.context")->setToken($token);
    	$event = new InteractiveLoginEvent($request, $token);
    	$this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
    	
    	$emailConfirmationCode->getUser()->setConfirmedEmail(true);
    	$emailConfirmationCode->getUser()->setActive(true);
    	
    	$em = $this->getDoctrine()->getManager();
    	$em->persist($emailConfirmationCode->getUser());
    	$em->remove($emailConfirmationCode);
    	$em->flush();
    	
    	return $this->render('account/welcome.html.twig');
    }
    
    public function recoverPasswordAction(Request $request)
    {
    	$session = $request->getSession();
    	
    	// last username entered by the user
    	$lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);
    	
    	$user = new User();
    	$user->setEmail($lastUsername);
    	$form = $this->createForm(new RecoverPasswordType(), $user, array(
    			'action' => $this->generateUrl('send_recover_email'),
    	));
    	
    	return $this->render(
    			'account/recover-password.html.twig',
    			array('form' => $form->createView())
    	);
    }
    
    public function sendRecoverEmailAction(Request $request)
    {
    	$form = $this->createForm(new RecoverPasswordType(), new User());
    	$form->handleRequest($request);
    
    	if ($form->isValid()) {
    		
    		$user = $form->getData();
    		
    		// Get user from database
    		$user = $this->getDoctrine()
    		->getRepository('AppBundle:User')
    		->findOneBy(array('email' => $user->getEmail()));
    		
    		$em = $this->getDoctrine()->getManager();
    		
    		// Delete any previous codes for password reset for the user
    		$repository = $em->getRepository('AppBundle:ResetPassCode');
    		$repository->deleteAllByUserId($user);
    		
    		// Create a new ResetPassCode and saves it in the database
    		$resetPasswordCode = new ResetPassCode($user);
    		$em = $this->getDoctrine()->getManager();
    		$em->persist($resetPasswordCode);
    		$em->flush();
    		
    		// Generate link for password reseting
    		$link= $this->generateUrl('reset_password', array('code' => $resetPasswordCode->getCode()), true);
    
    		$this->get('email_manager')->sendResetPassMail($user, $link);
    		
    		return $this->render(
    			'account/check-email-reset.html.twig',
    			array('user' => $user)
    		);
    	}
    
    	return $this->render(
    			'account/recover-password.html.twig',
    			array('form' => $form->createView())
    	);
    }
    
    public function resetPasswordAction(Request $request, $code)
    {
    	$resetPasswordCode = $this->getDoctrine()
    	->getRepository('AppBundle:ResetPassCode')
    	->findOneBy(array('code' => $code));
    	
    	$user = $resetPasswordCode->getUser();
    	
    	// Automatically login the user
    	$token = new UsernamePasswordToken($user, null, "default", $user->getRoles());
    	$this->get("security.context")->setToken($token);
    	$event = new InteractiveLoginEvent($request, $token);
    	$this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
    	
    	// Oportunistically confirms user email in case he has not done this
    	$user->setConfirmedEmail(true);
    	$user->setActive(true);
    	$em = $this->getDoctrine()->getManager();
    	$em->persist($user);
    	$em->flush();
    	
    	$repository = $em->getRepository('AppBundle:EmailConfCode');
    	$repository->deleteAllByUserId($user);
    	
    	$user = new User();
    	$form = $this->createForm(new ResetPasswordType(), $user, array(
    			'action' => $this->generateUrl('update_password'),
    	));
    	
    	return $this->render('account/reset-password.html.twig', 
    			array('form' => $form->createView())
    	);
    }
    
    public function updatePasswordAction(Request $request)
    {
    	// Check if user is logged in and fully authenticated (remember me won't work)
    	if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
    		throw new AccessDeniedException();
    	}
    	
    	$form = $this->createForm(new ResetPasswordType(), new User());
    	$form->handleRequest($request);
    	
    	if ($form->isValid()) {
	    	
	    	$user = $form->getData();
	    	$newPassword = $user->getPassword();
	    	
    		// Encodes new user's password and save it
    		$user = $this->getUser();
    		$factory = $this->get('security.encoder_factory');
    		$encoder = $factory->getEncoder($user);
    		$user->setPassword($encoder->encodePassword($newPassword, $user->getSalt()));
    		$em = $this->getDoctrine()->getManager();
    		$em->persist($user);
    		$em->flush();
    		
    		// Delete all ResetPassCodes for the user
    		$repository = $em->getRepository('AppBundle:ResetPassCode');
    		$repository->deleteAllByUserId($user);
    	
    		//Redict do index
    		return $this->redirect($this->generateUrl('index'));
    	}
    	
    	return $this->render(
    			'account/reset-password.html.twig',
    			array('form' => $form->createView())
    	);
    }
    
    public function changePasswordAction(Request $request)
    {
    	// Check if user is logged in and fully authenticated (remember me won't work)
    	if (!$this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
    		throw new AccessDeniedException();
    	}
    	
    	$changePasswordModel = new ChangePassword();
    	$form = $this->createForm(new ChangePasswordType(), $changePasswordModel);
    	
    	$form->handleRequest($request);
    	
    	if ($form->isSubmitted() && $form->isValid()) {
    		$user = $this->getUser();
    		$factory = $this->get('security.encoder_factory');
    		$encoder = $factory->getEncoder($user);
    		$user->setPassword($encoder->encodePassword($changePasswordModel->getNewPassword(), $user->getSalt()));

    		$em = $this->getDoctrine()->getManager();
    		$em->persist($user);
    		$em->flush();
    		
    		return $this->render('account/change-password-success.html.twig');
    	}
    	
    	return $this->render('account/change-password.html.twig', array(
    			'form' => $form->createView()
    	));
    }
    
	public function loginAction(Request $request)
	{
		$appLocales = explode('|', $this->container->getParameter('app_locales'));
		$preferedLanguage = $this->get('request')->getPreferredLanguage($appLocales);
		
		return $this->redirect($this->generateUrl('login2', array("_locale" => $preferedLanguage)));
	}
	
    public function login2Action(Request $request)
    {
    	$session = $request->getSession();
    
    	// If the user is already logged in, redirect to index
    	if ($this->get('security.context')->isGranted('ROLE_USER')) {
    		return $this->redirect($this->generateUrl('index'));
    	}
    	 
    	 
    	// get the login error if there is one
    	if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
    		$error = $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR);
    	} elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
    		$error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
    		$session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
    	} else {
    		$error = null;
    	}
    	 
    	// last username entered by the user
    	$lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);
    	 
    	$response = new Response();
    	$response->headers->addCacheControlDirective('no-cache', true);
    	$response->headers->addCacheControlDirective('max-age', 0);
    	$response->headers->addCacheControlDirective('must-revalidate', true);
    	$response->headers->addCacheControlDirective('no-store', true);
    	 
    	return $this->render(
    			'security/login.html.twig',
    			array(
    					// last username entered by the user
    					'last_username' => $lastUsername,
    					'error'         => $error,
    			), $response
    	);
    }
    
    public function loginCheckAction()
    {
    	// this controller will not be executed,
    	// as the route is handled by the Security system
    }
}
