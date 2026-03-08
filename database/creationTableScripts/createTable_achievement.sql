CREATE TABLE achievement (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    achievement_name TEXT NOT NULL,
    achievement_desc TEXT DEFAULT NULL,
    icon_path TEXT NOT NULL
);