<?php

require_once __DIR__ . '/../Database/DatabaseConnection.php';
require_once __DIR__ . '/../Model/UserFavorite.php';

class UserFavoriteRepository {
    // ATTRIBUTES
    private PDO $pdo;

    // CONSTRUCTOR
    public function __construct() {
        // We get the unique instance of the database connection
        $this->pdo = DatabaseConnection::getInstance();
    }

    // METHODS

    // UTILITY METHOD : Convert a database row (associative array) to a UserFavorite object
    private function rowToUserFavorite(array $row) : UserFavorite {
        // We create a new UserFavorite object with the data of the row (we use the constructor for the required attributes)
        $userFavorite = new UserFavorite($row['id_user'], $row['id_game'], $row['playtime_hours']);
        // Set the optional dateAdded attribute using the setter
        $userFavorite->setDateAdded(new DateTime($row['date_added']));
        return $userFavorite;
    }


    // CREATE : Insert a link row between user_account and game (user_favorite) in the database
    public function insert(UserFavorite $userFavorite) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "INSERT INTO user_favorite (id_user, id_game, playtime_hours, date_added) 
                    VALUES (:id_user, :id_game, :playtime_hours, :date_added);";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'id_user' => $userFavorite->getIdUser(),
            'id_game' => $userFavorite->getIdGame(),
            'playtime_hours' => $userFavorite->getPlaytimeHours(),
            // We convert the DATETIME into a STRING because SQLite use string
            'date_added' => $userFavorite->getDateAdded()->format('Y-m-d H:i:s')
        ]);
    }

    // READ [ID PAIR] : Find a user_favorite by its PRIMARY KEY pair (id_user, id_game)
    public function findByIdPair(int $idUser, int $idGame) : ?UserFavorite {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM user_favorite 
                    WHERE id_user = :id_user
                    AND id_game = :id_game;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'id_user' => $idUser,
            'id_game' => $idGame
        ]);
        // We now fetch the result of the execution and store it in $row
        $row = $statement->fetch();

        // If $row isn't empty we return a UserFavorite object using "rowToUserFavorite()", else we return null
        return $row ? $this->rowToUserFavorite($row) : null;
    }

    // READ [ALL BY USER_ID] : Find all user_favorite associated to a user_account
    public function findAllByUserId(int $idUser) : array {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM user_favorite WHERE id_user = :id_user;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id_user' => $idUser]);
        // We now fetch the result of the execution and store it in $rows
        $rows = $statement->fetchAll();

        $userFavorites = [];
        // We create a UserFavorite for each element of the $rows array using "rowToUserFavorite()"
        foreach($rows as $row) {
            $userFavorites[] = $this->rowToUserFavorite($row);
        }
        return $userFavorites;
    }

    // READ [ALL BY GAME_ID] : Find all user_favorite associated to a game
    public function findAllByGameId(int $idGame) : array {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM user_favorite WHERE id_game = :id_game;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id_game' => $idGame]);
        // We now fetch the result of the execution and store it in $rows
        $rows = $statement->fetchAll();

        $userFavorites = [];
        // We create a UserFavorite for each element of the $rows array using "rowToUserFavorite()"
        foreach($rows as $row) {
            $userFavorites[] = $this->rowToUserFavorite($row);
        }
        return $userFavorites;
    }

    // READ [COUNT BY USER_ID] : Return the amount of favorite that a user have
    public function countByUserId(int $idUser) : int {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT COUNT(*) FROM user_favorite WHERE id_user = :id_user;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id_user' => $idUser]);
        // We now return the content of the first column (contain the count) of the execution
        return (int)$statement->fetchColumn();
    }

    // UPDATE : Update a user_favorite from the database (only the playtime_hours is modifiable)
    public function update(UserFavorite $userFavorite) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "UPDATE user_favorite
                    SET playtime_hours = :playtime_hours
                    WHERE id_user = :id_user
                    AND id_game = :id_game;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
           'playtime_hours' => $userFavorite->getPlaytimeHours(),
           'id_user' => $userFavorite->getIdUser(),
           'id_game' => $userFavorite->getIdGame()
        ]);
    }

    // DELETE : Delete a user_favorite from the database
    public function delete(int $idUser, int $idGame) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "DELETE FROM user_favorite 
                    WHERE id_user = :id_user
                    AND id_game = :id_game;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
           'id_user' => $idUser,
           'id_game' => $idGame
        ]);
    }
}