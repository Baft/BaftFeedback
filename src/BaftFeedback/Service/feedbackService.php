<?php

namespace BaftFeedback\Service;

use BaftFeedback\Entity\BaftfeedbackFeedback;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Form\Fieldset;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Zend\Form\Form;
use Bundle\jdf;
use BaftFeedback\Event\FeedbackEvent;
use Zend\Form\Factory;
use Zend\EventManager\EventManager;
use Zend\Json\Json;
use BaftFeedback\Exception\FeedbackNotFoundException;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubject;
use BaftFeedback\Form\SubjectForm;
use BaftFeedback\Form\QuestionsForm;
use BaftFeedback\Entity\BaftfeedbackFeedbackGroups;
use BaftFeedback\Entity\BaftfeedbackQuestionGroup;
use BaftFeedback\Entity\BaftfeedbackQuestionGroupQuestions;

class feedbackService implements ServiceLocatorAwareInterface, EventManagerAwareInterface {
	public $serviceLocator;
	protected $eventManager;
	protected $proxyAttributes = [ ];

	/**
	 * lazy load some models
	 *
	 * @param unknown $attrName
	 * @throws \Exception
	 * @return Ambigous <>
	 */
	public function __get($attrName) {

		$refKey = null;

		if (strcasecmp ( $attrName, 'feedbackModel' ) == 0) {
			$model = 'BaftFeedback\Model\feedback';
			$refKey = $attrName;
		}

		if (strcasecmp ( $attrName, 'questionModel' ) == 0) {
			$model = 'BaftFeedback\Model\feedback';
			$refKey = $attrName;
		}

		if (strcasecmp ( $attrName, 'feedbackVersionModel' ) == 0) {
			$model = 'BaftFeedback\Model\feedbackVersion';
			$refKey = $attrName;
		}

		if (is_null ( $refKey ))
			throw new \Exception ( "property '{$attrName}' dose not defined for '" . __CLASS__ . "'" );

		if (! isset ( $this->proxyAttributes [$refKey] ))
			$this->proxyAttributes [$refKey] = $this->getServiceLocator ()->get ( $model );

		return $this->proxyAttributes [$refKey];

	}

	/**
	 * return feedbackevent on exception occured
	 *
	 * @param
	 *        	BaftfeedbackFeedback | FeedbackEvent $feedbackData
	 */
	public function createFeedback($feedbackData) {

		$feedbackEvent = new FeedbackEvent ();
		$feedbackEvent->setTarget ( $this );
		$feedbackEvent->setParam ( 'feedback_data', $feedbackData );

		$this->getEventManager ()->trigger ( FeedbackEvent::EVENT_CREATE_FEEDBACK_PRE, $feedbackEvent );

		if ($feedbackEvent->hasException ())
			return $feedbackEvent;

		$this->getEventManager ()->trigger ( FeedbackEvent::EVENT_CREATE_FEEDBACK, $feedbackEvent );

		return $feedbackEvent->getFeedback ();

	}

	/**
	 * return feedbackevent on exception occured
	 *
	 * @param
	 *        	BaftfeedbackFeedback | FeedbackEvent $feedbackData
	 */
	public function editFeedback($feedback, $feedbackData) {

		$feedbackEvent = new FeedbackEvent ();
		$feedbackEvent->setTarget ( $this );
		$feedbackEvent->setParam ( 'feedback_data', $feedbackData );
		$feedbackEvent->setFeedback ( $feedback );

		$this->getEventManager ()->trigger ( FeedbackEvent::EVENT_UPDATE_FEEDBACK_PRE, $feedbackEvent );

		if ($feedbackEvent->hasException ())
			return $feedbackEvent;

		$this->getEventManager ()->trigger ( FeedbackEvent::EVENT_UPDATE_FEEDBACK, $feedbackEvent );

		return $feedbackEvent->getFeedback ();

	}

