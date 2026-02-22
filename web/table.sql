CREATE DATABASE splitwise_clone;

USE splitwise_clone;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE grps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE group_members (
    group_id INT,
    user_id INT,
    FOREIGN KEY (group_id) REFERENCES grps(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    PRIMARY KEY (group_id, user_id)
);

CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    amount DECIMAL(10, 2),
    date DATE,
    group_id INT,
    paid_by INT,
    FOREIGN KEY (group_id) REFERENCES grps(id),
    FOREIGN KEY (paid_by) REFERENCES users(id)
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10, 2),
    paid_by INT,
    paid_to INT,
    group_id INT,
    date DATE,
    FOREIGN KEY (paid_by) REFERENCES users(id),
    FOREIGN KEY (paid_to) REFERENCES users(id),
    FOREIGN KEY (group_id) REFERENCES grps(id)
);
