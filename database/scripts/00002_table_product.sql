USE iom_db_test;

CREATE TABLE IF NOT EXISTS `product` (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    price varchar(255) NOT NULL,
    PRIMARY KEY (id)
);