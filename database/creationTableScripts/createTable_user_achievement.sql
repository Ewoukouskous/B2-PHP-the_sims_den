CREATE TABLE user_achievement (
    id_user INTEGER NOT NULL,
    id_achievement INTEGER NOT NULL,
    unlocked_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,

    -- La clé primaire est la paire (id_user,id_achievement)
    -- pour éviter qu'un utilisateur ai plusieurs fois le même succès
    PRIMARY KEY (id_user, id_achievement),

    -- Les clés FOREIGN suivantes sont en "ON DELETE CASCADE"
    -- pour que lorsqu'un user ou achievement est supprimé,
    -- on supprime automatiquement la ligne user_achievement correspondante
    FOREIGN KEY (id_user) REFERENCES user_account(id) ON DELETE CASCADE,
    FOREIGN KEY (id_achievement) REFERENCES achievement(id) ON DELETE CASCADE
);