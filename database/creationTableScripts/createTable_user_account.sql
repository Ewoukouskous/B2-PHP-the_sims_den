CREATE TABLE user_account (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_role TEXT CHECK ( user_role IN ('user', 'admin') ) DEFAULT 'user' NOT NULL,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    date_joined DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
    last_login DATETIME NULL,
    id_profile_pic INTEGER DEFAULT 1 NOT NULL,

    FOREIGN KEY (id_profile_pic) REFERENCES profile_pic(id)
);