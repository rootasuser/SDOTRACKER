CREATE DATABASE IF NOT EXISTS sdo_teachers_tracker_v1;
USE sdo_teachers_tracker_v1;


CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



INSERT INTO users (username, password)
VALUES
('admin1', '$2y$10$JeUc3gSg2H8WEoPSNlKFBepmXHziXB9oCUa3SpYG1H6tWmkNbaB1G');  
INSERT INTO users (username, password)
VALUES
('admin2', '$2y$10$JeUc3gSg2H8WEoPSNlKFBepmXHziXB9oCUa3SpYG1H6tWmkNbaB1G');  
INSERT INTO users (username, password)
VALUES
('admin3', '$2y$10$JeUc3gSg2H8WEoPSNlKFBepmXHziXB9oCUa3SpYG1H6tWmkNbaB1G');  
INSERT INTO users (username, password)
VALUES
('admin4', '$2y$10$JeUc3gSg2H8WEoPSNlKFBepmXHziXB9oCUa3SpYG1H6tWmkNbaB1G');  





CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    action TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empPosition VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO positions (empPosition) VALUES 
('Teacher I'), 
('Teacher II'), 
('Teacher III'), 
('Teacher IV'), 
('Teacher V'), 
('Teacher VI'), 
('Master Teacher I'), 
('Master Teacher II'), 
('Master Teacher III'), 
('Master Teacher IV'), 
('Master Teacher V'), ), 
('Head Teacher I'), 
('Head Teacher II'), 
('Head Teacher III'), 
('Head Teacher IV'), 
('Head Teacher V'), 
('Head Teacher VI'), 
('Principal I'), 
('Principal II'), 
('Principal III'), 
('Principal IV'), 
('Principal V'), 
('Principal VI'), ;


CREATE TABLE IF NOT EXISTS schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empAssignSchool VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO schools (empAssignSchool) VALUES 
('Dawis Elementary School'), 
('Dawis National High School'), 
('A. Zulueta Elementary School');


CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empTeachingSubject VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO subjects (empTeachingSubject) VALUES
('FILIPINO V'), ('FILIPINO III'), ('FILIPINO II'), ('FILIPINO I');

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empName VARCHAR(255) NOT NULL,
    empNumber VARCHAR(50) UNIQUE NOT NULL,
    empAddress TEXT NOT NULL,
    empSex VARCHAR(10) NOT NULL,
    empPosition_id INT NOT NULL,
    empAssignSchool_id INT NOT NULL,
    empHistory TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE employee_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

CREATE TABLE `database_backups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `backup_name` VARCHAR(255) NOT NULL,
  `backup_content` LONGTEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE backup_passcode (
    id INT AUTO_INCREMENT PRIMARY KEY,
    passcode_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Sample passcode '1234' 
INSERT INTO backup_passcode (passcode_hash) VALUES 
    ('$2y$10$NfS23H20bEblHqyZOK0w/.G//1GK5LrTpFOAFH/ngHJT2p5vj9IJK');



    CREATE TABLE admin_updated_activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    employee_id INT NOT NULL,
    field_name VARCHAR(255) NOT NULL,
    old_value TEXT,
    new_value TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE admin_updated_activity_logs
ADD COLUMN user_id INT NOT NULL AFTER id;

CREATE TABLE IF NOT EXISTS trainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    date_conducted DATE NOT NULL,
    venue VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS employee_trainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    training_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (training_id) REFERENCES trainings(id)
);