CREATE TABLE user_favorite (
    id_user INTEGER NOT NULL,
    id_game INTEGER NOT NULL,
    playtime_hours INTEGER DEFAULT 0 NOT NULL,
    date_added DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,

    -- La clé primaire est la paire (id_user,id_game)
    -- pour éviter qu'un utilisateur ai plusieurs fois le même favoris
    PRIMARY KEY (id_user, id_game),

    -- Les clés FOREIGN suivantes sont en "ON DELETE CASCADE"
    -- pour que lorsqu'un user ou game est supprimé,
    -- on supprime automatiquement la ligne user_favorite correspondante
    FOREIGN KEY (id_user) REFERENCES user_account(id) ON DELETE CASCADE,
    FOREIGN KEY (id_game) REFERENCES game(id) ON DELETE CASCADE
);