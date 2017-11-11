<?php

namespace BaftFeedback\Model;

use baft\std\enum\enumAbstract;

class submissionStatesEnum extends enumAbstract {

	// flow : CREATED ----------> CLOSED
	//				|				 |
	//				 --> EXPIRED --->

	const CREATED = 0;
	const EXPIRED = 2;
	const CLOSED = 4;


}