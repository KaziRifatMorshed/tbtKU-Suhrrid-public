-- Database Name: tbtKU_Suhrrid_db
CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    warning_count INT DEFAULT 0,
    identity ENUM ('student', 'outsider') NOT NULL,
    administrative_access ENUM ('user', 'moderator', 'admin') DEFAULT 'user'
) ENGINE = InnoDB;

CREATE TABLE ku_student (
    user_id INT PRIMARY KEY,
    student_id VARCHAR(15) UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE outsider (
    user_id INT PRIMARY KEY,
    address TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE advertisement (
    ad_id INT AUTO_INCREMENT PRIMARY KEY,
    author_identity ENUM ('student', 'outsider', 'land owner', 'caretaker') NOT NULL,
    availability ENUM ('open', 'closed', 'inactive') NOT NULL DEFAULT 'open',
    last_renewal_date DATE,
    reported_status BOOLEAN DEFAULT FALSE,
    approval_status ENUM ('not approved', 'approved') NOT NULL DEFAULT 'not approved',
    which_ad ENUM ('room', 'sell') NOT NULL
) ENGINE = InnoDB;

CREATE TABLE room_advertisement (
    ad_id INT PRIMARY KEY,
    room_ad_purpose ENUM ('Search Room', 'Room To-Let') NOT NULL,
    zone_name VARCHAR(60) NOT NULL,
    full_address TEXT NOT NULL,
    rent_cost INT NOT NULL,
    gender ENUM ('Male', 'Female') NOT NULL,
    room_type TEXT NOT NULL,
    room_count INT NOT NULL,
    student_count INT NOT NULL,
    which_month VARCHAR(20) NOT NULL,
    agreement_policy ENUM ('Short-term', 'Long-term', 'Flexible') NOT NULL,
    bathroom_details TEXT,
    roommate_details TEXT,
    location_link TEXT,
    religion VARCHAR(50),
    security TEXT,
    furniture TEXT,
    entry_time VARCHAR(255),
    nearby_landmarks TEXT,
    owner_name VARCHAR(255),
    owner_contact VARCHAR(20),
    distance VARCHAR(50),
    facing_side ENUM ('North', 'South', 'East', 'West'),
    which_floor VARCHAR(255),
    kitchen BOOLEAN,
    fridge BOOLEAN,
    drinking_water BOOLEAN,
    balcony TEXT,
    room_size VARCHAR(50),
    garage BOOLEAN,
    smoking_details TEXT,
    problems TEXT,
    other_details TEXT,
    FOREIGN KEY (ad_id) REFERENCES advertisement (ad_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE sell_advertisement (
    ad_id INT PRIMARY KEY,
    item_ad_purpose ENUM ('Search or Buy Item', 'Sell Item') NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    brand_model VARCHAR(255),
    item_condition ENUM ('New', 'Good', 'Average', 'Needs Repair') NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    original_price DECIMAL(10, 2),
    location VARCHAR(255) NOT NULL,
    FOREIGN KEY (ad_id) REFERENCES advertisement (ad_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE posts (
    user_id INT,
    ad_id INT,
    posting_date DATE NOT NULL,
    PRIMARY KEY (user_id, ad_id),
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE,
    FOREIGN KEY (ad_id) REFERENCES advertisement (ad_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE comment (
    comment_id INT AUTO_INCREMENT,
    comment_text TEXT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    PRIMARY KEY (comment_id)
) ENGINE = InnoDB;

CREATE TABLE user_comments (
    comment_id INT,
    user_id INT,
    PRIMARY KEY (user_id, comment_id),
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comment (comment_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE ad_comments (
    comment_id INT,
    ad_id INT,
    PRIMARY KEY (comment_id, ad_id),
    FOREIGN KEY (ad_id) REFERENCES advertisement (ad_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comment (comment_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE photos (
    ad_id INT,
    photo_path VARCHAR(100) NOT NULL,
    PRIMARY KEY (ad_id, photo_path),
    FOREIGN KEY (ad_id) REFERENCES advertisement (ad_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE emails (
    user_id INT,
    email VARCHAR(255) UNIQUE NOT NULL,
    PRIMARY KEY (user_id, email),
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE phone_no (
    user_id INT,
    phone_no VARCHAR(20) UNIQUE NOT NULL,
    PRIMARY KEY (user_id, phone_no),
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE facilities (
    ad_id INT PRIMARY KEY,
    food BOOLEAN DEFAULT FALSE,
    cctv BOOLEAN DEFAULT FALSE,
    geyser BOOLEAN DEFAULT FALSE,
    ips BOOLEAN DEFAULT FALSE,
    drinking_water BOOLEAN DEFAULT FALSE,
    garbage BOOLEAN DEFAULT FALSE,
    assistant BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (ad_id) REFERENCES advertisement (ad_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE utility_bills (
    ad_id INT PRIMARY KEY,
    wifi INT,
    electricity INT,
    food INT,
    gas INT,
    water INT,
    garbage INT,
    fridge INT,
    security INT,
    assistant INT,
    FOREIGN KEY (ad_id) REFERENCES advertisement (ad_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE marking_favourite (
    user_id INT,
    ad_id INT,
    PRIMARY KEY (user_id, ad_id),
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE,
    FOREIGN KEY (ad_id) REFERENCES advertisement (ad_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE reports (
    report_against_user_id INT,
    reporter_user_id INT,
    report_text TEXT NOT NULL,
    report_date DATE NOT NULL,
    PRIMARY KEY (report_against_user_id, reporter_user_id),
    FOREIGN KEY (report_against_user_id) REFERENCES user (user_id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE login_info (
    user_id INT NOT NULL,
    pass VARCHAR(255) NOT NULL,
    user_status ENUM ('allowed', 'banned') DEFAULT 'allowed',
    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user (user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE DEFINER=`root`@`localhost` EVENT `inactive_15_day_old_ad_EventSchedule` ON SCHEDULE EVERY 1 DAY STARTS '2025-05-17 03:30:12' ON COMPLETION NOT PRESERVE ENABLE COMMENT 'In every night 3.30 AM, all ad with 15 days age will inactive' DO UPDATE advertisement a
JOIN posts p ON a.ad_id = p.ad_id
SET a.availability = 'inactive'
WHERE p.posting_date < DATE_SUB(CURRENT_DATE(), INTERVAL 15 DAY)
AND a.availability = 'open'
