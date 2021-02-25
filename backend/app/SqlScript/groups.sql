CREATE TABLE groups(
                       id INT auto_increment primary key ,
                       name VARCHAR(100) NOT NULL ,
                       description VARCHAR(255),
                       created DATETIME DEFAULT CURRENT_TIMESTAMP,
                       created_by INT,
                       modified DATETIME DEFAULT CURRENT_TIMESTAMP,
                       modified_by INT
);
