<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Entity\Contributor;

class InviteContributorType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('email', 'email', array('label' => 'form.label.email', 'max_length' => 255));
		$builder->add('role', 'choice', array(
				'choices'  => array(
						Contributor::ROLE_JOURNALIST => 'form.label.journalist',
						Contributor::ROLE_ADMIN => 'form.label.admin'),
		));
		$builder->add('customMessage', 'textarea', array('label' => 'form.label.resume', 'max_length' => 160));
		$builder->add('invite', 'submit', array('label' => 'label.inviteContributor'));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Entity\ContributorInvitation'
		));
	}

	public function getName()
	{
		return 'contributorinvitation';
	}
}