-- SQL script som lager de nødvendige databasene og tables
CREATE DATABASE IF NOT EXISTS board;

USE board;

CREATE TABLE users (
    user_id int NOT NULL AUTO_INCREMENT,
    username varchar(255),
    password varchar(255),
    profile_image varchar(50) DEFAULT "defaultprofile.svg",
    PRIMARY KEY (user_id)
);

CREATE TABLE messages (
    message_id int NOT NULL AUTO_INCREMENT,
    username varchar(255) DEFAULT (NULL),
    message varchar(500) DEFAULT (NULL),
    file varchar(50) DEFAULT (NULL),
    date varchar(64),
    time varchar(64),
    endret int NULL DEFAULT (NULL),
    PRIMARY KEY (message_id)
);