<?php

// $questionsMap=[];
// $filePointer=fopen(__DIR__.'/questionsMap.csv','r');
// while($line=fgetcsv($filePointer,1024,',','"')){
// 	list($baftGroup,$baftQid,$rialiGroup,$rialiQid)=$line;
// 	$questionsMap[$rialiQid]=[$baftQid,$baftGroup];
// }
// var_dump($test);die();
$questionsMap=array ( 1 => array ( 0 => '158', 1 => '105', ), 2 => array ( 0 => '159', 1 => '105', ), 3 => array ( 0 => '160', 1 => '105', ), 4 => array ( 0 => '161', 1 => '105', ), 5 => array ( 0 => '162', 1 => '105', ), 6 => array ( 0 => '163', 1 => '105', ), 7 => array ( 0 => '164', 1 => '105', ), 8 => array ( 0 => '165', 1 => '105', ), 9 => array ( 0 => '166', 1 => '105', ), 10 => array ( 0 => '167', 1 => '105', ), 11 => array ( 0 => '168', 1 => '105', ), 12 => array ( 0 => '169', 1 => '105', ), 13 => array ( 0 => '170', 1 => '105', ), 14 => array ( 0 => '171', 1 => '105', ), 15 => array ( 0 => '172', 1 => '105', ), 16 => array ( 0 => '173', 1 => '105', ), 17 => array ( 0 => '174', 1 => '105', ), 18 => array ( 0 => '175', 1 => '105', ), 19 => array ( 0 => '176', 1 => '105', ), 20 => array ( 0 => '177', 1 => '105', ), 21 => array ( 0 => '178', 1 => '105', ), 22 => array ( 0 => '179', 1 => '106', ), 23 => array ( 0 => '180', 1 => '106', ), 24 => array ( 0 => '181', 1 => '106', ), 25 => array ( 0 => '182', 1 => '106', ), 26 => array ( 0 => '183', 1 => '106', ), 27 => array ( 0 => '184', 1 => '106', ), 28 => array ( 0 => '185', 1 => '107', ), 29 => array ( 0 => '186', 1 => '107', ), 30 => array ( 0 => '187', 1 => '107', ), 31 => array ( 0 => '188', 1 => '107', ), 32 => array ( 0 => '189', 1 => '107', ), 33 => array ( 0 => '190', 1 => '108', ), 34 => array ( 0 => '191', 1 => '109', ), 35 => array ( 0 => '192', 1 => '109', ), 36 => array ( 0 => '193', 1 => '109', ), 37 => array ( 0 => '194', 1 => '109', ), 38 => array ( 0 => '195', 1 => '110', ), 39 => array ( 0 => '196', 1 => '110', ), 40 => array ( 0 => '197', 1 => '110', ), 41 => array ( 0 => '198', 1 => '111', ), 42 => array ( 0 => '199', 1 => '111', ), 43 => array ( 0 => '200', 1 => '111', ), 44 => array ( 0 => '201', 1 => '111', ), 45 => array ( 0 => '202', 1 => '111', ), 46 => array ( 0 => '203', 1 => '111', ), 47 => array ( 0 => '204', 1 => '111', ), 48 => array ( 0 => '205', 1 => '111', ), 49 => array ( 0 => '206', 1 => '111', ), 50 => array ( 0 => '207', 1 => '111', ), 51 => array ( 0 => '208', 1 => '111', ), 52 => array ( 0 => '209', 1 => '111', ), 53 => array ( 0 => '210', 1 => '111', ), 54 => array ( 0 => '211', 1 => '111', ), 55 => array ( 0 => '212', 1 => '111', ), 56 => array ( 0 => '213', 1 => '111', ), 57 => array ( 0 => '214', 1 => '111', ), 58 => array ( 0 => '215', 1 => '111', ), 59 => array ( 0 => '216', 1 => '111', ), 60 => array ( 0 => '217', 1 => '111', ), 61 => array ( 0 => '218', 1 => '111', ), 62 => array ( 0 => '219', 1 => '111', ), 63 => array ( 0 => '220', 1 => '111', ), 64 => array ( 0 => '221', 1 => '111', ), 65 => array ( 0 => '222', 1 => '111', ), 66 => array ( 0 => '223', 1 => '111', ), 67 => array ( 0 => '224', 1 => '111', ), 68 => array ( 0 => '225', 1 => '111', ), 69 => array ( 0 => '226', 1 => '112', ), 70 => array ( 0 => '227', 1 => '112', ), );

