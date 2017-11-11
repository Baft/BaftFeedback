
USE `insp_v2_feedbackv2`;
-- ---------------------------------------------------------------------------------

ALTER TABLE  `baftfeedback_feedback`
ADD COLUMN `ref_subject_fieldset` VARCHAR(255) NULL AFTER `ref_baftfeedback_question_group_id` ,
ADD COLUMN `continuous` INT(1) NULL DEFAULT 0 AFTER `active`;

UPDATE  `baftfeedback_feedback` SET `continuous`='1' WHERE `id`='2' and`name`='arzi_feedback';

UPDATE  `baftfeedback_feedback` SET `ref_subject_fieldset`='4' WHERE `id`='2' and`name`='arzi_feedback';
UPDATE  `baftfeedback_feedback` SET `ref_subject_fieldset`='3' WHERE `id`='3' and`name`='omoomi_feedback';


ALTER TABLE  `baftfeedback_feedback`
DROP COLUMN `period_time`,
CHANGE COLUMN `continuous` `continuous` INT(1) NULL DEFAULT '0' AFTER `repeat`,
ADD COLUMN `interval_time` VARCHAR(255) NULL AFTER `duration_time`,
ADD COLUMN `interval_reverse_time` VARCHAR(255) NULL AFTER `interval_time`,
ADD COLUMN `subject_ref_form` VARCHAR(255) NULL AFTER `active`,
ADD COLUMN `subject_label` TEXT NULL AFTER `subject_ref_form`,
ADD COLUMN `subject_name` VARCHAR(255) NULL AFTER `subject_label`,
ADD COLUMN `subject_json_setting` TEXT NULL AFTER `subject_name`,
ADD COLUMN `password` VARCHAR(45) NULL AFTER `subject_json_setting`,
ADD COLUMN `paginate` INT(1) NULL AFTER `password`,
ADD COLUMN `group_break_page` INT(1) NULL AFTER `paginate`,
ADD COLUMN `question_page` INT(4) NULL AFTER `group_break_page`,
ADD COLUMN `shuffle_question` INT(1) NULL AFTER `question_page`,
ADD COLUMN `json_feedback_config` TEXT NULL AFTER `shuffle_question`,
ADD COLUMN `submitter_limit` INT(10) NULL AFTER `json_feedback_config`;

ALTER TABLE  `baftfeedback_feedback`
CHANGE COLUMN `disable` `deleted` INT(1) NULL DEFAULT '0' COMMENT 'hidden in view , unavailable in business' ;

ALTER TABLE `baftfeedback_feedback`
ADD COLUMN `simultaneous` INT(10) NULL ,
ADD COLUMN `submission_limit` INT(10) NULL ;

ALTER TABLE `baftfeedback_feedback`
ADD COLUMN `editable` INT(1) NULL DEFAULT 1 AFTER `desc`;

UPDATE  `baftfeedback_feedback` SET `editable`='1' ;


-- ---------------------------------------------------------------------------------

ALTER TABLE  `baftfeedback_feedback_subject`
CHANGE COLUMN `name` `name` VARCHAR(255) NULL DEFAULT NULL AFTER `id`,
CHANGE COLUMN `label` `label` TEXT NULL DEFAULT NULL AFTER `name`,
CHANGE COLUMN `ref_baftfeedback_feedback_id` `ref_baftfeedback_feedback_id` INT(10) UNSIGNED NOT NULL AFTER `label`,
ADD COLUMN `ref_fieldset` VARCHAR(255) NULL AFTER `ref_baftfeedback_question_id`,
ADD COLUMN `subject_order` INT NULL AFTER `question_order`,
ADD COLUMN `json_subject_config` TEXT NULL AFTER `subject_order`,
ADD COLUMN `json_fieldset_config` TEXT NULL AFTER `json_subject_config`;