	/**
	 * find and load feedback then build form object
	 *
	 * @todo read feedback base of version number
	 *
	 * @param
	 *        	string | int | BaftfeedbackFeedback $feedback
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackVersion $version
	 * @throws \Exception
	 * @return boolean|\BaftFeedback\Entity\BaftfeedbackFeedback
	 */
	public function readFeedback($feedback, $version = null) {

		$feedbackEntity = null;
		// to be sure feedback entity exist
		if (is_string ( $feedback ))
			$feedbackEntity = $this->feedbackModel->findOneBy ( [
					'name' => $feedback
			] );

		if (is_numeric ( $feedback ))
			$feedbackEntity = $this->feedbackModel->find ( $feedback );

		if (! $feedbackEntity instanceof BaftfeedbackFeedback) {
			throw new FeedbackNotFoundException ( __METHOD__ . " : requestd feedback dose not exist ." );
			return false;
		}

		if ($version instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackVersion)
			$feedbackEntity->setVersion ( $version );

		if (! $feedbackEntity->getVersion () instanceof \BaftFeedback\Entity\BaftfeedbackFeedbackVersion)
			$feedbackEntity->setVersion ( $this->getLastVersion ( $feedbackEntity ) );

		$feedbackEvent = new FeedbackEvent ();
		$feedbackEvent->setTarget ( $this );
		$feedbackEvent->setFeedback ( $feedbackEntity );

		$this->getEventManager ()->trigger ( FeedbackEvent::EVENT_READ_FEEDBACK, $feedbackEvent );

		return $feedbackEvent->getFeedback ();

	}

	// #################################################################
	// #################################################################
	// #################################################################

	/**
	 * check if feedback has any period or interval
	 * @param  \BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity
	 * @return bool|string
	 */
	public function hasInterval(\BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity){
		if(!empty($feedbackEntity->getIntervalTime()))
				return $feedbackEntity->getIntervalTime();
		return false;

	}

	/**
	 * is currently declined respite time
	 * RESPITE	 : return 0 = it is in respite span
	 * EXPIRED   : return 1 = declined expire time
	 * PREMATURE : return 2 = it dose not reach to respite span
	 * @param array $respiteTime
	 * @param int $now current time or specific time in future or past to check with repsite time
	 * @return int
	 */
	public function isExpiredRespiteTime($respiteTime,$now=null){

		if($respiteTime===false)
			return 1;

		if($now==null)
			$now=time();

		$availableTime=$respiteTime['available_time'];
		$expireTime=$respiteTime['expire_time'];

		// in span (RESPITE)
		if($availableTime <= $now && $now < $expireTime)
			return 0;

		// greater than span (EXPIRED)
		if($expireTime < $now)
			return 1;

		//less than span (PREMATURE)
		if($availableTime > $now)
			return 2;

		return 1;
	}

