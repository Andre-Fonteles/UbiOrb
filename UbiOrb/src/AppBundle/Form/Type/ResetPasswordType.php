<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResetPasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('password', 'repeated', array(
				'first_name'  => 'first',
				'second_name' => 'second',
				'type'        => 'password',
				'first_options'  => array('label' => 'form.label.password', 'max_length' => 255),
				'second_options' => array('label' => 'form.label.repeatPassword', 'max_length' => 255),
				'invalid_message' => 'not.match.password'
		));
		$builder->add('reset', 'submit', array('label' => 'form.label.resetPassword'));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Entity\User',
				'validation_groups' => array('reset-password')
		));
	}

	
	
	public function getName()
	{
		return 'recoverpassword';
	}
}