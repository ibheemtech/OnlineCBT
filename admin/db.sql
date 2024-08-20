CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,               -- Unique identifier for each admin
    username VARCHAR(50) NOT NULL UNIQUE,           -- Admin's username, required and should be unique
    name VARCHAR(100) NOT NULL,                      -- Admin's full name, required
    email VARCHAR(100) NOT NULL UNIQUE,              -- Admin's email, required and should be unique
    profile_picture VARCHAR(255) DEFAULT 'default.png', -- Path to the admin's profile picture, defaults to 'default.png' if not provided
    password VARCHAR(255) NOT NULL,                  -- Admin's hashed password, required
    navbar_color VARCHAR(7) DEFAULT '#343a40'        -- Navbar color, default is dark gray
);


CREATE TABLE courses (
  course_id INT AUTO_INCREMENT,
  course_name VARCHAR(255) NOT NULL,
  timer INT NOT NULL,
  total_questions INT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (course_id),
  CONSTRAINT ck_is_active CHECK (is_active IN (0, 1))
);

CREATE TABLE questions (
  question_id INT AUTO_INCREMENT,
  course_id INT NOT NULL,
  question_text TEXT NOT NULL,
  option1 VARCHAR(255) NOT NULL,
  option2 VARCHAR(255) NOT NULL,
  option3 VARCHAR(255) NOT NULL,
  option4 VARCHAR(255) NOT NULL,
  correct_option TINYINT(1) NOT NULL,
  PRIMARY KEY (question_id),
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

    CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL UNIQUE,
email VARCHAR(100) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
name VARCHAR(100),
profile_picture VARCHAR(255),
is_active BOOLEAN DEFAULT 1
);

CREATE TABLE exam_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    answer VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_result (course_id, user_id, question_id)
);

CREATE TABLE course_results (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Unique identifier for each record
    course_id INT NOT NULL,                     -- ID of the course
    user_id INT NOT NULL,                       -- ID of the user
    total_questions INT NOT NULL,               -- Total number of questions in the exam
    correct_answers INT NOT NULL,               -- Number of correct answers by the user
    percentage DECIMAL(5,2) NOT NULL,           -- Percentage of correct answers (e.g., 75.50)
    UNIQUE KEY unique_course_user (course_id, user_id), -- Ensures one result per course per user
    FOREIGN KEY (course_id) REFERENCES courses(id),     -- Foreign key referencing the courses table
    FOREIGN KEY (user_id) REFERENCES users(id)          -- Foreign key referencing the users table
);