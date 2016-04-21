<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('firstName', 'text', array('label' => 'form.label.firstName', 'max_length' => 60));
		$builder->add('lastName', 'text', array('label' => 'form.label.lastName', 'max_length' => 60));
		$builder->add('email', 'email', array('label' => 'form.label.email', 'max_length' => 255));
		$builder->add('password', 'repeated', array(
				'first_name'  => 'first',
				'second_name' => 'second',
				'type'        => 'password',
				'first_options'  => array('label' => 'form.label.password', 'max_length' => 255),
				'second_options' => array('label' => 'form.label.repeatPassword', 'max_length' => 255),
				'invalid_message' => 'not.match.password'
		));
		$builder->add('Register', 'submit', array('label' => 'label.signUp'));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Entity\User'
		));
	}

	public function getName()
	{
		return 'user';
	}
}