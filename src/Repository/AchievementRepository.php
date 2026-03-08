<?php

class AchievementRepository {
    // ATTRIBUTES
    private PDO $pdo;

    // CONSTRUCTOR
    public function __construct() {
        // We get the unique instance of the database connection
        $this->pdo = DatabaseConnection::getInstance();
    }
    // METHODS

    // UTILITY METHOD : Convert a database row (associative array) to an Achievement object
    private function rowToAchievement(array $row) : Achievement {
        $achievement = new Achievement($row['achievement_name'], $row['icon_path'], $row['achievement_desc']);
        $achievement->setId($row['id']);
        return $achievement;
    }

    // CREATE : Insert a new achievement in the database
    public function insert(Achievement $achievement) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "INSERT INTO achievement (achievement_name, achievement_desc, icon_path) 
                    VALUES (:achievement_name, :achievement_desc, :icon_path);";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'achievement_name' => $achievement->getAchievementName(),
            'achievement_desc' => $achievement->getAchievementDesc(),
            'icon_path' => $achievement->getIconPath()
        ]);
        // Update the Achievement object's id by getting it from "pdo->lastInsertId()"
        $achievement->setId((int)$this->pdo->lastInsertId());
    }

    // READ [ID] : Find an achievement by its id
    public function findById(int $id) : ?Achievement {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM achievement WHERE id = :id";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id' => $id]);
        // We now fetch the result of the execution and store it in $row
        $row = $statement->fetch();

        // If $row isn't empty we create a new Achievement object then return it
        return $row ? $this->rowToAchievement($row) : null;
    }
    // READ [NAME] : Find an achievement by its name
    public function findByName(string $achievement_name) : ?Achievement {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM achievement WHERE achievement_name = :achievement_name;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['achievement_name' => $achievement_name]);
        // We now fetch the result of the execution and store it in $row
        $row = $statement->fetch();

        // If $row isn't empty we create a new Achievement object then return it
        return $row ? $this->rowToAchievement($row) : null;
    }

    // READ [ALL] : Find all achievement from the database
    public function findAll() : array {
        // Because there is no named parameters (no custom parameters), we don't need to prepare the SQL string before
        $statement = $this->pdo->query("SELECT * FROM achievement;");
        // We get all the rows resulted for our previous query
        $rows = $statement->fetchAll();

        $achievements = [];
        // We create an Achievement object for each element of the $rows array
        foreach ($rows as $row) {
            $achievements[] = $this->rowToAchievement($row);
        }

        return $achievements;
    }

    // UPDATE : Update an achievement by its id
    public function update(Achievement $achievement) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "UPDATE achievement
                    SET achievement_name = :achievement_name,
                        achievement_desc = :achievement_desc,
                        icon_path = :icon_path
                    WHERE id = :id ;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'achievement_name' => $achievement->getAchievementName(),
            'achievement_desc' => $achievement->getAchievementDesc(),
            'icon_path' => $achievement->getIconPath(),
            'id' => $achievement->getId()
        ]);
    }

    // DELETE : Delete an achievement from the database
    public function delete(int $id) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "DELETE FROM achievement WHERE id = :id;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id' => $id]);
    }
}