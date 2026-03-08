<?php
class Game {
    // ATTRIBUTES
    private ?int $id = null; // Is equal to null by default, because when we create a game (to INSERT it after) we don't know his id yet
    private string $gameName;
    private float $price;
    private GameType $gameType;
    private string $gameDesc;
    private string $imageHeroPath;
    private string $imageTitlePath;
    private int $favoritesNumber = 0;
    private PegiAge $pegiAge;

    // CONSTRUCTOR
    public function __construct(string $gameName, float $price, GameType $gameType, string $gameDesc, string $imageHeroPath, string $imageTitlePath, PegiAge $pegiAge ) {
        // Assignation of data given by the parameters
        $this->gameName = $gameName;
        $this->price = $price;
        $this->gameType = $gameType;
        $this->gameDesc = $gameDesc;
        $this->imageHeroPath = $imageHeroPath;
        $this->imageTitlePath = $imageTitlePath;
        $this->pegiAge = $pegiAge;
    }

    // GETTERS
    public function getId() : ?int {return $this->id;}
    public function getGameName() : string {return $this->gameName;}
    public function getPrice() : float {return $this->price;}
    public function getGameType() : GameType {return $this->gameType;}
    public function getGameDesc() : string {return $this->gameDesc;}
    public function getImageHeroPath() : string {return $this->imageHeroPath;}
    public function getImageTitlePath() : string {return $this->imageTitlePath;}
    public function getFavoritesNumber() : int {return $this->favoritesNumber;}
    public function getPegiAge() : PegiAge {return $this->pegiAge;}

    // SETTERS
    public function setId(int $id): void {$this->id = $id;}
    public function setGameName(string $gameName): void {$this->gameName = $gameName;}
    public function setPrice(float $price): void {$this->price = $price;}
    public function setGameType(GameType $gameType): void {$this->gameType = $gameType;}
    public function setGameDesc(string $gameDesc): void {$this->gameDesc = $gameDesc;}
    public function setImageHeroPath(string $imageHeroPath): void {$this->imageHeroPath = $imageHeroPath;}
    public function setImageTitlePath(string $imageTitlePath): void {$this->imageTitlePath = $imageTitlePath;}
    public function setFavoriteNumber(int $favoritesNumber): void {$this->favoritesNumber = $favoritesNumber;}
    public function setPegiAge(PegiAge $pegiAge): void {$this->pegiAge = $pegiAge;}
}
