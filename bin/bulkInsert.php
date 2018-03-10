
<!--[if IEMobile 7]><html class="no-js iem7 oldie"><![endif]-->
<!--[if (IE 7)&!(IEMobile)]><html class="no-js ie7 oldie" lang="en"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]><html class="no-js ie8 oldie" lang="en"><![endif]-->
<!--[if (IE 9)&!(IEMobile)]><html class="no-js ie9" lang="en"><![endif]-->
<!--[if (gt IE 9)|(gt IEMobile 7)]><!-->
<html class="no-js" lang="en">
<!--<![endif]-->

<head>
<meta charset="utf-8">
</head>
<body>
bulk question insert:

<?php
ini_set('display_errors', 1);
$mysqli = new mysqli("localhost", "root", "root", "insp_v2");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}
mysqli_set_charset ( $mysqli , "utf8" );


$dataFile=fopen(dirname(__FILE__).'/bulkdata.txt','r');

$feedbackParentGroup=124;
//number to start counting group, to make unique name
$groupNumberStart=34;
//number to start counting question , to make unique name
$questionNumberStart=34;

$fileLine=0;
$counter=0;
$groupNum=$groupNumberStart;
$questionNum=$questionNumberStart;
while($txtLine=trim(fgets($dataFile))){

	$fileLine++;
	$groupId=false;

	if(strpos($txtLine, "#") === 0 ){

		$groupLabel=trim($txtLine,":#");

		$groupInsert= "
			INSERT INTO `insp_v2`.`baftfeedback_question_group`
			(`name` , `label`, `order`, `ref_group_parent`)
			VALUES
			('G{$groupNum}' , '{$groupLabel}', '-1', '{$feedbackParentGroup}');";
		print $groupInsert."<br/>";
		mysqli_query($mysqli,$groupInsert);
		$groupId=mysqli_insert_id($mysqli);
		++$groupNum;

	}

	if(empty($groupId)){
		die("reading line {$fileLine} .  empty group id ");
	}


	if(empty($txtLine))
		continue;


	$insertQuestion  = "
		INSERT INTO `insp_v2`.`baftfeedback_question`
		(`label`, `name`, `ref_baftfeedback_question_structure_id`, `disable`)
		VALUES
			('{$$txtLine}',
			'Q{$questionNum}',
			'1',
			'0');
	";
	print $insertQuestion."<br/>";
	mysqli_query($mysqli,$insertQuestion);
	$questionId=mysqli_insert_id($mysqli);
	$questionNum++;


	$insertToGroup  = "
		INSERT INTO `insp_v2`.`baftfeedback_question_group_questions`
		(`ref_baftfeedback_question_group_id`, `ref_baftfeedback_question_id`, `question_order`, `score`, `disable`)
		VALUES
		('{$groupId}', '{$questionId}', '-1', '0', '0');
	";
	print $insertToGroup."<br/>";
	mysqli_query($mysqli,$insertToGroup);

}


/* close connection */
$mysqli->close();

?>
</body>
</html>