CREATE TABLE person (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname CHAR(255) NOT NULL,
    lastname CHAR(255) NOT NULL,
    birthdate DATE NOT NULL,
    password VARCHAR(255) NOT NULL, # TODO: should be replaced by passwordseed and passwordcipher
    email CHAR(255) NOT NULL
    )
    ENGINE InnoDB,
    CHARACTER SET utf8
;

CREATE TABLE run (
        id MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        runnerid TINYINT UNSIGNED NOT NULL,
        starttimestamp TIMESTAMP(0) NOT NULL, # TODO: type should be updated in 2038
        durationseconds MEDIUMINT UNSIGNED NOT NULL,
        distancemeters MEDIUMINT UNSIGNED NOT NULL,
        FOREIGN KEY (runnerid) REFERENCES person (id) ON DELETE CASCADE ON UPDATE CASCADE
    )
    ENGINE InnoDB,
    CHARACTER SET utf8
;