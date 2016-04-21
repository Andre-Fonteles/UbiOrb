<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RecoverPasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('email', 'email', array('label' => 'form.label.email', 'max_length' => 255));
		$builder->add('recover', 'submit', array('label' => 'form.label.getNewPassword'));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Entity\User',
				'validation_groups' => array('recover-password')
		));
	}

	
	
	public function getName()
	{
		return 'recoverpassword';
	}
}