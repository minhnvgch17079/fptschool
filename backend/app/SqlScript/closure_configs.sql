CREATE TABLE closure_configs(
                                id INT primary key auto_increment,
                                name VARCHAR(100),
                                first_closure_DATE DATETIME COMMENT 'Allow students submit their file',
                                final_closure_DATE DATETIME COMMENT 'Deadline for students',
                                created DATETIME DEFAULT CURRENT_TIMESTAMP,
                                created_by INT,
                                modified DATETIME DEFAULT CURRENT_TIMESTAMP,
                                modified_by INT
);