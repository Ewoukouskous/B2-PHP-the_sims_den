<?php

class UserAchievement {
    // ATTRIBUTES
    private int $idUser;
    private int $idAchievement;
    private DateTime $unlockedAt;

    // CONSTRUCTOR
    public function __construct(int $idUser, int $idAchievement, ?DateTime $unlockedAt = null) {
        $this->idUser = $idUser;
        $this->idAchievement = $idAchievement;
        $this->unlockedAt = $unlockedAt ?? new DateTime();
    }

    // GETTERS
    public function getIdUser(): int {return $this->idUser;}
    public function getIdAchievement(): int {return $this->idAchievement;}
    public function getUnlockedAt(): DateTime {return $this->unlockedAt;}


}