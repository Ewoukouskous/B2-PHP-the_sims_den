<?php

class UserAchievement {
    // ATTRIBUTES
    private int $idUser;
    private int $idAchievement;
    private bool $isAchieved = false;

    // CONSTRUCTOR
    public function __construct(int $idUser, int $idAchievement) {
        $this->idUser = $idUser;
        $this->idAchievement = $idAchievement;
    }

    // GETTERS
    public function getIdUser(): ?int {return $this->idUser;}
    public function getIdAchievement(): int {return $this->idAchievement;}
    public function getIsAchieved(): bool {return $this->isAchieved;}

    // SETTERS
    public function setIsAchieved(bool $isAchieved) : void  {$this->isAchieved = $isAchieved;}

}