	/**
	 * بازه ی زمانیی برای فرم که باید زمان همه ی سابمیشن(ثبت فرم)ها در بازه ی ان قرار داشته باشد که به عنوان مهلت از ان یاد می شود
	 * get currentRespiteTime (default).
	 * get list of respiteTimeS on $currentRespiteTime=false .
	 * get false if can not calculate new respiteTime :
	 * 		1-set fixed respite
	 * 		2-error occured
	 * 		3-no any more respiteTime exists(fixed expireTime or end of calculation)
	 * interval_id = start of interval time span
	 *
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedback $feedbackEntity
	 * @param boolean $currentRespiteTime
	 * @return array|false [available_time,expire_time,interval_id]
	 */
	public function getRespiteTime($feedbackEntity,$currentRespiteTime=true){

		$packRespite=function($start,$end,$priodId){
			return [
					"available_time" => $start ,
					"expire_time" => $end ,
					"interval_id" => $priodId
					];
		};

		$feedbackEntity = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' )->find ( $feedbackEntity );

		if (! $feedbackEntity instanceof \BaftFeedback\Entity\BaftfeedbackFeedback)
			throw new \Exception ( "method '" . __METHOD__ . "' expect parameter one to be instance of BaftfeedbackFeedback , instance of '" . gettype ( $feedbackEntity ) . "' is passed" );

		$currentTime = time ();
		$availableTime = $feedbackEntity->getAvailableTime ();
		$expireTime = $feedbackEntity->getExpireTime ();
		$periodTime = $feedbackEntity->getIntervalTime ();

		$jdf = new jdf ();
		$currentJYear = $jdf->jgetdate($currentTime)['year'];//gregorian_to_jalali ( $currentYear, 01, 01 ) [0];

		// start of "farvardin" to gregorian
		if($availableTime==0){
			$availableTime = $jdf->jalali_to_gregorian ( $currentJYear, 1, 1 );
			// to timestamp
			$availableTime = mktime ( 0, 0, 0, $availableTime [1], $availableTime [2], $availableTime [0] );
		}

		// enf of "esfand" to gregorian
		if($expireTime==0){
			$expireTime = $jdf->jalali_to_gregorian ( $currentJYear, 12, $jdf->monthDayNumber ( 12, $currentJYear ) );
			// to timestamp
			$expireTime = mktime ( 0, 0, 0, $expireTime [1], $expireTime [2], $expireTime [0] );
		}

		if ( empty ( $periodTime ) )
			return $packRespite( $availableTime , $expireTime , "fix_{$availableTime}");

		// period is dose not valid
		if (!is_string ( $periodTime ))
			return false;

		// period is set?
		$interval = new \DateInterval ( $periodTime );
		$datePeriod = new \DatePeriod ( new \DateTime ( '@' . $availableTime ), $interval, new \DateTime ( '@' . $expireTime ) );

		$periodArray = iterator_to_array($datePeriod);
		var_dump($periodArray,$periodTime,date("Y m d",$availableTime),date("Y m d",$expireTime));

		$periodesRange=[];
		foreach ( $periodArray as $num => $date ) {
			$periodStart = $date->getTimestamp ();

			if((!$nextPeriod=each($periodArray)['value']) || (false && prev($periodArray)) )
				$nextPeriod=$datePeriod->end;

			$periodEnd=$nextPeriod->getTimeStamp();

			$periodesRange [] = $packRespite($periodStart , $periodEnd-1 ,"{$periodTime}_{$num}_{$periodStart}");

		}
		//return list of priodes range
		if(!$currentRespiteTime)
			return $periodesRange;

		//find current respiteTime on flag $currentRespiteTime=true
		foreach ($periodesRange as $period ) {
			if($period['available_time'] <= $currentTime &&  $currentTime < $period['expire_time']  )
				return $period;
		}

		return false;

	}

