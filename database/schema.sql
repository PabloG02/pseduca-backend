-- Remove any existing information
DROP DATABASE IF EXISTS pseduca;

CREATE DATABASE pseduca;
USE pseduca;

-- User

CREATE TABLE User
(
    username   VARCHAR(50) PRIMARY KEY,
    password   VARCHAR(255) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    name       VARCHAR(255) NOT NULL,
    activated  BOOLEAN   DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP
);

CREATE TABLE Role
(
    name VARCHAR(50) PRIMARY KEY
);

CREATE TABLE UserRole
(
    user_id VARCHAR(50),
    role_id VARCHAR(50),
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES User (username) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES Role (name) ON DELETE CASCADE
);

-- Resources

CREATE TABLE Resource
(
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(180) NOT NULL,
    acronym         VARCHAR(10)  NOT NULL,
    year            YEAR         NOT NULL,
    description     TEXT,
    notes           TEXT,
    image_uri       VARCHAR(255), -- Alt. text will be the name of the resource

    min_age_years   TINYINT UNSIGNED CHECK (min_age_years BETWEEN 0 AND 99),
    min_age_months  TINYINT UNSIGNED CHECK (min_age_months BETWEEN 0 AND 11),
    max_age_years   TINYINT UNSIGNED CHECK (max_age_years BETWEEN 0 AND 99),
    max_age_months  TINYINT UNSIGNED CHECK (max_age_months BETWEEN 0 AND 11),

    completion_time SMALLINT UNSIGNED CHECK (completion_time BETWEEN 0 AND 999),

    CONSTRAINT chk_age_range CHECK ((min_age_years * 12 + min_age_months) <= (max_age_years * 12 + max_age_months))
);

CREATE TABLE Author
(
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE ResourceAuthor
(
    resource_id INT UNSIGNED,
    author_id   INT UNSIGNED,
    PRIMARY KEY (resource_id, author_id),
    FOREIGN KEY (resource_id) REFERENCES Resource (id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES Author (id)
);

CREATE TABLE Category
(
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE ResourceCategory
(
    resource_id INT UNSIGNED,
    category_id INT UNSIGNED,
    PRIMARY KEY (resource_id, category_id),
    FOREIGN KEY (resource_id) REFERENCES Resource (id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES Category (id) ON DELETE CASCADE
);

CREATE TABLE ResourceFile
(
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resource_id INT UNSIGNED NOT NULL,
    file_uri    VARCHAR(255) NOT NULL,
    FOREIGN KEY (resource_id) REFERENCES Resource (id) ON DELETE CASCADE
);

CREATE TABLE Format
(
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL -- Values: Paper, Online
);

CREATE TABLE ResourceFormat
(
    resource_id INT UNSIGNED,
    format_id   INT UNSIGNED,
    PRIMARY KEY (resource_id, format_id),
    FOREIGN KEY (resource_id) REFERENCES Resource (id) ON DELETE CASCADE,
    FOREIGN KEY (format_id) REFERENCES Format (id)
);

CREATE TABLE Area
(
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL -- Values: Learning, Development, Special Educational Needs (SEN)
);

CREATE TABLE ResourceArea
(
    resource_id INT UNSIGNED,
    area_id     INT UNSIGNED,
    PRIMARY KEY (resource_id, area_id),
    FOREIGN KEY (resource_id) REFERENCES Resource (id) ON DELETE CASCADE,
    FOREIGN KEY (area_id) REFERENCES Area (id)
);

CREATE TABLE ResType
(
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL -- Values: Assessment, Intervention
);

CREATE TABLE ResourceType
(
    resource_id INT UNSIGNED,
    type_id     INT UNSIGNED,
    PRIMARY KEY (resource_id, type_id),
    FOREIGN KEY (resource_id) REFERENCES Resource (id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES ResType (id)
);

CREATE TABLE ResApplication
(
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL -- Values: Individual, Group
);

CREATE TABLE ResourceApplication
(
    resource_id    INT UNSIGNED,
    application_id INT UNSIGNED,
    PRIMARY KEY (resource_id, application_id),
    FOREIGN KEY (resource_id) REFERENCES Resource (id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES ResApplication (id)
);

-- Articles

CREATE TABLE Article
(
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(255) NOT NULL,
    subtitle   TEXT,
    body       TEXT,
    image_uri  VARCHAR(255),
    image_alt  VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- TODO: Author

    CONSTRAINT chk_image_alt CHECK (
        (image_uri IS NULL AND image_alt IS NULL) OR
        (image_uri IS NOT NULL AND image_alt IS NOT NULL)
    )
);

-- Training

CREATE TABLE AcademicPrograms
(
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(255)                             NOT NULL,
    qualification_level ENUM ('Master', 'Doctorate')             NOT NULL,
    description         TEXT,
    image_uri           VARCHAR(255),
    image_alt           VARCHAR(255),
    available_slots     INT UNSIGNED                             NOT NULL,
    teaching_type       ENUM ('Online', 'Onsite')                NOT NULL,
    offering_frequency  ENUM ('Annual', 'Biannual', 'Quarterly') NOT NULL,
    duration_ects       INT UNSIGNED                             NOT NULL,
    location            VARCHAR(255)                             NOT NULL,
    url                 VARCHAR(255)
);

CREATE TABLE Courses
(
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT,
    start_date  DATE         NOT NULL,
    end_date    DATE         NOT NULL,
    image_uri   VARCHAR(255),
    url         VARCHAR(255),

    CONSTRAINT chk_course_dates CHECK (end_date >= start_date)
);

-- About Us

CREATE TABLE TeamMember
(
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255) NOT NULL,
    email         VARCHAR(100) NOT NULL,
    image_uri     VARCHAR(255),         -- Alt. text will be the name of the team member
    biography     TEXT,
    researcher_id INT UNSIGNED NOT NULL -- https://portalcientifico.uvigo.gal/investigadores/{id}
);

-- Tests and Programs

-- TODO

-- Contact

CREATE TABLE Contact
(
    organization_key VARCHAR(50) PRIMARY KEY,
    address          VARCHAR(255) NOT NULL,
    email            VARCHAR(100) NOT NULL,
    phone            VARCHAR(20)
    -- TODO: google_maps VARCHAR(255)
);

-- Webpage texts

CREATE TABLE WebpageText
(
    text_key VARCHAR(50) PRIMARY KEY,
    text     TEXT NOT NULL
);
