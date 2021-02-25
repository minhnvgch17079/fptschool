CREATE TABLE files (
                       id INT auto_increment primary key ,
                       file_name VARCHAR (255) NOT NULL ,
                       is_delete BOOLEAN DEFAULT 1,
                       file_path VARCHAR(500),
                       created DATETIME DEFAULT CURRENT_TIMESTAMP,
                       created_by INT,
                       modified DATETIME DEFAULT CURRENT_TIMESTAMP,
                       modified_by INT
);