	/**
	 * calculate submission respite time (startTime,endTime) base of current (use case in creating new submission).
	 *
	 * 	1-if "availableTime" , "expireTime" , "durationTime" set fixed
	 * 		1- priod is set : ref to 2-2 & 2-1.
	 * 		2- duratin time is set : checklist available till duration in span of [availableTime , expireTime].
	 *  2-"priodic" & "repeat" are arguments to generate dynamic "availableTime" & "expireTime" & "durationTime".
	 *  	1- To generate base of "priodTime" (1-with availableTime) : generate (time spans) base of priodTime starting from availableTime.
	 *  	2- To generate base of "priodTime" (1-with availableTime & expirtime) : generate (time spans) , calculating time spans just in span of [availableTime , expireTime] .
	 *  	3- To generate base of "priodTime" (2-no availableTime) : generate (time spans) base of priodTime starting from start of each year .
	 *  	4- (depricated: can be defined using priodTime only)To generate base of "repeat" : priodTime have to defined , generate (time spans) base of calculating priodTime/repeatTime.
	 * period time is ISO_8601 : https://en.wikipedia.org/wiki/ISO_8601#Durations
	 *
	 * @param
	 *        	\BaftFeedback\Entity\BaftfeedbackFeedback | int $feedbackEntity
	 * @return int
	 */
	public function _calculateRespiteTime($feedbackEntity) {

		$feedbackEntity = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' )->find ( $feedbackEntity );

		if (! $feedbackEntity instanceof \BaftFeedback\Entity\BaftfeedbackFeedback)
			throw new \Exception ( "method '" . __METHOD__ . "' expect parameter one to be instance of BaftfeedbackFeedback , instance of '" . gettype ( $feedbackEntity ) . "' is passed" );


		$startTimeSpan=0;
		$endTimeSpan=0;

		$availableTime = $feedbackEntity->getAvailableTime ();
		$expireTime = $feedbackEntity->getExpireTime ();
		$periodTime = $feedbackEntity->getIntervalTime ();

		$jdf = new jdf ();
		$currentTime = time ();
		$currentYear = date ( 'Y' );
		$currentJYear = $jdf->gregorian_to_jalali ( $currentYear, 01, 01 ) [0];

		// start of "farvardin" to gregorian
		$startTimeSpan = $jdf->jalali_to_gregorian ( $currentJYear, 1, 1 );
		// to timestamp
		$startTimeSpan = mktime ( 0, 0, 0, $startTimeSpan [1], $startTimeSpan [2], $startTimeSpan [0] );

		// enf of "esfand" to gregorian
		$endTimeSpan = $jdf->jalali_to_gregorian ( $currentJYear, 12, $jdf->monthDayNumber ( 12, $currentJYear ) );
		// to timestamp
		$endTimeSpan = mktime ( 0, 0, 0, $endTimeSpan [1], $endTimeSpan [2], $endTimeSpan [0] );

		// if availableTime is set
		if (! empty ( $availableTime ) && $availableTime > 0) {
			$startTimeSpan = $availableTime;
		}

		// if expireTime is set
		if (! empty ( $expireTime ) && $expireTime > 0) {
			$endTimeSpan = $expireTime;
		}

		// period is set?
		if (! empty ( $periodTime ) && is_string ( $periodTime )) {

			$interval = new \DateInterval ( $periodTime );
			$datePeriod = new \DatePeriod ( new \DateTime ( '@' . $startTimeSpan ), $interval, new \DateTime ( '@' . $endTimeSpan ) );
			$timeSpanCeil = $endTimeSpan;

			$dateRange = [ ];
			foreach ( $datePeriod as $date ) {
				$dateRange [] = $date->getTimestamp ();
			}

			while ( $date = current ( $dateRange ) ) {

				$timeSpanFloor = $date;

				// set Ceil of time Span
				($timeSpanCeil = next ( $dateRange ) and ! ($timeSpanCeil >= $endTimeSpan)) || $timeSpanCeil = $endTimeSpan;

				if ($currentTime < $timeSpanCeil && $currentTime >= $timeSpanFloor) {
					$respiteTime = $timeSpanCeil;
					break;
				}
			}
		}

		return [
				'start' => $startTimeSpan,
				'end' => $endTimeSpan
		];

	}

	/**
	 * return form object with form bind object
	 * and fill form with submission data
	 *
	 * @param unknown $feedback
	 * @param array $formData
	 *        	submission data to populate in form
	 * @return array
	 */
	public function getFeedbackForm($feedbackEntity, $submissionData = [], $subjectData = []) {

		$subjectFieldset = $this->getSubjectForm ( $feedbackEntity )->getFieldset ();
		$feedbackFieldset = $this->getQuestionsFieldset ( $feedbackEntity, $submissionData );

		$feedbackNamespace = $feedbackEntity->getNamespace ();
		$feedbackFormName = $feedbackFieldset->getName ();
		$subjectFormName = $subjectFieldset->getName ();

		$formObject = new Form ( $feedbackNamespace );
		$formObject->setAttribute ( 'method', 'POST' )->setBindOnValidate ( Form::BIND_ON_VALIDATE )->getFormFactory ()->setFormElementManager ( $this->getServiceLocator ()->get ( 'FormElementManager' ) );

		// glue subject & feedback together in one form
		$baseBindObject = new \stdClass ();
		$baseBindObject->{$subjectFormName} = $subjectFieldset->getObject ();
		$baseBindObject->{$feedbackFormName} = $feedbackFieldset->getObject ();

		// ######################### wrap all form element in baseFieldSet #####################
		// $baseFieldset = new Fieldset($feedbackNamespace);
		// $baseFieldset->setAttribute('class', $feedbackNamespace."_basefieldset");
		// $baseFieldset->setUseAsBaseFieldset(true);
		// $baseFieldset->setHydrator(new ObjectProperty());
		// $baseFieldset->setObject($baseBindObject);
		// $baseFieldset->add($subjectFieldset);
		// $baseFieldset->add($feedbackFieldset);
		// $formObject->add($baseFieldset);
		// $formObject->add(array(
		// 'name' => 'submit',
		// 'attributes' => array(
		// 'type' => 'submit',
		// 'value' => 'Send'
		// )
		// ));
		// $formObject->populateValues([
		// $feedbackNamespace => [
		// $subjectFieldset->getName() => $subjectData,
		// $feedbackFieldset->getName() => $submissionData
		// ]
		// ]);
		// $formObject->bind( $formObject->get($feedbackNamespace)->getObject() );
		// return $formObject;
		// ####################################################################################

		$formObject->setHydrator ( new ObjectProperty () );
		$formObject->setObject ( $baseBindObject );
		$formObject->add ( $subjectFieldset );
		$formObject->add ( $feedbackFieldset );

		$formObject->add ( array (
				'name' => 'submit',
				'attributes' => array (
						'type' => 'submit',
						'value' => 'Send'
				)
		) );

		$formObject->populateValues ( [
				$subjectFieldset->getName () => $subjectData,
				$feedbackFieldset->getName () => $submissionData
		] );

		$formObject->bind ( $formObject->getObject () );

		return $formObject;

	}

