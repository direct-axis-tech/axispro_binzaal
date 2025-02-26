CREATE TABLE 0_metadata (
	`key` VARCHAR(50) NOT NULL PRIMARY KEY,
	`val` VARCHAR(100) NOT NULL,
	updated_at TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP 
);

INSERT INTO 0_metadata (`key`, `val`) VALUES ('attendace_generated_till_id', '0');

ALTER TABLE `0_empl_punchinouts` ADD INDEX (authdate), ADD INDEX (authtime);

ALTER TABLE `0_kv_empl_info` ADD UNIQUE (emp_code);