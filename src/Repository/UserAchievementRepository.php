<?php

require_once __DIR__ . '/../Database/DatabaseConnection.php';
require_once __DIR__ . '/../Model/UserAchievement.php';

class UserAchievementRepository {
    // ATTRIBUTES
    private PDO $pdo;

    // CONSTRUCTOR
    public function __construct() {
        // We get the unique instance of the database connection
        $this->pdo = DatabaseConnection::getInstance();
    }

    // METHODS

    // UTILITY METHOD : Convert a database row (associative array) to a UserAchievement object
    private function rowToUserAchievement(array $row) : UserAchievement {
        return new UserAchievement($row['id_user'], $row['id_achievement'], new DateTime($row['unlocked_at']));
    }


    // CREATE : Insert a link row between UserAccount and Achievement ('user_achievement)
    public function insert(UserAchievement $userAchievement) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "INSERT INTO user_achievement (id_user, id_achievement, unlocked_at) 
                    VALUES (:id_user, :id_achievement, :unlocked_at);";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'id_user' => $userAchievement->getIdUser(),
            'id_achievement' => $userAchievement->getIdAchievement(),
            // We need to convert 'unlockedAt' into a DateTime because SQLite dont know DateTime type
            'unlocked_at' => $userAchievement->getUnlockedAt()->format('Y-m-d H:i:s')
        ]);
    }

    // READ [ID PAIR] : Find a user_achievement by its PRIMARY KEY pair (id_user, id_achievement)
    public function findByIdPair(int $idUser, int $idAchievement) : ?UserAchievement {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM user_achievement 
                    WHERE id_user = :id_user
                    AND id_achievement = :id_achievement;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'id_user' => $idUser,
            'id_achievement' => $idAchievement
        ]);
        // We now fetch the result of the execution and store it in $row
        $row = $statement->fetch();

        // If $row isn't empty we return a UserAchievement object using "rowToUserAchievement()", else we return null
        return $row ? $this->rowToUserAchievement($row) : null;
    }

    // READ [ALL BY USER_ID] : Find all user_achievement associated to a user's id
    public function findAllByUserId(int $idUser) : array {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM user_achievement WHERE id_user = :id_user;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id_user' => $idUser]);
        // We now fetch the result of the execution and store it in $rows
        $rows = $statement->fetchAll();

        $userAchievements = [];
        // We create a UserAchievement for each element of the $rows array using "rowToUserAchievement()"
        foreach($rows as $row) {
            $userAchievements[] = $this->rowToUserAchievement($row);
        }
        return $userAchievements;
    }

    // UPDATE : Update the is_achieved status to a user_achievement by using the id's pair
    public function update(UserAchievement $userAchievement) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "UPDATE user_achievement 
                    SET unlocked_at = :unlocked_at 
                    WHERE id_user = :id_user
                    AND id_achievement = :id_achievement;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            // We need to convert 'unlocked_at' into a string because SQLite dont know DateTime type
            'unlocked_at' => $userAchievement->getUnlockedAt()->format('Y-m-d H:i:s'),
            'id_user' => $userAchievement->getIdUser(),
            'id_achievement' => $userAchievement->getIdAchievement()
        ]);
    }

    // DELETE : Delete a user_achievement by its id's pair
    public function delete(int $idUser, int $idAchievement) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "DELETE FROM user_achievement 
                       WHERE id_user = :id_user 
                       AND id_achievement = :id_achievement;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'id_user' => $idUser,
            'id_achievement' => $idAchievement
        ]);
    }
}