	public function getSubjectForm($feedbackEntity, $subjectData = []) {

		if (! $subjectEntity = $feedbackEntity->getSubject ())
			return new Form ();

		$feedbackNamespace = $feedbackEntity->getNamespace ();
		$subjectNamespace = $feedbackEntity->getSubjectNamespace ();

		$formObject = new SubjectForm ( $feedbackNamespace );
		$formObject->setServiceLocator ( $this->getServiceLocator () );
		$formObject->setFeedback ( $feedbackEntity );
		$formObject->init ();

		$formObject->populateValues ( [
				$subjectNamespace => $subjectData
		] );

		return $formObject;

	}

	public function getQuestionsForm($feedbackEntity, $submissionEntity, $submissionData = []) {

		$feedbackNamespace = $feedbackEntity->getNamespace ();

		$formObject = new QuestionsForm ( $feedbackNamespace );
		$formObject->setServiceLocator ( $this->getServiceLocator () );
		$formObject->setFeedback ( $feedbackEntity );
		$formObject->setSubmission ( $submissionEntity );
		$formObject->init ();

		if (! empty ( $submissionData ))
			$formObject->populateValues ( [
					$feedbackEntity->getQuestionsNamespace () => $submissionData
			] );

		return $formObject;

	}


	/**
	 * get questions of feedback till $level, if $group specified from $group till level of child groups (if specified)
	 *
	 * @param int|BaftfeedbackFeedback $feedback
	 * @param int|BaftfeedbackQuestionGroup $group
	 * @param int $level
	 * @return array
	 */
	public function getQuestions($feedback, $level = 1, $group = false) {

		$questionGroupModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\questionGroup' );

		$feedbackModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' );
		/**
		 *
		 * @var \BaftFeedback\Entity\BaftfeedbackFeedback $feedback
		 */
		$feedback = $feedbackModel->find ( $feedback );

		$questions = [ ];

		$parentGroup = $feedback->getRefBaftfeedbackQuestionGroupId ();
		if ($group !== false && $questionGroupModel->isExist ( $group, $parentGroup ))
			$parentGroup = $group;

		$questions = $questionGroupModel->getQuestions ( $parentGroup, $level );

		return $questions;

	}

	/**
	 * get question object that sit in $number in $group on $feedback
	 *
	 * @param int|feedbackEntity $feedback
	 * @param int $group
	 * @param int $questionNubmer
	 */
	public function getQuestion($feedback, $group, $questionNubmer) {
		// @TODO implement getQuestion body
	}

