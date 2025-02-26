CREATE TABLE 0_empl_punchinouts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	empid VARCHAR(25) NULL,
	authdatetime DATETIME NULL,
	authdate DATE NULL,
	authtime TIME NULL,
	devicename VARCHAR(100) NULL,
	deviceserialno VARCHAR(100) NULL,
	person VARCHAR(50) NULL,
	cardno VARCHAR(25) NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	INDEX(authdatetime),
	INDEX(empid)
);

-- DROP TABLE `0_empl_punchinouts`;