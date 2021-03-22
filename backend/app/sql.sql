create table closure_configs
(
    id int auto_increment
        primary key,
    name varchar(100) null,
    first_closure_DATE datetime null comment 'Allow students submit their file',
    final_closure_DATE datetime null comment 'Deadline for students',
    created datetime default CURRENT_TIMESTAMP null,
    created_by int null,
    modified datetime default CURRENT_TIMESTAMP null,
    modified_by int null,
    is_delete tinyint(1) default 0 null
);

create table faculties
(
    id int auto_increment
        primary key,
    closure_config_id int not null,
    name varchar(255) not null,
    description varchar(255) null,
    created datetime default CURRENT_TIMESTAMP null,
    created_by int null,
    modified datetime default CURRENT_TIMESTAMP null,
    modified_by int null,
    is_delete tinyint(1) default 0 null
);

create table files
(
    id int auto_increment
        primary key,
    submissions_id int not null,
    file_name varchar(255) not null,
    is_delete tinyint(1) default 0 null,
    file_path varchar(500) null,
    created datetime default CURRENT_TIMESTAMP null,
    created_by int null,
    modified datetime default CURRENT_TIMESTAMP null,
    modified_by int null
);

create table `groups`
(
    id int auto_increment
        primary key,
    name varchar(100) not null,
    description varchar(255) null,
    created datetime default CURRENT_TIMESTAMP null,
    created_by int null,
    modified datetime default CURRENT_TIMESTAMP null,
    modified_by int null
);

create table submissions
(
    id int auto_increment
        primary key,
    status enum ('accept', 'reject') null,
    is_active tinyint(1) default 1 null,
    note varchar(500) null,
    faculty_id int not null,
    created datetime default CURRENT_TIMESTAMP null,
    created_by int null,
    modified datetime default CURRENT_TIMESTAMP null,
    modified_by int null
);

create table users
(
    id int auto_increment
        primary key,
    group_id int not null,
    username varchar(50) not null,
    password varchar(255) not null,
    login_failed int null,
    full_name varchar(100) null,
    phone_number varchar(100) null,
    email varchar(100) null,
    age int null,
    DATE_of_birth date null,
    last_change_password date null,
    token varchar(255) null,
    created datetime default CURRENT_TIMESTAMP null,
    created_by int null,
    modified datetime default CURRENT_TIMESTAMP null,
    modified_by int null,
    constraint username
        unique (username)
);

CREATE TABLE `files_upload` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `name` varchar(255) NOT NULL,
                                `is_delete` tinyint(1) DEFAULT '0',
                                `file_path` varchar(500) DEFAULT NULL,
                                `created` datetime DEFAULT CURRENT_TIMESTAMP,
                                `created_by` int(11) DEFAULT NULL,
                                `modified` datetime DEFAULT CURRENT_TIMESTAMP,
                                `modified_by` int(11) DEFAULT NULL,
                                PRIMARY KEY (`id`),
                                key `name` (name) using btree ,
                                key `created` (created) using btree ,
                                key `created_by` (created_by) using btree ,
                                key `modified` (modified) using btree ,
                                key `modified_by` (modified_by) using btree
);

create table logs (
                      id int primary key auto_increment,
                      error text,
                      created datetime default CURRENT_TIMESTAMP null,
                      key `created` (created) using btree
);

alter table users drop column token,
                  add column remember_token varchar(255) default null,
                  add index `token` (remember_token);

create table sessions (
                          id varchar(255) unique ,
                          user_id int,
                          ip_address varchar(45),
                          user_agent text,
                          payload text,
                          last_activity int
);

CREATE TABLE `faculty_uploads` (
                                   `id` int(11) NOT NULL AUTO_INCREMENT,
                                   `is_active` bool default true,
                                   `note` varchar(500) DEFAULT NULL,
                                   `file_upload_id` int(11) NOT NULL,
                                   `group_comment_id` int(11) default null,
                                   `created` datetime DEFAULT CURRENT_TIMESTAMP,
                                   `created_by` int(11) DEFAULT NULL,
                                   `modified` datetime DEFAULT CURRENT_TIMESTAMP,
                                   `modified_by` int(11) DEFAULT NULL,
                                   PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

alter table faculty_uploads add column faculty_id int;
alter table faculty_uploads add column teacher_status varchar(255) default 'pending';
alter table users add column is_active bool default 1;

create table comments (
                          id int primary key auto_increment,
                          group_id int not null,
                          message varchar(255) not null,
                          file_id int,
                          created_by int,
                          created datetime default current_timestamp
);
alter table comments add column username_created varchar(255);
alter table logs add column status bool default 1;
