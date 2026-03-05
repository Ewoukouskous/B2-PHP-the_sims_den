CREATE TABLE game (
    id INTEGER AUTOINCREMENT PRIMARY KEY,
    game_name TEXT UNIQUE NOT NULL,
    price REAL NOT NULL,
    game_type TEXT CHECK ( game_type IN ('pc','console','smartphone') ) NOT NULL,
    game_desc TEXT NOT NULL,
    image_hero_path TEXT NOT NULL,
    image_title_path TEXT NOT NULL,
    favorites_number INTEGER DEFAULT 0 NOT NULL,
    pegi_age TEXT CHECK ( pegi_age IN ('3','7','12','16','18') ) NOT NULL
);