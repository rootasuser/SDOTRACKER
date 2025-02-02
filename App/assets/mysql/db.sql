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
('admin', '$2y$10$JeUc3gSg2H8WEoPSNlKFBepmXHziXB9oCUa3SpYG1H6tWmkNbaB1G');  



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
('Master Teacher V'), 
('Master Teacher VI'), 
('Master Teacher VII'), 
('Master Teacher VIII'), 
('Master Teacher IX'), 
('Master Teacher X'), 
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
('Principal VI'), 
('Principal VII'), 
('Principal VIII'), 
('Principal IX'), 
('Principal X'), 
('Principal XI'), 
('Principal XII'), 
('Principal XIII'), 
('Principal XIV'), 
('Principal XV'), 
('Principal XVI'), 
('Principal XVII'), 
('Principal XVIII'), 
('Principal XIX'), 
('Principal XX'), 
('Principal XXI'), 
('Principal XXII'), 
('Principal XXIII'), 
('Principal XXIV'), 
('Principal XXV'), 
('Principal XXVI'), 
('Principal XXVII'), 
('Principal XXVIII'), 
('Principal XXIX'), 
('Principal XXX'), 
('Principal XXXI'), 
('Principal XXXII'), 
('Principal XXXIII'), 
('Principal XXXIV'), 
('Principal XXXV'), 
('Principal XXXVI'), 
('Principal XXXVII'), 
('Principal XXXVIII'), 
('Principal XXXIX'), 
('Principal XL'), 
('Principal XLI'), 
('Principal XLII'), 
('Principal XLIII'), 
('Principal XLIV'), 
('Principal XLV'), 
('Principal XLVI'), 
('Principal XLVII'), 
('Principal XLVIII'), 
('Principal XLIX'), 
('Principal L'), 
('Principal LI'), 
('Principal LII'), 
('Principal LIII'), 
('Principal LIV'), 
('Principal LV'), 
('Principal LVI'), 
('Principal LVII'), 
('Principal LVIII'), 
('Principal LIX'), 
('Principal LX'), 
('Principal LXI'), 
('Principal LXII'), 
('Principal LXIII'), 
('Principal LXIV'), 
('Principal LXV'), 
('Principal LXVI'), 
('Principal LXVII'), 
('Principal LXVIII'), 
('Principal LXIX'), 
('Principal LXX'), 
('Principal LXXI'), 
('Principal LXXII'), 
('Principal LXXIII'), 
('Principal LXXIV'), 
('Principal LXXV'), 
('Principal LXXVI'), 
('Principal LXXVII'), 
('Principal LXXVIII'), 
('Principal LXXIX'), 
('Principal LXXX'), 
('Principal LXXXI'), 
('Principal LXXXII'), 
('Principal LXXXIII'), 
('Principal LXXXIV'), 
('Principal LXXXV'), 
('Principal LXXXVI'), 
('Principal LXXXVII'), 
('Principal LXXXVIII'), 
('Principal LXXXIX'), 
('Principal XC'), 
('Principal XCI'), 
('Principal XCII');


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
