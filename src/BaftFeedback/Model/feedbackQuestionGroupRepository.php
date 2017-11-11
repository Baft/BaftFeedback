<?php

namespace BaftFeedback\Model;

use BaftFeedback\Entity\BaftfeedbackQuestionGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayObject;
use baft\std\flattenTree\flattenTree;
use Doctrine\ORM\EntityRepository;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Stdlib\Hydrator\ObjectProperty;

class feedbackQuestionGroupRepository extends EntityRepository {

	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {

		$this->serviceLocator = $serviceLocator;

	}

	public function find($group) {

		if (is_numeric ( $group ))
			$group = $this->getEntityManager ()->find ( 'BaftFeedback\Entity\BaftfeedbackQuestionGroup', $group );

			// if not found submission
		if (! ($group instanceof BaftfeedbackQuestionGroup)) {
			return null;
		}

		return $group;

	}

	/**
	 *
	 * @param
	 *        	array | \BaftFeedback\Entity\BaftfeedbackQuestionGroup | \BaftFeedback\Entity\BaftfeedbackFeedback $data
	 * @param
	 *        	\BaftFeedback\Entity\BaftfeedbackQuestionGroup | \BaftFeedback\Entity\BaftfeedbackFeedback $parent
	 * @throws \Exception
	 */
	public function create($data, $parent = null) {

		$em = $this->getEntityManager ();
		$groupEntity = null;

		if (is_array ( $data )) {

			if (! isset ( $data ['name'] ))
				throw new \Exception ( __METHOD__ . ' : group name dose not set' );

			$data ['label'] = (isset ( $data ['label'] )) ? $data ['label'] : '';
			$data ['parent'] = (isset ( $data ['parent'] )) ? $data ['parent'] : '0';
			$data ['order'] = (isset ( $data ['order'] )) ? $data ['order'] : '0';
			$data ['children'] = (isset ( $data ['children'] )) ? $data ['children'] : '0';

			$hydrator = new ObjectProperty ();
			$groupEntity = $hydrator->hydrate ( $data, new BaftfeedbackQuestionGroup () );
		}

		// make question group base of feedbackEntity , used when creating feedback
		if ($data instanceof \BaftFeedback\Entity\BaftfeedbackFeedback) {
			$groupEntity = new BaftfeedbackQuestionGroup ();
			$groupEntity->setName ( $data->getNamespace () );
			$groupEntity->setLabel ( $data->getLabel () );
			$groupEntity->setOrder ( 0 );
		}

		if ($data instanceof \BaftFeedback\Entity\BaftfeedbackQuestionGroup)
			$groupEntity = $data;

			// else cases for $entity argument
		if (! $groupEntity instanceof \BaftFeedback\Entity\BaftfeedbackQuestionGroup)
			throw new \Exception ( __METHOD__ . ' : first parameter have to be array or instance of \BaftFeedback\Entity\BaftfeedbackQuestionGroup or \BaftFeedback\Entity\BaftfeedbackFeedback ' );

		if (isset ( $parent )) {

			if ($parent instanceof \BaftFeedback\Entity\BaftfeedbackFeedback)
				$parent = $parent->getRefBaftfeedbackQuestionGroupId ();

			if (! $parent instanceof \BaftFeedback\Entity\BaftfeedbackQuestionGroup)
				throw new \Exception ( __METHOD__ . ' : second parameter have to be instance of \BaftFeedback\Entity\BaftfeedbackQuestionGroup or \BaftFeedback\Entity\BaftfeedbackFeedback' );

			$groupEntity->setRefGroupParent ( $parent );
		}

		// var_dump($groupEntity);
		try {
			$em->persist ( $groupEntity );
			$em->flush ( $groupEntity );
		}
		catch ( \Exception $ex ) {
			return null;
		}

		return $groupEntity;

	}

	/**
	 * list question of group ($level=0) and child group till $level>0
	 *
	 * @param int|BaftfeedbackQuestionGroup $group
	 * @param int $level
	 */
	public function getQuestions($group, $level = 0) {

		$group = $this->find ( $group );

		$questions = [ ];

		// @TODO currently read one level . need to read all level
		if ($level == 0)
			return $questions [$group->getId ()] = $group->getQuestions ();

		$groups = $this->getChilds ( $group, $level );
		foreach ( $groups as $childGroup ) {
			$questions [$childGroup->getId ()] = $childGroup->getQuestions ();
		}

		return $questions;

	}

	/**
	 * list of child group till level .
	 * till level=500
	 *
	 * @param int|BaftfeedbackQuestionGroup $group
	 * @param int $level
	 */
	public function getChilds($group, $level = 500) {

		// @TODO currently read one level . need to read all level
		$group = $this->find ( $group );
		$childs = $group->getChildren ()->toArray ();
		return $childs;

		// ---------DISABLED
		$level --;
		$flattenTree = new flattenTree ();
		// convert child groups to form
		if ($level > 0 && $children->count () > 0) {
			// iterate over groups
			foreach ( $childs as $childGroup ) {
				$node = $flattenTree::nodeFactory ( $childGroup->getName (), $childGroup );
				$flattenTree->addNode ( $node );

				$children = $this->getChilds ( $childGroup, $level );

				foreach ( $children as $c )
					$children->offsetSet ( $childGroup->getId (), $childGroup->getChildren () );
				if ($children->count () > 0)
					$this->getChilds ( $group );
			}
		}

		return $flattenTree;

	}

	/**
	 * check group $child exist in parent , if parent not specified try find on all (like find method)
	 * false on not found and group Instance on exist
	 *
	 * @param int|BaftfeedbackQuestionGroup $child
	 * @param int|BaftfeedbackQuestionGroup $parent
	 * @return int|BaftfeedbackQuestionGroup
	 */
	public function isExist($child, $parent = false) {

		if ($parent === false)
			return $this->find ( $child );

		$child = $this->find ( $child );
		$parent = $this->find ( $parent );
		$childs = $this->getChilds ( $parent, 0 );
		foreach ( $childs as $childInstance ) {
			if ($childInstance->getId () == $child->getId ())
				return $child;
		}

		return false;

	}


}