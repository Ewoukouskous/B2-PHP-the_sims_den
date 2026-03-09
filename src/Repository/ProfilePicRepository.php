<?php

class ProfilePicRepository {
    // ATTRIBUTES
    private PDO $pdo;

    // CONSTRUCTOR
    public function __construct() {
        // We get the unique instance of the database connection
        $this->pdo = DatabaseConnection::getInstance();
    }

    // METHODS
    // CREATE : Insert a new profile_pic in the database
    public function insert(ProfilePic $profilePic) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "INSERT INTO profile_pic (picture_path) VALUES (:picture_path);";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['picture_path' => $profilePic->getPicturePath()]);

        // Update the ProfilePic object's id by getting it from "pdo->lastInsertId()"
        $profilePic->setId((int)$this->pdo->lastInsertId());
    }

    // READ [ID] : Find a profile_pic by its id
    public function findById(int $id) : ?ProfilePic {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM profile_pic WHERE id = :id;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id' => $id]);
        // We now fetch the result of the execution and store it in $row
        $row = $statement->fetch();
        // If $row isn't empty we create a new ProfilePic object then return it
        if ($row) {
            $profilePic = new ProfilePic($row['picture_path']);
            $profilePic->setId($row['id']);
            return $profilePic;
        }
        return null;
    }

    // READ [ALL] : Find all profile_pic from the database
    public function findAll() : array {
        // Because there is no named parameters (no custom parameters), we don't need to prepare the SQL string before
        $statement = $this->pdo->query("SELECT * FROM profile_pic;");
        // We get all the rows resulted for our previous query
        $rows = $statement->fetchAll();

        $profilePics = [];
        // We create a ProfilePic object for each element of the $rows array
        foreach($rows as $row) {
            $profilePic = new ProfilePic($row['picture_path']);
            $profilePic->setId($row['id']);
            $profilePics[] = $profilePic;
        }

        return $profilePics;
    }

    // UPDATE : Modify profile_pic picture (NOT MEANT FOR USE : this method is here just in case we need it later)
    public function update(ProfilePic $profilePic) : void {
        throw new LogicException("The modification of the default profile pics is FORBIDDEN");
    }

    // DELETE : Delete a profile_pic (NOT MEANT FOR USE : right now there is 4 profile pic owned by the website that user can choose for their profile)
    public function delete(int $id) : void {
        throw new LogicException("The deletion of the default profile pics is FORBIDDEN");
    }
}