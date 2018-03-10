<?php

namespace BaftFeedback\Event;

use Zend\EventManager\Event;
use BaftFeedback\Entity\BaftfeedbackFeedbackSubmissionState;
use Zend\Stdlib\Parameters;
use Zend\Form\Form;
use Zend\Stdlib\SplStack;
use BaftFeedback\Exception\BaftFeedbackExceptionInterface;

class FeedbackEvent extends Event {

	/**
	 * after loading feedback information (findig feedback , read information , create objects and load submission data "if requested" )
	 *
	 * @var unknown
	 */
	const EVENT_READ_FEEDBACK_PRE = 'read_feedback.pre';
	const EVENT_READ_FEEDBACK = 'read_feedback';

	const EVENT_CREATE_FEEDBACK_PRE = 'create_feedback.pre';
	const EVENT_CREATE_FEEDBACK = 'create_feedback';

	const EVENT_UPDATE_FEEDBACK_PRE = 'update_feedback.pre';
	const EVENT_UPDATE_FEEDBACK = 'update_feedback';

	const EVENT_READ_SUBMISSION_PRE = 'read_submission.pre';
	const EVENT_READ_SUBMISSION = 'read_submission';

	/**
	 * before submission create (inclusive before state create) .
	 * at this point feedbackEvent contain formData(sent by client if be valid) and other basic feeback information
	 * after a submission created and save data and save other information like submitter and subject and state set (inclusive each time edit submission data)
	 *
	 * @var unknown
	 */
	const EVENT_CREATE_SUBMISSION_PRE = 'create_submission.pre';
	const EVENT_CREATE_SUBMISSION = 'create_submission';

	const EVENT_UPDATE_SUBMISSION_PRE = 'edit_submission.pre';
	const EVENT_UPDATE_SUBMISSION = 'edit_submission';

	/**
	 * before every time submission state change (inclusive when creating state for first time)
	 *
	 * @var unknown
	 */
	const EVENT_CHANGE_STATE = 'change_state';

	/**
	 * stack contain name of called listenrs on eventName
	 *
	 * @var unknown
	 */
	protected $calledListners = [ ];

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedback
	 */
	protected $feedback;

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission
	 */
	protected $submission;

	/**
	 *
	 * @var array
	 */
	protected $submissionData;

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackSubjectData $subject
	 */
	protected $subject;

	/**
	 *
	 * @var array
	 */
	protected $subjectData;

	/**
	 *
	 * @var array $submitter
	 */
	protected $submitter;

	/**
	 *
	 * @var array $state
	 */
	protected $state;

	/**
	 *
	 * @var \Zend\Form\Form $feedbackForm
	 */
	protected $feedbackForm;


	/**
	 *
	 * @var string $feedbackNamespace
	 */
	protected $feedbackNamespace;

	/**
	 *
	 * @var string $subjectNamespace
	 */
	protected $subjectNamespace;

	/**
	 *
	 * @var \BaftFeedback\Entity\BaftfeedbackFeedbackVersion $feedbackVersion
	 */
	protected $feedbackVersion;

	/**
	 *
	 * @var string
	 */
	protected $periodId = '';

	/**
	 * save current time event to sync all listener to a single current time when they need
	 *
	 * @var int
	 */
	protected $currentTime;

	/**
	 * detect event initialized ?
	 * useful when passing eventObject to another flow
	 * @var string
	 */
	private $initialized=false;

	/**
	 * predefined porperies
	 *
	 * @var array
	 */
	private $parameters = [
			'exceptions',
			//subject data posted by http
			'form_subject_data',
			//form data posted by http
			'feedback_form_raw_data',
			'feedback_form_extracted_data',
			'submitter_data',
			// creating feedback form data
			'feedback_data',
			'submission_start_time',
			'submission_expire_time',
			//list of previous submissions in feedback subject period
			'prev_submissions',
			'period_id',

	];

	public function __construct() {

		$this->setParams ( new Parameters () );
		$this->resetEvent ();

	}


	public function resetEvent() {

		$this->feedback = null;
		$this->feedbackForm = null;
		$this->feedbackNamespace = null;
		$this->feedbackVersion = null;

		$this->submission = null;
		$this->submissionData = [ ];

		$this->subject = null;
		$this->subjectNamespace = null;
		$this->subjectData = [ ];

		$this->submitter = null;

		$this->state = null;

		$this->calledListners = null;

		$this->setCurrentTime ( time () );

		foreach ( $this->parameters as $parameter ) {
			parent::setParam ( $parameter, null );
		}

		$this->initialized=false;

		return $this;

	}

	public function getEvents() {

		$rfltClass = new \ReflectionClass ( __CLASS__ );
		return $rfltClass->getConstants ();

	}

