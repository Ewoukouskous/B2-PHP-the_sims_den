<?php

class PegiDescriptor {
    // ATTRIBUTES
    private ?int $id = null;
    private string $label;

    // CONSTRUCTOR
    public function __construct( string $label) {$this->label = $label;}

    // GETTERS
    public function getId() : ?int {return $this->id;}
    public function getLabel() : string {return $this->label;}

    // SETTERS
    public function setId(int $id) : void {$this->id = $id;}
    public function setLabel(string $label) : void {$this->label = $label;}
}