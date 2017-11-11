<?php

namespace BaftFeedback\Listener;

/**
 *
 * @author web
 *        
 */
trait ListenerTrait {
	protected static $prefixes = [ ];
	protected $methods = [ ];
	
	// call foreach prefix after __call find matching method and before call methodPrefix
	protected $beforeCallClosures = [ ];

	/**
	 * - pass parameters item by refrence and set corresponding arguments in method by refrence to be passed by refrence successfully
	 * - add three parameter to the end of parameter list : string methodPrefix , string methodPostfix , string closureName=null
	 * - before call methodName , call registerd callback in $beforeCallClosure corresponding to methodName
	 */
	public function __call($methodName, $parameters) {

		if (! is_array ( $parameters ))
			$parameters = [ 
					$parameters 
			];
		
		if ($method = $this->isCallable ( $methodName ))
			return call_user_func_array ( $method, $parameters );
		
		foreach ( self::$prefixes as $prefix => $prefixMethodName ) {
			$matchResult = false;
			
			if (! preg_match ( "/^(?P<methodPrefix>({$prefix}))(?P<methodPostfix>([a-zA-Z0-9_]+))/", $methodName, $matchResult ))
				continue;
				
				// prefix dose not exist in string
			if (! isset ( $matchResult ['methodPrefix'] ))
				continue;
			
			$methodPrefix = $matchResult ['methodPrefix'];
			$methodPostfix = $matchResult ['methodPostfix'];
			
			// it is better to read these by ... operator (php >=5.6)
			$parameters [] = $methodPrefix;
			$parameters [] = $methodPostfix;
			
			$method = $this->getMethod ( $prefixMethodName );
			
			$this->checkMethodArguments ( $method, $parameters );
			
			if (isset ( $this->beforeCallClosures [$methodPrefix] )) {
				$beforeCall = [ ];
				if (! is_array ( $this->beforeCallClosures [$methodPrefix] ))
					$beforeCall [] = $this->beforeCallClosures [$methodPrefix];
				
				foreach ( $beforeCall as $closure ) {
					if (! is_callable ( $closure ))
						throw new \Exception ( __CLASS__ . " : before Call methods have to be callable " );
					call_user_func ( $closure, $parameters );
				}
			}
			
			return call_user_func_array ( $method, $parameters );
		}
		
		throw new \BadMethodCallException ( "Undefined method '$methodName'. The method name must start with either " . implode ( ' or ', self::$prefixes ) );
	
	}

	/**
	 * add closures to class as methodPrefix
	 * methods added using this way override method member class
	 * can not add beforeCallClosure for method member class
	 *
	 * @param string $methodName        	
	 * @param \Closure|callable|array $methodCallable        	
	 * @return \BaftFeedback\Listener\ListenerTrait
	 */
	public function addMethod($methodName, $methodCallable) {

		$methodCallable = $this->toCallable ( $methodCallable );
		
		$this->methods [$methodName] = $methodCallable;
		
		return $this;
	
	}

	/**
	 * bind a method to a prefix , to responsible for all calls like [prefix][customeName]() methods
	 *
	 * @param string $prefixName        	
	 * @param string $methodName
	 *        	have to defined previously for class
	 * @param \Closure|callable|array $beforeCallClosure        	
	 * @throws \Exception
	 */
	public function addMethodPrefix($prefixName, $methodName, $beforeCallClosure = null) {

		if (! is_string ( $methodName ))
			throw new \Exception ( __METHOD__ . " required method name (second parameter) to be string ", 1 );
		
		if (! $this->isCallable ( $methodName ))
			throw new \Exception ( __METHOD__ . " provided method name '{$methodName}' dose not property of class " . __CLASS__ . " . ", 1 );
		
		self::$prefixes [$prefixName] = $methodName;
		
		if (isset ( $beforeCallClosure )) {
			
			if (! is_array ( $beforeCallClosure ))
				$beforeCallClosure = [ 
						$beforeCallClosure 
				];
			
			foreach ( $beforeCallClosure as $clousure ) {
				$beforeCallClosure = $this->toCallable ( $clousure );
				$this->beforeCallClosures [$prefixName] [] = $beforeCallClosure;
			}
		}
		
		return $this;
	
	}

	/**
	 * return callable method
	 *
	 * @param string $methodName        	
	 * @throws \Exception
	 * @return boolean|array|\Closure
	 */
	public function getMethod($methodName) {

		if ($methodName = $this->isCallable ( $methodName ))
			return $methodName;
		else
			throw new \Exception ( __METHOD__ . " : requsted method '{$methodName}' dose not exists.", 1 );
	
	}

	/**
	 * check if methodName is an existans method/callable object in this class then return callable Object else false
	 * if prefixName passed ,return callable object bound to it
	 *
	 * @param
	 *        	string | array $methodName
	 * @return boolean|array|\Closure
	 */
	protected function isCallable($methodName) {

		if (is_string ( $methodName ) && isset ( $this->methods [$methodName] ))
			return $this->methods [$methodName];
		
		if (is_string ( $methodName ) && isset ( self::$prefixes [$methodName] ) && isset ( $this->methods [self::$prefixes [$methodName]] ))
			return $this->methods [self::$prefixes [$methodName]];
		
		if (is_string ( $methodName ) && method_exists ( $this, $methodName ))
			return [ 
					$this,
					$methodName 
			];
		
		if (is_array ( $methodName ) && is_callable ( $methodName ))
			return $methodName;
		
		return false;
	
	}

	/**
	 * make a callable object
	 *
	 * @param
	 *        	\Clousure | callable $callable
	 * @throws \Exception
	 */
	protected function toCallable($callable) {

		if ($callable instanceof \Closure)
			$callable = $callable->bindTo ( $this, get_class ( $this ) );
		else
			$callable = \Closure::bind ( $callable, $this, get_class ( $this ) );
		
		if (! is_callable ( $callable ))
			throw new \Exception ( __METHOD__ . ' : dose not callable method' );
		
		return $callable;
	
	}

	protected function checkMethodArguments($methodName, $parameters) {
		
		// @TODO check arguments of method with passed parameters
		return true;
		if (empty ( $parameters )) {
			throw \Exception::findByRequiresParameter ( $method . $by );
		}
	
	}


}

?>