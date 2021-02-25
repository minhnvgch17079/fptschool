CREATE TABLE users(
                      id INT AUTO_INCREMENT PRIMARY KEY ,
                      group_id INT NOT NULL ,
                      username VARCHAR(50) UNIQUE NOT NULL ,
                      password VARCHAR(255) NOT NULL ,
                      login_failed INT,
                      full_name VARCHAR(100) ,
                      phone_number VARCHAR(100),
                      email VARCHAR(100) ,
                      age INT,
                      DATE_of_birth DATE,
                      last_change_password DATE,
                      token VARCHAR(255),
                      created DATETIME DEFAULT CURRENT_TIMESTAMP,
                      created_by INT,
                      modified DATETIME DEFAULT CURRENT_TIMESTAMP,
                      modified_by INT
);
