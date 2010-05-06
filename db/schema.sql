
CREATE TABLE `binaries` (
		`ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(255) NOT NULL DEFAULT '',
		`fromname` VARCHAR(255) NOT NULL DEFAULT '',
		`date` DATETIME DEFAULT NULL,
		`xref` VARCHAR(255) NOT NULL DEFAULT '',
		`totalParts` INT(11) UNSIGNED NOT NULL DEFAULT '0',
		`groupID` INT(11) UNSIGNED NOT NULL DEFAULT '0',
		procstat INT DEFAULT 0,
		procattempts INT DEFAULT 0,
		filename VARCHAR(255) NULL,
		relpart INT DEFAULT 0,
		reltotalpart INT DEFAULT 0,
		relname VARCHAR(255) NULL,
		releaseID INT NULL,
		PRIMARY KEY  (`ID`),
		KEY `fromname` (`fromname`),
		KEY `date` (`date`),
		KEY `groupID` (`groupID`),
		FULLTEXT KEY `name` (`name`)
		) ENGINE=MYISAM AUTO_INCREMENT=1 ;

CREATE TABLE `releases` 
(
`ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`name` VARCHAR(255) NOT NULL DEFAULT '',
`searchname` VARCHAR(255) NOT NULL DEFAULT '',	
`totalpart` INT DEFAULT 0,	
`groupID` INT UNSIGNED NOT NULL DEFAULT '0',
`size` INT(11) UNSIGNED NOT NULL DEFAULT '0',
`postdate` DATETIME DEFAULT NULL,
`adddate` DATETIME DEFAULT NULL,
guid varchar(50) not null,
`fromname` VARCHAR(255) NULL,
categoryID INT DEFAULT 0,
PRIMARY KEY  (`ID`),
FULLTEXT KEY `searchname` (`searchname`)
) ENGINE=MYISAM AUTO_INCREMENT=1 ;		

CREATE TABLE `groups` (
  `ID` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `last_record` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `last_updated` DATETIME DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '0',
  `description` VARCHAR(255) NULL DEFAULT '',
  `postcount` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (`ID`),
  KEY `active` (`active`)
) ENGINE=MYISAM AUTO_INCREMENT=1 ;


CREATE TABLE `parts` (
  `ID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
  `binaryID` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `messageID` VARCHAR(255) NOT NULL DEFAULT '',
  `number` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `partnumber` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `size` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (`ID`),
  KEY `binaryID` (`binaryID`)
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

CREATE TABLE category
(
ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
title VARCHAR(255) NOT NULL,
parentID INT NULL
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

insert into category (ID, title) values (1, 'Console');
insert into category (ID, title) values (2, 'Movies');
insert into category (ID, title) values (3, 'Music');
insert into category (ID, title) values (4, 'PC');
insert into category (ID, title) values (5, 'TV');
insert into category (ID, title) values (6, 'XXX');
insert into category (ID, title) values (7, 'Other');
insert into category (ID, title, parentID) values (8, 'NDS', 1);
insert into category (ID, title, parentID) values (9, 'PSP', 1);
insert into category (ID, title, parentID) values (10, 'Wii', 1);
insert into category (ID, title, parentID) values (11, 'Xbox', 1);
insert into category (ID, title, parentID) values (12, 'Xbox 360', 1);
insert into category (ID, title, parentID) values (13, 'DVD', 2);
insert into category (ID, title, parentID) values (14, 'WMV-HD', 2);
insert into category (ID, title, parentID) values (15, 'XviD', 2);
insert into category (ID, title, parentID) values (16, 'x264', 2);
insert into category (ID, title, parentID) values (17, 'MP3', 3);
insert into category (ID, title, parentID) values (18, 'Video', 3);
insert into category (ID, title, parentID) values (19, '0day', 4);
insert into category (ID, title, parentID) values (20, 'ISO', 4);
insert into category (ID, title, parentID) values (21, 'Mac', 4);
insert into category (ID, title, parentID) values (22, 'DVD', 5);
insert into category (ID, title, parentID) values (23, 'H264', 5);
insert into category (ID, title, parentID) values (24, 'SWE', 5);
insert into category (ID, title, parentID) values (25, 'XviD', 5);
insert into category (ID, title, parentID) values (26, 'x264', 5);
insert into category (ID, title, parentID) values (27, 'DVD', 6);
insert into category (ID, title, parentID) values (28, 'WMV', 6);
insert into category (ID, title, parentID) values (29, 'XviD', 6);
insert into category (ID, title, parentID) values (30, 'x264', 6);
insert into category (ID, title, parentID) values (31, 'Misc', 7);


CREATE TABLE content
(
id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
title VARCHAR(255) NOT NULL,
url VARCHAR(2000) NULL,
body TEXT NULL,
metadescription VARCHAR(1000) NOT NULL,
metakeywords VARCHAR(1000) NOT NULL,
contenttype INT NOT NULL,
showinmenu INT NOT NULL,
`status` INT NOT NULL,
ordinal INT NULL
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

INSERT INTO content (title, body, contenttype, STATUS, metadescription, metakeywords, showinmenu) 
VALUES ('welcome to the homepage', '<p>this is the homepage text, its from the database</p>', 3, 1, '', '', 0);

INSERT INTO content (title, url, body, contenttype, STATUS, showinmenu, metadescription, metakeywords) 
VALUES ('example content', '/great/seo/content/page/', '<p>this is an example content page</p>', 2, 1, 1, '', '');

INSERT INTO content (title, url, body, contenttype, STATUS, showinmenu, metadescription, metakeywords) 
VALUES ('next content', '/another/great/seo/content/page/', '<p>this is another example content page</p>', 2, 1, 1, '', '');


CREATE TABLE site (
id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
`code` VARCHAR(255) NOT NULL,
title VARCHAR(1000) NOT NULL,
strapline VARCHAR(1000) NOT NULL,
metatitle VARCHAR(1000) NOT NULL,
metadescription VARCHAR(1000) NOT NULL,
metakeywords VARCHAR(1000) NOT NULL,
footer VARCHAR(2000) NOT NULL,
email VARCHAR(1000) NOT NULL,
root VARCHAR(255) NOT NULL,
lastupdate DATETIME NOT NULL,
google_adsense_menu VARCHAR(255) NULL,
google_adsense_search VARCHAR(255) NULL,
google_adsense_sidepanel VARCHAR(255) NULL,
google_analytics_acc VARCHAR(255) NULL
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

INSERT INTO site VALUES (NULL, 'newznab', 'Newznab', 'A great usenet indexer', 'meta title', 'metadesc', 'usenet,nzbs', 'intelligent footer text', 'info@newznab.com', '/', NOW(), NULL, NULL, NULL, NULL);