	/**
	 * init event props base of feedback entity / submission
	 * feedbackEvent can not initialize twice witout reseting event
	 *
	 * @param unknown $feedback
	 */
	public function init($serviceLocator, \BaftFeedback\Entity\BaftfeedbackFeedback $feedback=null, \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $submission = null ) {

	    if($this->initialized)
	        return $this;

		/**
		 *
		 * @var \BaftFeedback\Service\feedbackService $feedbcakService
		 */
		$feedbcakService = $serviceLocator->get ( 'BaftFeedback\Service\feedback' );

		/**
		 * @var \BaftFeedback\Service\submissionService $submissionService
		 */
		$submissionService = $serviceLocator->get ( 'BaftFeedback\Service\submission' );

		$this->resetEvent ();

		if ( isset ( $feedback ))
			$this->setFeedback ( $feedback );

		if ( isset ( $submission )){
			$this->setFeedback ( $submission->getRefBaftfeedbackFeedback());

			$this->setSubmission( $submission );

			$this->setFeedbackVersion($submission->getRefBaftfeedbackFeedbackVersion());

			$this->setSubjectData ( $this->getSubmission ()->getSubjectData () );

			$this->setSubmissionData($submissionService->getSubmissionLastData($this->getSubmission()));

			$this->setSubmitter($this->getSubmission()->getSubmitters());

			$this->setState($this->getSubmission()->getStates());

			$feedbackForm = $feedbcakService->getQuestionsForm($this->getFeedback(),$this->getSubmission ());
			$feedbackForm->bind( $feedbackForm->getObject ());
			$this->setFeedbackForm( $feedbackForm  );

		}

		// not set $feedback but set $submission -> find feedback base of submission
		if (! isset ( $feedback ) ){
			if(!isset($submission))
				throw new \Exception("feedback event initialize : event can not initial without 'feedback' and 'submission' , at least one of both have to be define");
			$this->setFeedback ( $submission->getRefBaftfeedbackFeedback () );
		}

		$this->setSubject($this->getFeedback()->getSubject());
		$this->setSubjectNamespace($this->getFeedback()->getSubjectNamespace());
		$this->setQuestionsNamespace ( $this->getFeedback()->getQuestionsNamespace() );



		$periodId=$feedbcakService->getCurrentPeriodNumber($this->getFeedback());
		$this->setPeriodNumber($periodId);

        $this->initialized=true;

		return $this;

	}

	public function isInitialized(){
	    return $this->initialized;
	}

	public function getParam($name, $default = null) {

		if (in_array ( $name, $this->parameters ) === false)
			throw new \Exception ( __CLASS__ . " : parameter '$name' dose not exist , allowed parameters are '" . implode ( ",", $this->parameters ) . "' ." );
		return parent::getParam ( $name, $default );

	}

	public function setParam($name, $value) {

		if (in_array ( $name, $this->parameters ) === false)
			throw new \Exception ( __CLASS__ . " : parameter '$name' dose not exist , allowed parameters are '" . implode ( ",", $this->parameters ) . "' ." );
		parent::setParam ( $name, $value );

		return $this;

	}

	/**
	 *
	 * @return array
	 */
	public function getExceptions() {

		return $this->getParam ( 'exceptions', [ ] );

	}

	/**
	 * add exception to exceptions list
	 *
	 * @param
	 *        	string | \Exception $exception
	 */
	public function addException($exception) {

		$exceptions = $this->getParam ( 'exceptions', [ ] );

		$previousException = (($previousException = end ( $exceptions )) !== false) ? $previousException : null;

		if (is_string ( $exception )) {
			$exception = new \Exception ( $exception, null, $previousException );
		}

		if ($exception instanceof BaftFeedbackExceptionInterface)
			$exception->setPrevious ( $previousException );

		$exceptions [] = $exception;
		$this->setParam ( 'exceptions', $exceptions );
		return $this;

	}

	/**
	 * check to see exist exception in event
	 */
	public function hasException() {

		$exceptions = $this->getExceptions ();
		if (! empty ( $exceptions ))
			return true;
		return false;

	}

	/**
	 * stack of listenrs name that called for this event Name
	 * then we can use names in trigger callback or elsewhere to do somthing/controll
	 *
	 * @return \Zend\Stdlib\SplStack
	 */
	public function getCalledListeners() {

		return $this->calledListners [$this->getName ()];

	}

	/**
	 * listeners can push their name under eventName when triggered
	 *
	 * @param string $listenerName
	 * @return feedbackEvent
	 */
	public function pushCalledListener($listenerName) {

		if (! isset ( $this->calledListners [$this->getName ()] ))
			$this->calledListners [$this->getName ()] = new SplStack ();

		$this->calledListners [$this->getName ()]->push ( $listenerName );
		return $this;

	}

