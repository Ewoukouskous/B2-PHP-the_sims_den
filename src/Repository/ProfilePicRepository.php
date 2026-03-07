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
        $sqlRequest = "INSERT INTO profile_pic (picture) VALUES (:picture);";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['picture' => $profilePic->getPicture()]);

        // Update the ProfilePic object's id by getting it from "pdo->lastInsertId()"
        $profilePic->setId((int)$this->pdo->lastInsertId());
    }

    // READ [ID] : Find a profile_pic by its id
    public function findById(int $id) : ?ProfilePic {
        $sqlRequest = "SELECT * FROM profile_pic WHERE id = :id;";
        $statement = $this->pdo->prepare($sqlRequest);
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();
        if ($row) {
            $profilePic = new ProfilePic($row['picture']);
            $profilePic->setId($row['id']);
            return $profilePic;
        }
        return null;
    }

    // READ [ALL] : Find all profile_pic from the database
    public function findAll() : array {
        $statement = $this->pdo->query("SELECT * FROM profile_pic;");
        $rows = $statement->fetchAll();

        $profilePics = [];
        foreach($rows as $row) {
            $profilePic = new ProfilePic($row['picture']);
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