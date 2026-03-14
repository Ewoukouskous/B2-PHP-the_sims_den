<?php

require_once __DIR__ . '/../Database/DatabaseConnection.php';
require_once __DIR__ . '/../Model/GamePegiDescriptor.php';

class GamePegiDescriptorRepository {
    // ATTRIBUTES
    private PDO $pdo;

    // CONSTRUCTOR
    public function __construct() {
        // We get the unique instance of the database connection
        $this->pdo = DatabaseConnection::getInstance();
    }

    // METHODS

    private function rowToGamePegiDescriptor(array $row) : GamePegiDescriptor {
        return new GamePegiDescriptor((int)$row['id_game'], (int)$row['id_descriptor']);
    }

    // CREATE : Insert a link row between game and pegi_descriptor (game_pegi_descriptor) in the database
    public function insert(GamePegiDescriptor $gamePegiDescriptor) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "INSERT INTO game_pegi_descriptor (id_game, id_descriptor) 
                    VALUES (:id_game, :id_descriptor);";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'id_game' => $gamePegiDescriptor->getIdGame(),
            'id_descriptor' => $gamePegiDescriptor->getIdDescriptor()
        ]);
    }

    // READ [ID PAIR] : Find a game_pegi_descriptor by its PRIMARY KEY pair (id_game, id_descriptor)
    public function findByIdPair(int $idGame, int $idDescriptor) : ?GamePegiDescriptor {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM game_pegi_descriptor 
                    WHERE id_game = :id_game
                    AND id_descriptor = :id_descriptor;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'id_game' => $idGame,
            'id_descriptor' => $idDescriptor
        ]);
        // We now fetch the result of the execution and store it in $row
        $row = $statement->fetch();
        // If $row isn't empty we return a GamePegiDescriptor object, else we return null
        return $row ? new GamePegiDescriptor($idGame, $idDescriptor) : null;
    }

    // READ [GAME ID] : Find all game_pegi_descriptor rows for one game
    public function findByGameId(int $idGame) : array {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM game_pegi_descriptor WHERE id_game = :id_game;";
        // We prepare the SQL request with the PDO connection
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id_game' => $idGame]);
        // We get all the rows resulted for our previous query
        $rows = $statement->fetchAll();

        $gamePegiDescriptors = [];
        foreach($rows as $row) {
            $gamePegiDescriptors[] = $this->rowToGamePegiDescriptor($row);
        }

        return $gamePegiDescriptors;
    }

    // UPDATE : Update a game_pegi_descriptor (NOT MEANT FOR USE : The id pair is a PRIMARY KEY, their not meant to be modified)
    public function update(int $idGame, int $idDescriptor) : void {
        throw new LogicException("The update of game_pegi_descriptor is FORBIDDEN");
    }

    // DELETE : Delete a game_pegi_descriptor association
    public function delete(int $idGame, int $idDescriptor) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "DELETE FROM game_pegi_descriptor 
                    WHERE id_game = :id_game 
                    AND id_descriptor = :id_descriptor;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'id_game' => $idGame,
            'id_descriptor' => $idDescriptor
        ]);
    }
}