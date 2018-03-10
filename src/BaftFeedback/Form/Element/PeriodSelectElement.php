<?php

namespace BaftFeedback\Form\Element;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Element\Select;

/**
 * period of feedback
 * 
 * @author web
 *        
 */
class PeriodSelectElement extends Select implements ServiceLocatorAwareInterface {
	
	// TODO: form elements that get their data from db (eg. selectBox filled by names from db)
	// contain dates base of feedback period
	// theming and add/remove fields by usecase on presentation/controller
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	public function __construct() {

		parent::__construct ( 'period_date' );
		
		$this->setLabel ( 'انتخاب دوره :' );
		
		$this->setAttributes ( [ 
				'id' => 'period_date_list',
				'class' => 'font-blue-ebonyclay input-large  margin-bottom' 
		] );
		$this->setOptions ( [ 
				'empty_option' => 'انتخاب دوره' 
		] );
	
	}

	/**
	 * calculate period dates base of period string
	 * period time is ISO_8601 : https://en.wikipedia.org/wiki/ISO_8601#Durations
	 * 
	 * @param string $period        	
	 * @param DateTime $startTimeValley
	 *        	start of valley , default is start of year
	 * @param int|DateTime $endTimeValley
	 *        	end of valley to calculate period base of valley , default is number of recurrences from startTime
	 */
	public function setPeriod($period, \DateTime $startTimeValley, $endTimeValley = 1) {

		if ($endTimeValley instanceof \DateTime)
			$timeSpanCeil = $endTimeValley->getTimestamp ();
		
		$interval = new \DateInterval ( $period );
		$datePeriod = new \DatePeriod ( $startTimeValley, $interval, $endTimeValley );
		

		$dateRange = [ ];
		foreach ( $datePeriod as $date ) {
			$dateRange [] = $date->getTimestamp ();
		}
		
		// var_dump($dateRange );
		
		$valueOptions = [ ];
		$date = current ( $dateRange );
		while ( $date !== false ) {
			
			$timeSpanFloor = $date;
			
			// set Ceil of time Span
			($timeSpanCeil = next ( $dateRange ) and ! ($timeSpanCeil >= $endTimeValley->getTimestamp ())) || $timeSpanCeil = $endTimeValley->getTimestamp ();
			
			// var_dump("($timeSpanFloor - $timeSpanCeil)");
			
			$valueOptions ["{$timeSpanFloor}-{$timeSpanCeil}"] = date ( 'Y-m-d', $timeSpanFloor );
			$date = current ( $dateRange );
		}
		
		$this->setValueOptions ( $valueOptions );
	
	}

	/**
	 * Set the service locator.
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return AbstractHelper
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {

		$this->serviceLocator = $serviceLocator;
		return $this;
	
	}

	/**
	 * Get the service locator.
	 *
	 * @return ServiceLocatorInterface
	 */
	public function getServiceLocator() {

		return $this->serviceLocator;
	
	}


}