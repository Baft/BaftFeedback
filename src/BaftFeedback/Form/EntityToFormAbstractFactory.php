<?php

namespace BaftFeedback\Form;

use Zend\ServiceManager\AbstractFactoryInterface;

class EntityToFormAbstractFactory implements AbstractFactoryInterface {
	private $entityObject;

	/*
	 * (non-PHPdoc)
	 * @see \Zend\ServiceManager\AbstractFactoryInterface::canCreateServiceWithName()
	 */
	public function canCreateServiceWithName(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator, $name, $requestedName) {

		if (! class_exists ( $requestedName ))
			return false;
		
		$this->entityObject = new $requestedName ();
		if (! $this->entityObject instanceof \BaftFeedback\Entity\BaftFeedbackEntityInterface)
			return false;
		
		return true;
	
	}

	/*
	 * (non-PHPdoc)
	 * @see \Zend\ServiceManager\AbstractFactoryInterface::createServiceWithName()
	 */
	public function createServiceWithName(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator, $name, $checkListName) {

		$formBuilder = new \DoctrineORMModule\Form\Annotation\AnnotationBuilder ( $serviceLocator->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' ) );
		// $formBuilder=new \Zend\Form\Annotation\AnnotationBuilder();
		$form = $formBuilder->createForm ( $this->entityObject );
		
		return $form;
	
	}


}