	/**
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedback
	 */
	public function getFeedback() {

		return $this->feedback;

	}

	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedback $feedback
	 */
	public function setFeedback($feedback) {

		$this->feedback = $feedback;
		return $this;

	}


	/**
	 *
	 * @return \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission
	 */
	public function getSubmission() {

		return $this->submission;

	}

	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $submission
	 */
	public function setSubmission(\BaftFeedback\Entity\BaftfeedbackFeedbackSubmission $submission) {

		$this->submission = $submission;
		return $this;

	}

	/**
	 *
	 * @return BaftfeedbackFeedbackSubjectData
	 */
	public function getSubject() {

		return $this->subject;

	}

	/**
	 *
	 * @param
	 *        	$subject
	 */
	public function setSubject($subject) {

		$this->subject = $subject;
		return $this;

	}

	/**
	 *
	 * @return BaftfeedbackFeedbackSubmitterData
	 */
	public function getSubmitter() {

		return $this->submitter;

	}

	/**
	 *
	 * @param
	 *        	$submitter
	 */
	public function setSubmitter($submitter) {

		$this->submitter = $submitter;
		return $this;

	}

	/**
	 *
	 * @return BaftfeedbackFeedbackSubmissionState
	 */
	public function getState() {

		return $this->state;

	}

	/**
	 *
	 * @param
	 *        	$state
	 */
	public function setState($state) {

		$this->state = $state;
		return $this;

	}

	/**
	 *
	 * @return array
	 */
	public function getSubmissionData() {

		return $this->submissionData;

	}

	/**
	 *
	 * @param array $submissionData
	 */
	public function setSubmissionData($submissionData) {

		$this->submissionData = $submissionData;
		return $this;

	}

	public function getFeedbackForm() {

		return $this->feedbackForm;

	}

	public function setFeedbackForm(Form $formObject) {

		$this->feedbackForm = $formObject;
		return $this;

	}

	/**
	 *
	 * @return string
	 */
	public function getQuestionsNamespace() {

		if (! isset ( $this->questionsNamespace ) && isset ( $this->feedback ))
			$this->setQuestionsNamespace ( $this->getFeedback ()->getQuestionsNamespace () );

		return $this->questionsNamespace;

	}

	/**
	 *
	 * @return string
	 */
	public function setQuestionsNamespace($questionsNamespace) {

		$this->questionsNamespace = $questionsNamespace;
		return $this;

	}

	/**
	 *
	 * @return string
	 */
	public function getFeedbackNamespace() {

		if (! isset ( $this->feedbackNamespace ) && isset ( $this->feedback ))
			$this->setFeedbackNamespace ( $this->getFeedback ()->getNamespace () );

		return $this->feedbackNamespace;

	}

	/**
	 *
	 * @param string $namespace
	 * @return \BaftFeedback\Event\FeedbackEvent
	 */
	public function setFeedbackNamespace($namespace) {

		$this->feedbackNamespace = $namespace;
		return $this;

	}

	/**
	 * get namespce of subject.
	 * ususally used in form naming
	 *
	 * @return string
	 */
	public function getSubjectNamespace() {

		if (! isset ( $this->subjectNamespace ) && isset ( $this->feedback ))
			$this->setSubjectNamespace ( $this->getFeedback ()->getSubjectNamespace () );

		return $this->subjectNamespace;

	}

	/**
	 * save subject namespce
	 *
	 * @param string $subjectNamespace
	 * @return \BaftFeedback\Event\FeedbackEvent
	 */
	public function setSubjectNamespace($subjectNamespace) {

		$this->subjectNamespace = $subjectNamespace;
		return $this;

	}

	/**
	 *
	 * @return the $subjectData
	 */
	public function getSubjectData() {

		return $this->subjectData;

	}

	/**
	 *
	 * @param multitype: $subjectData
	 */
	public function setSubjectData($subjectData) {

		$this->subjectData = $subjectData;

	}


	/**
	 *
	 * @return the $feedbackVersion
	 */
	public function getFeedbackVersion() {

		return $this->feedbackVersion;

	}

	/**
	 *
	 * @param \BaftFeedback\Entity\BaftfeedbackFeedbackVersion $feedbackVersion
	 */
	public function setFeedbackVersion($feedbackVersion) {

		$this->feedbackVersion = $feedbackVersion;

	}

	/**
	 *
	 * @return the $periodId
	 */
	public function getPeriodNumber() {

		return $this->periodId;

	}


	/**
	 *
	 * @param string $periodId
	 */
	public function setPeriodNumber($periodId) {

		$this->periodId = $periodId;

	}

	/**
	 *
	 * @return the $currentTime
	 */
	public function getCurrentTime() {

		return $this->currentTime;

	}


	/**
	 *
	 * @param number $currentTime
	 */
	public function setCurrentTime($currentTime) {

		$this->currentTime = $currentTime;

	}


}