UPDATE  `baftfeedback_feedback_subject` SET `name`='brcode', `label`='انتخاب شعبه', `ref_fieldset`='feedbackExtendBranch', `json_subject_config`='{}', `json_fieldset_config`='{}' WHERE `id`='3';
UPDATE  `baftfeedback_feedback_subject` SET `name`='brcode', `label`='انتخاب شعبه', `ref_fieldset`='feedbackExtendBranch', `json_subject_config`='{}', `json_fieldset_config`='{}' WHERE `id`='4';

ALTER TABLE  `baftfeedback_feedback_subject`
DROP FOREIGN KEY `fk_baftfeedback_feedback_subject_baftfeedback_question1`;
ALTER TABLE  `baftfeedback_feedback_subject`
DROP COLUMN `question_order`,
DROP COLUMN `ref_baftfeedback_question_id`,
DROP INDEX `fk_baftfeedback_feedback_subject_baftfeedback_question1_idx`;

ALTER TABLE  `baftfeedback_feedback_subject`
DROP FOREIGN KEY `fk_baftfeedback_feedback_subject_baftfeedback_feedback1`;
ALTER TABLE  `baftfeedback_feedback_subject`
DROP COLUMN `ref_baftfeedback_feedback_id`,
DROP INDEX `fk_baftfeedback_feedback_subject_baftfeedback_feedback1_idx` ;


-- ---------------------------------------------------------------------------------


ALTER TABLE  `baftfeedback_feedback_subject_data`
ADD COLUMN `ref_baftfeedback_subject_id` INT NULL AFTER `ref_baftfeedback_question_id`,
ADD COLUMN `field_name` VARCHAR(255) NULL AFTER `question_field_name`,
ADD COLUMN `subject_data_identifier` INT NOT NULL AFTER `value`;


update baftfeedback_feedback_subject_data set field_name=question_field_name , ref_baftfeedback_subject_id=4;
update baftfeedback_feedback_subject_data set field_name='brcode/brcode';

update baftfeedback_feedback_subject_data as subject_data
INNER JOIN
(SELECT id,ref_baftfeedback_submission_id FROM baftfeedback_feedback_subject_data GROUP BY ref_baftfeedback_submission_id) subject_group_data
ON subject_group_data.ref_baftfeedback_submission_id  = subject_data.ref_baftfeedback_submission_id
set subject_data.subject_data_identifier=subject_group_data.id;


ALTER TABLE  `baftfeedback_feedback_subject_data`
DROP FOREIGN KEY `fk_baftfeedback_feedback_subject_data_baftfeedback_question1`,
DROP INDEX `fk_baftfeedback_feedback_subject_data_baftfeedback_question_idx` ,
DROP COLUMN `question_field_name`,
DROP COLUMN `ref_baftfeedback_question_id`;

-- ---------------------------------------------------------------------------------

ALTER TABLE  `baftfeedback_feedback_submission`
ADD COLUMN `duration_time` VARCHAR(255) NULL AFTER `start_time`,
ADD COLUMN `submitter_limit` VARCHAR(255) NULL AFTER `duration_time`,
ADD COLUMN `continuous` INT(1) NULL AFTER `submitter_limit`,
CHANGE COLUMN `ref_baftfeedback_feedback_version_id` `ref_baftfeedback_feedback_version_id` INT(11) NOT NULL  ,
ADD INDEX `idx_feedback_version` (`ref_baftfeedback_feedback_version_id` ASC);

ALTER TABLE  `baftfeedback_feedback_submission`
ADD CONSTRAINT `fk_feedback_version`
  FOREIGN KEY (`ref_baftfeedback_feedback_version_id`)
  REFERENCES  `baftfeedback_feedback_version` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `baftfeedback_feedback_submission`
ADD COLUMN `editable` INT(1) NULL DEFAULT 1 ;

UPDATE  `baftfeedback_feedback_submission` SET `editable`='1' ;
UPDATE  `baftfeedback_feedback_submission` SET `continuous`='1' ;


