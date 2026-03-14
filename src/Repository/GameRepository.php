<?php

require_once __DIR__ . '/../Database/DatabaseConnection.php';
require_once __DIR__ . '/../Model/Game.php';
require_once __DIR__ . '/../Enum/GameType.php';
require_once __DIR__ . '/../Enum/PegiAge.php';

class GameRepository {
    // ATTRIBUTES
    private PDO $pdo;

    // CONSTRUCTOR
    public function __construct() {
        $this->pdo = DatabaseConnection::getInstance();
    }

    // METHODS

    // UTILITY METHOD : Convert a database row (associative array) to a Game object
    private function rowToGame(array $row) : Game {
        // Convert the string SQLite data into the correct ENUM type
        $gameType = GameType::from($row['game_type']);
        $pegiAge = PegiAge::from($row['pegi_age']);
        // We create a new Game object with the data of the row (we use the constructor for the required attributes)
        $game = new Game($row['game_name'],$row['price'], $gameType, $row['game_desc'],
            $row['image_hero_path'], $row['image_title_path'], $pegiAge );
        // Set the optional attributes using the setters (id and favoritesNumber)
        $game->setId($row['id']);
        $game->setFavoriteNumber($row['favorites_number']);

        return $game;
    }

    // CREATE : Insert a game in the database
    public function insert(Game $game) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "INSERT INTO game (game_name, price, game_type, game_desc, image_hero_path, image_title_path, pegi_age)
                    VALUES (:game_name, :price, :game_type, :game_desc, :image_hero_path, :image_title_path, :pegi_age);";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the SQL request with the data of the game given as parameter
        $statement->execute([
            'game_name' => $game->getGameName(),
            'price' => $game->getPrice(),
            // Get the string value of the ENUM, SQLite use string as ENUM
            'game_type' => $game->getGameType()->value,
            'game_desc' => $game->getGameDesc(),
            'image_hero_path' => $game->getImageHeroPath(),
            'image_title_path' => $game->getImageTitlePath(),
            // Get the string value of the ENUM, SQLite use string as ENUM
            'pegi_age' => $game->getPegiAge()->value
        ]);
        // Update the Game object's id by getting it from "pdo->lastInsertId()"
        $game->setId((int)$this->pdo->lastInsertId());
    }
    // READ [ID] : Find a game by its id
    public function findById(int $id) : ?Game {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM game WHERE id = :id;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the SQL request with the data of the game id given as parameter
        $statement->execute(['id' => $id]);
        // We now fetch the result of the execution and store it in $row
        $row = $statement->fetch();

        // If $row isn't empty we return a Game object using "rowToGame()", else we return null
        return $row ? $this->rowToGame($row) : null;
    }
    // READ [NAME] : Find a game by its name
    public function findByName(string $name) : ?Game {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM game WHERE game_name = :game_name;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the SQL request with the data of the game name given as parameter
        $statement->execute(['game_name' => $name]);
        // We now fetch the result of the execution and store it in $row
        $row = $statement->fetch();

        // If $row isn't empty we return a Game object using "rowToGame()", else we return null
        return $row ? $this->rowToGame($row) : null;
    }

    // READ [TYPE] : Get all game associated with the specified GameType
    public function findByGameType(GameType $gameType) : array {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM game WHERE game_type = :game_type;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the SQL request with the data of the gameType given as parameter
        $statement->execute(['game_type' => $gameType->value]);
        // We get all the rows resulted for our previous query
        $rows = $statement->fetchAll();

        $games = [];
        // We create a Game object for each element of the $rows array
        foreach($rows as $row) {
            $games[] = $this->rowToGame($row);
        }
        return $games;
    }

    // READ [PEGI AGE] : Get all game associated with the specified PegiAge
    public function findByPegiAge(PegiAge $pegiAge) : array {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM game WHERE pegi_age = :pegi_age;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the SQL request with the data of the pegi age given as parameter
        $statement->execute(['pegi_age' => $pegiAge->value]);
        // We get all the rows resulted for our previous query
        $rows = $statement->fetchAll();

        $games = [];
        // We create a Game object for each element of the $rows array
        foreach($rows as $row) {
            $games[] = $this->rowToGame($row);
        }
        return $games;
    }

    // READ [ALL] : Get all game from the database
    public function findAll() : array {
        // Because there is no named parameters (no custom parameters), we don't need to prepare the SQL string before
        $statement = $this->pdo->query("SELECT * FROM game;");
        // We get all the rows resulted for our previous query
        $rows = $statement->fetchAll();

        $games = [];
        // We create a Game object for each element of the $rows array
        foreach($rows as $row) {
            $games[] = $this->rowToGame($row);
        }
        return $games;
    }
    // UPDATE : Update a game from the database by its id
    public function update(Game $game) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "UPDATE game
                    SET game_name = :game_name,
                        price = :price,
                        game_type = :game_type,
                        game_desc = :game_desc,
                        image_hero_path = :image_hero_path,
                        image_title_path = :image_title_path,
                        favorites_number = :favorites_number,
                        pegi_age = :pegi_age
                    WHERE id = :id;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'game_name' => $game->getGameName(),
            'price' => $game->getPrice(),
            // Convert GameType ENUM into a string for SQLite
            'game_type' => $game->getGameType()->value,
            'game_desc' => $game->getGameDesc(),
            'image_hero_path' => $game->getImageHeroPath(),
            'image_title_path' => $game->getImageTitlePath(),
            'favorites_number' => $game->getFavoritesNumber(),
            // Convert PegiAge ENUM into a string for SQLite
            'pegi_age' => $game->getPegiAge()->value,
            'id' => $game->getId()
        ]);
    }

    // DELETE : Delete a game from the database by its id
    public function delete(int $id) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "DELETE FROM game WHERE id = :id;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id' => $id]);
    }
}