<?php

require_once __DIR__ . '/../Database/DatabaseConnection.php';
require_once __DIR__ . '/../Model/GameMedia.php';

class GameMediaRepository {
    // ATTRIBUTES
    private PDO $pdo;

    // CONSTRUCTOR
    public function __construct() {
        // We get the unique instance of the database connection
        $this->pdo = DatabaseConnection::getInstance();
    }

    // METHODS

    // UTILITY METHOD : Convert a database row (associative array) to a GameMedia object
    private function rowToGameMedia(array $row) : GameMedia {
        // We create a new GameMedia object with the data of the row (we use the constructor for the required attributes)
        $gameMedia = new GameMedia($row['file_path'],$row['id_game']);
        // Set the optional id attribute using the setter
        $gameMedia->setId($row['id']);
        return $gameMedia;
    }

    // CREATE : Insert a game_media in the database
    public function insert(GameMedia $gameMedia) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "INSERT INTO game_media (file_path, id_game) VALUES (:file_path, :id_game)";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the SQL request with the data of the game media given as parameter
        $statement->execute([
           'file_path' => $gameMedia->getFilePath(),
           'id_game' => $gameMedia->getIdGame()
        ]);
        // Update the Game object's id by getting it from "pdo->lastInsertId()"
        $gameMedia->setId((int)$this->pdo->lastInsertId());
    }

    // READ [ID] : Find a game_media by its id
    public function findById(int $id) : ?GameMedia {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM game_media WHERE id = :id;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id' => $id]);
        // We now fetch the result of the execution and store it in $row
        $row = $statement->fetch();

        // If $row isn't empty we return a GameMedia object using "rowToGameMedia()", else we return null
        return $row ? $this->rowToGameMedia($row) : null;
    }

    // READ [GAME ID] : Find all game_media associated to a game
    public function findByGameId(int $gameId) : array {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM game_media WHERE id_game = :id_game;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the SQL request with the data of the gameId given as parameter
        $statement->execute(['id_game' => $gameId]);
        // We get all the rows resulted for our previous query
        $rows = $statement->fetchAll();

        $gameMedias = [];
        // We create a GameMedia for each element of the $rows array using "rowToGameMedia()"
        foreach($rows as $row) {
            $gameMedias[] = $this->rowToGameMedia($row);
        }
        return $gameMedias;
    }
    // UPDATE : Update a game_media (only file_path)
    public function update(GameMedia $gameMedia) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "UPDATE game_media
                    SET file_path = :file_path
                    WHERE id = :id;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'file_path' => $gameMedia->getFilePath(),
            'id' => $gameMedia->getId()
        ]);
    }

    // DELETE : Delete a game_media from the database
    public function delete(int $id) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "DELETE FROM game_media WHERE id = :id";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id' => $id]);
    }

}