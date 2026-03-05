<?php

class Achievement {
    // ATTRIBUTES
    private ?int $id = null; // Is equal to null by default, because when we create an achievement (to INSERT it after) we don't know his id yet
    private string $achievementName;
    private ?string $achievementDesc = null;

    // CONSTRUCTOR
    public function __construct(string $achievementName, ?string $achievementDesc = null) {
        $this->achievementName = $achievementName;
        $this->achievementDesc = $achievementDesc;
    }

    // GETTERS
    public function getId(): ?int {return $this->id;}
    public function getAchievementName(): string {return $this->achievementName;}
    public function getAchievementDesc(): ?string {return $this->achievementDesc;}

    // SETTERS
    public function setId(int $id): void {$this->id = $id;}
    public function setAchievementName(string $achievementName): void {$this->achievementName = $achievementName;}
    public function setAchievementDesc(?string $achievementDesc): void {$this->achievementDesc = $achievementDesc;}

}