<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\Container;

class EmailManager
{
	protected $container;
	
	public function __construct(Container $container)
	{
		$this->container = $container;
	}
	
	private function createMail() 
	{
		$mail = new \PHPMailer();
		$mail->CharSet = 'UTF-8';
		$mail->IsSMTP();
		$mail->Host = $this->container->getParameter('mailer_host');
		$mail->SetFrom($this->container->getParameter('mailer_user'), $this->container->getParameter('basehost'));

// 		$mail->SMTPAuth   = true;
// 		$mail->SMTPSecure = "tls";
// 		$mail->Host       = "smtp.gmail.com";
// 		$mail->Port       = 587;
// 		$mail->Username   = "andrefonteles@gmail.com";
// 		$mail->Password   = "xxx";
		
		$mail->addCustomHeader('X-Sender', $this->container->getParameter('mailer_user'));
		
		return $mail;
	}
	
	/**
	 * Send an email asking the user to access a link to confirm his email.
	 */
	public function sendEmailConfirmation($user, $confLink)
	{
		$mail = $this->createMail();
		$translator = $this->container->get('translator');
		$template = $this->container->get('templating');
		
		$mail->Subject = $translator->trans('confirmation.email.subject');
		$mail->AltBody = $template->render('emails/confirmation.txt.twig', array('user' => $user, 'link' => $confLink));
		$mail->MsgHTML($template->render('emails/confirmation.html.twig', array('user' => $user, 'link' => $confLink)));
		$mail->AddAddress($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());
		
		return $mail->Send();
	}
	
	/**
	 * Send an email inviting a user to contribute with a newspaper.
	 */
	public function sendContributorInvitation($contributeInvitation) 
	{
		$translator = $this->container->get('translator');
		$template = $this->container->get('templating');
			
		$fullName = $contributeInvitation->getInviter()->getFirstName() . ' ' .
				$contributeInvitation->getInviter()->getLastName();
		
		$mail = $this->createMail();
		$mail->Subject = $translator->trans('contributorInivitation.email.subject', array('%name%' => $fullName));
		$mail->AltBody = $template->render('emails/contribute-invitation.txt.twig', array('contributeInvitation' => $contributeInvitation));
		$mail->MsgHTML($template->render('emails/contribute-invitation.html.twig', array('contributeInvitation' => $contributeInvitation)));
		$mail->AddAddress($contributeInvitation->getEmail(), '');
		
		return $mail->Send();
	}
	
	/**
	 * Send an email containing a link to reset user's password.
	 */
	public function sendResetPassMail($user, $confLink)
	{
 		$translator = $this->container->get('translator');
 		$template = $this->container->get('templating');
			
		$mail = $this->createMail();
		$mail->Subject = $translator->trans('resetPassword.mail.subject');
		$mail->AltBody = $template->render( 'emails/reset-password.txt.twig', array('user' => $user, 'link' => $confLink));
		$mail->MsgHTML($template->render( 'emails/reset-password.html.twig', array('user' => $user, 'link' => $confLink)));
		$mail->AddAddress($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());
		
		return $mail->Send();
	}
}
