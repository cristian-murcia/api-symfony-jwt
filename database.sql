/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  Cristian Guzman
 * Created: 30/07/2020
 */

CREATE DATABASE IF NOT EXISTS api_rest_symfony;
USE api_rest_symfony;

CREATE TABLE users(
    id   int(255) auto_increment NOT NULL,
    name    varchar(50) not null,
    surname varchar(150),
    role    varchar(20) not null,
    email   varchar(255) not null,
    password    varchar(20),
    created_at  datetime default CURRENT_TIMESTAMP,
    CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;


CREATE TABLE videos(
    id int(255) auto_increment not null,
    user_id int (255) not null,
    title varchar(255) not null,
    description text,
    url varchar(255) not null,
    status varchar(50),
    created_at datetime default CURRENT_TIMESTAMP,
    updated_at datetime default CURRENT_TIMESTAMP,
    CONSTRAINT pk_videos PRIMARY KEY(id),
    CONSTRAINT fk_video_user FOREIGN KEY(user_id) REFERENCES users(id)
)ENGINE=InnoDb;