-- ---------------------------------------------------------------------------------

ALTER TABLE  `baftfeedback_question`
ADD COLUMN `ref_fieldset` VARCHAR(255) NULL AFTER `active`,
ADD COLUMN `json_fieldset_config` TEXT NULL AFTER `ref_fieldset`,
ADD COLUMN `json_question_config` TEXT NULL AFTER `json_fieldset_config`;


UPDATE  `baftfeedback_question` SET `ref_fieldset`='feedbackExtendThreeRadio', `json_fieldset_config`='{\"attributes\":{\"class\":\"question\"}}', `json_question_config`='{}';
UPDATE  `baftfeedback_question` SET `json_fieldset_config`='{}' ;

ALTER TABLE  `baftfeedback_question`
CHANGE COLUMN `name` `name` VARCHAR(255) NOT NULL ,
ADD UNIQUE INDEX `name_UNIQUE` (`name` ASC),
DROP FOREIGN KEY `fk_baftfeedback_question_baftfeedback_question_structure1`;

ALTER TABLE  `baftfeedback_question`
DROP INDEX `fk_baftfeedback_question_baftfeedback_question_structure1_idx`,
DROP COLUMN `ref_baftfeedback_question_structure_id`,
ADD COLUMN `deleted` INT(1) NULL DEFAULT 0 AFTER `name`;


UPDATE  `baftfeedback_question` SET `label`='question with 3 radio and one textarea' WHERE `id`='1';


-- ---------------------------------------------------------------------------------

ALTER TABLE  `baftfeedback_question_group_questions`
CHANGE COLUMN `disable` `disable` INT(1) UNSIGNED NULL DEFAULT '0' ,
ADD COLUMN `label` TEXT NULL AFTER `disable`,
ADD COLUMN `deleted` INT(1) NULL AFTER `label`,
ADD COLUMN `required` INT(1) NULL AFTER `deleted`,
ADD COLUMN `json_question_config` TEXT NULL AFTER `required`,
ADD COLUMN `json_fieldset_config` TEXT NULL AFTER `json_question_config`;

update
baftfeedback_question_group_questions gq , baftfeedback_question question
set gq.label=question.label
where
gq.ref_baftfeedback_question_id=question.id;

-- ---------------------------------------------------------------------------------

ALTER TABLE  `baftfeedback_feedback_submission_data`
DROP FOREIGN KEY `fk_baftfeedback_feedback_data_1`;


update
baftfeedback_feedback_submission_data sdata  ,
    baftfeedback_question_group_questions gq
set sdata.ref_baftfeedback_question_id=gq.id
where
    sdata.ref_baftfeedback_question_id = gq.ref_baftfeedback_question_id;

ALTER TABLE  `baftfeedback_feedback_submission_data`
ADD CONSTRAINT `fk_baftfeedback_feedback_submission_data_1`
  FOREIGN KEY (`ref_baftfeedback_question_id`)
  REFERENCES  `baftfeedback_question_group_questions` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

update baftfeedback_question_group_questions gq set gq.ref_baftfeedback_question_id=1;

-- ---------------------------------------------------------------------------------

ALTER TABLE  `baftfeedback_question_group`
ADD COLUMN `deleted` INT(1) NULL DEFAULT 0 COMMENT 'hidden in view and unavailable in bussines' AFTER `ref_group_parent`,
ADD COLUMN `active` INT(1) NULL DEFAULT 1 COMMENT 'just hidden in view but available in business' AFTER `deleted` ,
CHANGE COLUMN `ref_group_parent` `ref_group_parent` INT(10) UNSIGNED NULL DEFAULT '0' ;


-- ---------------------------------------------------------------------------------


delete from  `baftfeedback_question` where `id`<>'1';

update baftfeedback_feedback_submission
set expire_time=unix_timestamp('2018-03-19')
where submission_period='P1Y_1_960101';
