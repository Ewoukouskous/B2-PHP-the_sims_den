<?php

class UserAccount {
    // ATTRIBUTES
    private ?int $id = null; // Is equal to null by default, because when we create a user (to INSERT if after) we don't know his id yet
    private UserRole $userRole = UserRole::USER;
    private string $username;
    private string $email;
    private string $passwordHash;

    private DateTime $dateJoined;
    private ?DateTime $lastLogin = null;

    private int $idProfilePic = 1; // Default profile picture is the one with id = 1 in the database (the "default" profile picture)

    // CONSTRUCTOR
    public function __construct(string $username, string $email, string $passwordHash) {
        // Add the current Date and Time to dateJoined
        $this->dateJoined = new DateTime();

        // Assignation of data given by the parameters
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
    }

    // GETTERS
    public function getId(): ?int {return $this->id;}
    public function getUserRole(): UserRole {return $this->userRole;}
    public function getUsername(): string {return $this->username;}
    public function getEmail(): string {return $this->email;}
    public function getPasswordHash(): string {return $this->passwordHash;}
    public function getDateJoined(): DateTime {return $this->dateJoined;}
    public function getLastLogin(): ?DateTime {return $this->lastLogin;}
    public function getIdProfilePic(): int {return $this->idProfilePic;}


    // SETTERS
    public function setId(int $id): void {$this->id = $id;}

    public function setUserRole(UserRole $userRole): void {$this->userRole = $userRole;}

    public function setUsername(string $username): void {$this->username = $username;}

    public function setEmail(string $email): void {$this->email = $email;}

    public function setPasswordHash(string $passwordHash): void {$this->passwordHash = $passwordHash;}

    public function setDateJoined(DateTime $dateJoined): void {$this->dateJoined = $dateJoined;}

    public function setLastLogin(?DateTime $lastLogin): void {$this->lastLogin = $lastLogin;}

    public function setIdProfilePic(int $idProfilePic): void {$this->idProfilePic = $idProfilePic;}

}
