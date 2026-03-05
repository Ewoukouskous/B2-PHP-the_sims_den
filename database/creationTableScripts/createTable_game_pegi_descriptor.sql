CREATE TABLE game_pegi_descriptor (
    id_game INTEGER NOT NULL,
    id_descriptor INTEGER NOT NULL,

    -- La PRIMARY KEY est la paire de (id_game, id_descriptor)
    -- pour éviter a un jeu d'avoir plusieur fois le même descriptor
    PRIMARY KEY (id_game, id_descriptor),

    -- Les clé FOREIGN suivantes sont en "ON DELETE CASCADE"
    -- pour que lorsqu'un game ou pegi_descriptor est supprimé,
    -- on supprime automatiquement la ligne game_pegi_descriptor correspondante
    FOREIGN KEY (id_game) REFERENCES game(id) ON DELETE CASCADE,
    FOREIGN KEY (id_descriptor) REFERENCES pegi_descriptor(id) ON DELETE CASCADE
);