CREATE TABLE submissions(
                            id INT auto_increment primary key ,
                            status enum('accept', 'reject'),
                            is_active BOOLEAN DEFAULT 1,
                            note VARCHAR(500),
                            faculty_id INT NOT NULL,
                            created DATETIME DEFAULT CURRENT_TIMESTAMP,
                            created_by INT,
                            modified DATETIME DEFAULT CURRENT_TIMESTAMP,
                            modified_by INT
);
