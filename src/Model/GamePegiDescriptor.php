<?php

class GamePegiDescriptor {
    // ATTRIBUTES
    private int $idGame;
    private int $idDescriptor;

    // CONSTRUCTOR
    public function __construct(int $idGame, int $idDescriptor) {
        $this->idGame = $idGame;
        $this->idDescriptor = $idDescriptor;
    }

    // GETTERS
    public function getIdGame() : int {return $this->idGame;}
    public function getIdDescriptor() : int {return $this->idDescriptor;}

    // SETTERS
    // No setters, as the attributes are set in the constructor and should not be changed afterwards.
}