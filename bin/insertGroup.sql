set @feedbackParentGroup:=124;
set @gorder:=-1;
set @groupLabel:='';

-- ----------------------------------------------

select
	@gname:=concat('G',max( if(left(`name`,1)="G",cast( substring(`name` FROM 2)+1 as unsigned ) , 0 ) ) )as gname ,
	from insp_v2.baftfeedback_question_group;



INSERT INTO `insp_v2`.`baftfeedback_question_group`
(`name` , `label`, `order`, `ref_group_parent`)
VALUES
(@gname ,
@groupLabel,
@gorder,
@feedbackParentGroup);
