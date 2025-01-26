USE iom_db_test;

CREATE TABLE IF NOT EXISTS `order` (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description varchar(255),
    date DATE NOT NULL,
    PRIMARY KEY (id)
);
