<?php

class GameMedia {
    // ATTRIBUTES
    private ?int $id = null;
    private string $filePath;
    private int $idGame;

    // CONSTRUCTOR
    public function __construct(string $filePath, int $idGame) {
        $this->filePath = $filePath;
        $this->idGame = $idGame;
    }

    // GETTERS
    public function getId() : ?int {return $this->id;}
    public function getFilePath() : string {return $this->filePath;}
    public function getIdGame() : int {return $this->idGame;}

    // SETTERS
    public function setId(int $id) : void {$this->id = $id;}
    public function setFilePath(string $filePath) : void {$this->filePath = $filePath;}
}