mysql_connect('localhost','root','root');

// mysql_select_db('');

//subjects and submissions >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
$result=mysql_query("
			SELECT
				 ans.brcode ,'system' as first_submitter
			FROM
				bmiconnect2._omoomi_answers as ans join
				bmiconnect2._omoomi_samples as  samp
			on ans.sampleid=samp.sampleId
			group by brcode
			order by brcode ");

while($submission=mysql_fetch_assoc($result)){

	$brcode=$submission['brcode'];
	$firstSubmitter=$submission['first_submitter'];


	//insert into submission >>>>>>>>>>>>>>>>>>>>>>>>>>>
	mysql_query("
			INSERT INTO `zf2_module_development`.`baftfeedback_feedback_submission`
			(`ref_baftfeedback_feedback_id`, `ref_baftfeedback_feedback_version_id`, `expire_time`) VALUES
			('3', '2', '99');
			");
	$submissionId=mysql_insert_id();
	//##################################################


	//@todo set json_state_data with first submitter
	$jsonData=json_encode(['ratifier'=> $firstSubmitter, 'description'=>'']);

	//insert into submission state >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	mysql_query("
			INSERT INTO `zf2_module_development`.`baftfeedback_feedback_submission_state`
			(`state`, `ref_baftfeedback_feedback_submission_id`, `version`, `description`,`json_state_data`) VALUES
			('0', '{$submissionId}', '3', '','{$jsonData}');
			;
			");
	//##################################################



	//insert into subject data >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	mysql_query("
			INSERT INTO `zf2_module_development`.`baftfeedback_feedback_subject_data`
			(`ref_baftfeedback_feedback_id`, `ref_baftfeedback_submission_id`, `ref_baftfeedback_question_id`, `question_field_name`,`value`) VALUES
			('3', '{$submissionId}', '3', 'brcode','{$brcode}');");
	$subjectId=mysql_insert_id();
	//##################################################



	//submission data >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	$answersResult=mysql_query("
			SELECT
				ans.qid , ans.answer , ans.brcode , ans.`date` , ans.psampleid , samp.*
			FROM bmiconnect2._omoomi_answers as ans join bmiconnect2._omoomi_samples as samp
				on ans.sampleid=samp.sampleId
			where brcode='{$brcode}'
			order by brcode ");

	while($answer=mysql_fetch_assoc($answersResult)){

		$queistionId=$questionsMap[$answer["qid"]][0];
		$groupId=$questionsMap[$answer["qid"]][1];

		$value=$answer['answer'];
		$desc=$answer['comment'];

		//insert submission data
		mysql_query("
				INSERT INTO `zf2_module_development`.`baftfeedback_feedback_submission_data`
				(`ref_baftfeedback_feedback_submission_id`, `ref_baftfeedback_feedback_submitter_data_id`, `ref_baftfeedback_question_group_id`, `ref_baftfeedback_question_id`, `question_field_name`, `value`) VALUES
				('{$submissionId}', '{$submitter}', '{$groupId}', '{$queistionId}', 'rdo0', '{$value}');
				");

		if(!empty($desc))
			//insert submission data description
			mysql_query("
					INSERT INTO `zf2_module_development`.`baftfeedback_feedback_submission_data`
					(`ref_baftfeedback_feedback_submission_id`, `ref_baftfeedback_feedback_submitter_data_id`, `ref_baftfeedback_question_group_id`, `ref_baftfeedback_question_id`, `question_field_name`, `value`) VALUES
					('{$submissionId}', '{$submitter}', '{$groupId}', '{$queistionId}', 'desc0', '{$desc}');
					");

	}//##################################################


}//##################################################

	//insert into submitter
	mysql_query("
			INSERT INTO `zf2_module_development`.`baftfeedback_feedback_submitter_data`
			(`submitter`, `submitter_ip`, `submit_time`, `start_time`) VALUES
			('12', '222222', '11122121', '2121212');
			");