	/**
	 * list form groups of $feedback till $level
	 * if $group specified list child group of $group till $level
	 *
	 * @param unknown $feedback
	 * @param string $group
	 * @param number $level
	 */
	public function getQuestionGroups($feedback, $level = 1, $group = false) {

		/**
		 *
		 * @var \BaftFeedback\Model\feedbackQuestionGroupRepository $questionGroupModel
		 */
		$questionGroupModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\questionGroup' );
		$feedbackModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' );
		/**
		 *
		 * @var \BaftFeedback\Entity\BaftfeedbackFeedback $feedback
		 */
		$feedback = $feedbackModel->find ( $feedback );

		if ($group !== false && $questionGroupModel->isExist ( $group, $parentGroup ))
			$parentGroup = $group;
		else
			$parentGroup = $feedback->getRefBaftfeedbackQuestionGroupId ();

			// @TODO do it in model to do recursivley
			// $groups=$questionGroupModel->getChilds($parentGroup,$level);
		$groups = $parentGroup->getChildren ();

		return $groups;

	}


	/**
	 * check group exist in feedback
	 *
	 * @param unknown $feedback
	 * @param unknown $group
	 */
	public function hasGroup($feedback, $group) {

		$questionGroupModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\questionGroup' );
		$feedbackModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedback' );
		/**
		 *
		 * @var \BaftFeedback\Entity\BaftfeedbackFeedback $feedback
		 */
		$feedback = $feedbackModel->find ( $feedback );

		$parentGroup = $feedback->getRefBaftfeedbackQuestionGroupId ();
		if ($group !== false && $questionGroupModel->isExist ( $group, $parentGroup ))
			$parentGroup = $group;

		return $questionGroupModel->isExist ( $group, $parentGroup );

	}

	public function hasQuestion($feedback, $question) {

		return false;

	}

	public function hasSubject($feedback) {

		$subjectModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\subject' );

		return $subjectModel->findByFeedback ( $feedback );

	}

	/**
	 * get feedback subjects , use to create form
	 *
	 * @param unknown $feedbackId
	 * @return array subjectId as key and value is "quesiton" and "order"
	 */
	public function getSubject($feedback) {

		$feedbackEntity = $this->feedbackModel->find ( $feedback );

		$subjectModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\subject' );

		return $subjectModel->findByFeedback ( $feedbackEntity );

	}

	/**
	 * move question across groups and order in group
	 *
	 * @param BaftfeedbackQuestionGroupQuestions $feedbackQuestionEntity
	 * @param BaftfeedbackQuestionGroup $groupEntity
	 * @param int $order
	 */
	public function moveQuestion($feedbackQuestionEntity, $order, $groupEntity = null) {

		$feedbackQuestionModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\feedbackQuestion' );
		$result = true;

		if ($groupEntity && strcasecmp ( $feedbackQuestionEntity->getRefBaftfeedbackQuestionGroup ()->getName (), $groupEntity->getName () ) != 0)
			$feedbackQuestionEntity->setRefBaftfeedbackQuestionGroup ( $groupEntity );

			// simulate Desc because higher number has higher priority in form
		$negativeOrder = $order * - 1;

		if ($feedbackQuestionEntity->getQuestionOrder () != $negativeOrder) {
			$feedbackQuestionEntity->setQuestionOrder ( $negativeOrder );
			// reorder of others . in this case $questins is ordered DESC list base of questionOrder (-1 , -2 , ...)
			$questions = $feedbackQuestionEntity->getRefBaftfeedbackQuestionGroup ()->getQuestions ();
			$replacementOrder = $negativeOrder;
			foreach ( $questions as $question ) {

				if ($question->getId () == $feedbackQuestionEntity->getId ())
					continue;

				if ($question->getQuestionOrder () <= $negativeOrder)
					$question->setQuestionOrder ( -- $replacementOrder );

				$result = $result && $feedbackQuestionModel->update ( $question );
			}

			$result = $result && $feedbackQuestionModel->update ( $feedbackQuestionEntity );
		}

		return $result;

	}

