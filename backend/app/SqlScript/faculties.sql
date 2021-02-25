CREATE TABLE faculties(
                          id INT auto_increment primary key ,
                          closure_config_id INT NOT NULL ,
                          name VARCHAR(255) not null ,
                          description VARCHAR(255)

);

# modified at 2021-02-25
ALTER TABLE faculties ADD COLUMN created DATETIME DEFAULT CURRENT_TIMESTAMP,
                      ADD COLUMN created_by INT,
                      ADD COLUMN modified DATETIME DEFAULT CURRENT_TIMESTAMP,
                      ADD COLUMN modified_by INT
