<?php

class UserFavorite {
    // ATTRIBUTES
    private int $idUser;
    private int $idGame;
    private int $playtimeHours = 0;
    private DateTime $dateAdded;

    // CONSTRUCTOR
    public function __construct(int $idUser, int $idGame, int $playtimeHours = 0) {
        $this->dateAdded = new DateTime(); // Set the dateAdded to the current date and time

        $this->idUser = $idUser;
        $this->idGame = $idGame;
        $this->playtimeHours = $playtimeHours;
    }

    // GETTERS
    public function getIdUser(): int {return $this->idUser;}
    public function getIdGame(): int {return $this->idGame;}
    public function getPlaytimeHours(): int {return $this->playtimeHours;}
    public function getDateAdded(): DateTime {return $this->dateAdded;}

    // SETTERS
    public function setPlaytimeHours(int $playtimeHours): void {$this->playtimeHours = $playtimeHours;}
}
