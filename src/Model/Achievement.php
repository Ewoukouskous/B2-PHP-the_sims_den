<?php

class Achievement {
    // ATTRIBUTES
    private ?int $id = null; // Is equal to null by default, because when we create an achievement (to INSERT it after) we don't know his id yet
    private string $achievementName;
    private ?string $achievementDesc = null;
    private string $iconPath;
    private ?DateTime $unlockedAt = null;

    // CONSTRUCTOR
    public function __construct(string $achievementName, string $iconPath, ?string $achievementDesc = null ) {
        $this->achievementName = $achievementName;
        $this->iconPath = $iconPath;
        $this->achievementDesc = $achievementDesc;
    }

    // UTILITY METHOD : Will allow us to simply know if the success has been unlocked by the user
    public function isUnlocked() : bool {
        return $this->unlockedAt !== null;
    }

    // GETTERS
    public function getId(): ?int {return $this->id;}
    public function getAchievementName(): string {return $this->achievementName;}
    public function getAchievementDesc(): ?string {return $this->achievementDesc;}
    public function getIconPath(): string {return $this->iconPath;}
    public function getUnlockedAt(): ?DateTime {return $this->unlockedAt;}

    // SETTERS
    public function setId(int $id): void {$this->id = $id;}
    public function setAchievementName(string $achievementName): void {$this->achievementName = $achievementName;}
    public function setAchievementDesc(?string $achievementDesc): void {$this->achievementDesc = $achievementDesc;}
    public function setIconPath(string $iconPath): void {$this->iconPath = $iconPath;}
    public function setUnlockedAt(?DateTime $unlockedAt): void {$this->unlockedAt = $unlockedAt;}

}