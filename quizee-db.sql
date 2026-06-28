CREATE DATABASE QUIZ;
USE QUIZ;
CREATE TABLE student (
	student_id INT AUTO_INCREMENT PRIMARY KEY,
    MIS varchar(10),
    name varchar(50),
    branch varchar(50),
    division int,
    email varchar(50) UNIQUE NOT NULL,
    password varchar(255) NOT NULL,
    university varchar(50)
);

SELECT * from student;

CREATE TABLE teacher (
	teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    name varchar(50),
    email varchar(50) UNIQUE NOT NULL,
    password varchar(255) NOT NULL,
    university varchar(50)
);

CREATE TABLE quiz (
    quiz_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    duration INT NOT NULL,
    teacher_id INT NOT NULL
);

CREATE TABLE question (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text VARCHAR(255) NOT NULL,
    option1 VARCHAR(100),
    option2 VARCHAR(100),
    option3 VARCHAR(100),
    option4 VARCHAR(100),
    correct_option INT NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quiz(quiz_id)
);
