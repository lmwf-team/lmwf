CREATE TABLE person (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname CHAR(255) NOT NULL,
    lastname CHAR(255) NOT NULL,
    birthdate DATE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email CHAR(255) NOT NULL
    )
    ENGINE InnoDB,
    CHARACTER SET utf8
;