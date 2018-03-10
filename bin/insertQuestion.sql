
set @gid:=25;
set @questionLabel:='';
set @qtype:=1;
set @qorder:=-1;

-- ----------------------------------------------

-- insert question and set relation to a group

set @qname:='';
select @qname:=concat('Q',max( if(left(`name`,1)="Q",cast( substring(`name` FROM 2)+1 as unsigned ) , 0 ) ) )as qname from insp_v2.baftfeedback_question;

-- save question with new name (incrimental name)
INSERT INTO
`baftfeedback_question` (`label`, `name`, `ref_baftfeedback_question_structure_id`)
VALUES
(
@questionLabel
, @qname
, @qtype );

set @qid:=last_insert_id();

-- set last saved question under a specific group
INSERT INTO
`baftfeedback_question_group_questions`
(`ref_baftfeedback_question_group_id`, `ref_baftfeedback_question_id`, `question_order`)
VALUES
(@gid,@qid, @qorder);