	/**
	 * move question across groups and order in group
	 *
	 * @param BaftfeedbackQuestionGroupQuestions $feedbackQuestionEntity
	 * @param BaftfeedbackQuestionGroup $groupEntity
	 * @param int $order
	 */
	public function moveGroup($groupEntity, $order, $parentEntity = null) {

		$groupModel = $this->getServiceLocator ()->get ( 'BaftFeedback\Model\questionGroup' );

		if ($groupEntity && strcasecmp ( $feedbackQuestionEntity->getRefBaftfeedbackQuestionGroup ()->getName (), $groupEntity->getName () ) != 0)
			$feedbackQuestionEntity->setRefBaftfeedbackQuestionGroup ( $groupEntity );

			// simulate Desc because higher number has higher priority in form
		$negativeOrder = $order * - 1;

		if ($feedbackQuestionEntity->getQuestionOrder () != $negativeOrder) {
			$feedbackQuestionEntity->setQuestionOrder ( $negativeOrder );
			// reorder of others . in this case $questins is ordered DESC list base of questionOrder (-1 , -2 , ...)
			$questions = $feedbackQuestionEntity->getRefBaftfeedbackQuestionGroup ()->getQuestions ();
			$replacementOrder = $negativeOrder;
			foreach ( $questions as $question ) {

				if ($question->getId () == $feedbackQuestionEntity->getId ())
					continue;

				if ($question->getQuestionOrder () <= $negativeOrder)
					$question->setQuestionOrder ( -- $replacementOrder );

				$feedbackQuestionModel->update ( $question );
			}

			$feedbackQuestionModel->update ( $feedbackQuestionEntity );
		}

	}


	/**
	 * count questions of feedback
	 * @TODO make it count question in unlimited level (currently just read till one level from parent group)
	 */
	public function countQuestions($feedbackId) {

		$em = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );
		$connection = $em->getConnection ();

		$questionList = "
		SELECT
			count(baftfeedback_question_group_questions.ref_baftfeedback_question_id) as question_number
		FROM
			baftfeedback_feedback AS feedback
				JOIN
			baftfeedback_question_group
				JOIN
			baftfeedback_question_group_questions

		ON feedback.id = '{$feedbackId}'
		AND feedback.ref_baftfeedback_question_group_id = baftfeedback_question_group.ref_group_parent
		AND baftfeedback_question_group_questions.ref_baftfeedback_question_group_id = baftfeedback_question_group.id
		group by feedback.id;";

		$questionListCount = $connection->query ( $questionList )->fetch () ['question_number'];

		return $questionListCount;

	}


	/**
	 * get row of the latest version of feedback
	 *
	 * @param int $feedbackId
	 * @return array
	 */
	public function getLastVersion($feedbackId) {
		// witout orm
		// $versions=$this->feedbackVersionModel->findByFeedback($feedbackId);
		$version = $this->feedbackVersionModel->findByFeedback ( $feedbackId )->last ();

		return $version;

	}

	/**
	 *
	 * return a time span , base of period in valley of $from-$to
	 * and return exact time span if set number of interval ($at) eg. : 6th of period=P1M ~ 06/01-06/30
	 *
	 * @param string $period
	 *        	period string
	 * @param int $at
	 *        	number of period , eg: 6 omin tekrar e doreh. defaul is all (null passed)
	 * @param int $from
	 *        	unix , calculate period from this time . default is start of year
	 * @param int $to
	 *        	unix , claculate period to this time . default is end of year .
	 */
	public function periodToTimeSpan($period, $at = null, $from = null, $to = null) {
		// @todo implement period to time span
	}


	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Zend\EventManager\EventManagerAwareInterface::setEventManager()
	 */
	public function setEventManager(\Zend\EventManager\EventManagerInterface $eventManager) {

		$eventManager->addIdentifiers ( [
				__CLASS__,
				get_called_class (),
				'BaftFeedback'
		] );
		$eventManager->setEventClass ( '\BaftFeedback\Event\feedbackEvent' );
		$this->eventManager = $eventManager;

		return $this;

	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see \Zend\EventManager\EventsCapableInterface::getEventManager()
	 */
	public function getEventManager() {

		if (! isset ( $this->eventManager )) {
			$this->setEventManager ( new EventManager () );
		}
		return $this->eventManager;

	}


	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {

		$this->serviceLocator = $serviceLocator;

	}

	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {

		return $this->serviceLocator;

	}


}