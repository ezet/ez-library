DROP TABLE IF EXISTS `User`;
DROP TABLE IF EXISTS Book;
DROP TABLE IF EXISTS BookReview;
DROP TABLE IF EXISTS Publisher;
DROP TABLE IF EXISTS Tag;
DROP TABLE IF EXISTS Category;

CREATE TABLE `User` (
UserId INT unsigned NOT NULL auto_increment,
Username VARCHAR(20) NOT NULL,
Password VARCHAR(74) NOT NULL,
FirstName VARCHAR(127) NOT NULL,
LastName VARCHAR(127) NOT NULL,
Email VARCHAR(127) NOT NULL,
Template enum ('default') NOT NULL DEFAULT 'default',
LoginCount INT NOT NULL DEFAULT 0,
LastLogin TIMESTAMP NOT NULL DEFAULT current_timestamp ON UPDATE current_timestamp,
Created TIMESTAMP NOT NULL DEFAULT 0,
PRIMARY KEY (UserId)
-- UNIQUE (`Username`)
-- UNIQUE (`Email`)
) DEFAULT charset=utf8, engine=InnoDB;

CREATE TABLE Book (
BookId INT unsigned NOT NULL auto_increment,
UserId INT unsigned NOT NULL,
Isbn BIGINT unsigned NOT NULL,
Title VARCHAR(255) NOT NULL,
Author VARCHAR(255) NOT NULL,
Publisher VARCHAR(255) NOT NULL,
DatePublished DATE NOT NULL,
Synopsis BLOB NOT NULL,
Cover BLOB NOT NULL,
Tags VARCHAR(255) NOT NULL,
CategoryId INT unsigned NOT NULL,
DateAdded TIMESTAMP NOT NULL DEFAULT current_timestamp ON UPDATE current_timestamp,
-- Rating enum (0, 1, 2, 3, 4, 5) DEFAULT 0,
Rating TINYINT unsigned NOT NULL DEFAULT 0,
Review BLOB NOT NULL,
PRIMARY KEY (BookId),
INDEX (UserId)
) DEFAULT charset=utf8, engine=InnoDB;

CREATE TABLE Tag (
TagId INT unsigned NOT NULL auto_increment,
TagName VARCHAR(127) NOT NULL,
PRIMARY KEY (TagId)
) DEFAULT charset=utf8, engine=InnoDB;

CREATE TABLE Category (
CategoryId INT unsigned NOT NULL auto_increment,
CategoryName VARCHAR(127) NOT NULL,
PRIMARY KEY (CategoryId),
UNIQUE (CategoryName)
) DEFAULT charset=utf8, engine=InnoDB;