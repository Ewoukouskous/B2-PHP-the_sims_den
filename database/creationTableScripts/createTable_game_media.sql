CREATE TABLE game_media (
    id INTEGER AUTOINCREMENT PRIMARY KEY,
    file_path TEXT NOT NULL,
    id_game INTEGER NOT NULL,
    -- Lorsqu'un jeu est supprimé, on supprime automatiquement
    -- les lignes "game_media" rattachée(s) au jeu
    FOREIGN KEY (id_game) REFERENCES game(id) ON DELETE CASCADE
);