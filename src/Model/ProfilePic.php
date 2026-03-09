<?php

class ProfilePic {
    // ATTRIBUTES
    private ?int $id = null; // Is equal to null by default, because when we create a profile_pic (to INSERT it after) we don't know his id yet
    private string $picture_path; // $picture has the 'string' type because 'bytes' does not exist in PHP

    public function __construct(string $picture) {
        $this->picture_path = $picture;
    }

    // GETTERS
    public function getId(): ?int {return $this->id;}
    public function getPicturePath(): string {return $this->picture_path;}

    // SETTERS
    public function setId(int $id): void {$this->id = $id;}
}
