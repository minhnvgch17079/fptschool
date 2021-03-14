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
