<?php

class PegiDescriptorRepository {
    // ATTRIBUTES
    private PDO $pdo;

    // CONSTRUCTOR
    public function __construct() {
        // We get the unique instance of the database connection
        $this->pdo = DatabaseConnection::getInstance();
    }

    // METHODS

    // UTILITY METHOD : Convert a database row (associative array) to a PegiDescriptor object
    private function rowToPegiDescriptor(array $row) : PegiDescriptor {
        $pegiDescriptor = new PegiDescriptor($row['label']);
        $pegiDescriptor->setId($row['id']);
        return $pegiDescriptor;
    }

    // CREATE : Insert a new pegi_descriptor (normally after creating all the pegi descriptors you don't need to create others)
    public function insert(PegiDescriptor $pegiDescriptor) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "INSERT INTO pegi_descriptor (label) VALUES (:label);";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['label' => $pegiDescriptor->getLabel()]);
        // Update the PegiDescriptor object's id by getting it from "pdo->lastInsertId()"
        $pegiDescriptor->setId((int)$this->pdo->lastInsertId());
    }

    // READ [ID] : Find a pegi_descriptor by its id
    public function findById(int $id) : ?PegiDescriptor {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "SELECT * FROM pegi_descriptor WHERE id = :id;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute(['id' => $id]);
        // We now fetch the result of the execution and store it in $row
        $row = $statement->fetch();

        // If $row isn't empty we return a PegiDescriptor object using "rowToPegiDescriptor()", else we return null
        return $row ? $this->rowToPegiDescriptor($row) : null;
    }
    /** READ [ALL] : Find all PegiDescriptor from the database *
    * @return PegiDescriptor[]
    **/
    public function findAll() : array {
        // Because there is no named parameters (no custom parameters), we don't need to prepare the SQL string before
        $statement = $this->pdo->query("SELECT * FROM pegi_descriptor;");
        // We get all the rows resulted for our previous query
        $rows = $statement->fetchAll();

        $pegiDescriptors = [];
        // We create a PegiDescriptor for each element of the $rows array using "rowToPegiDescriptor()"
        foreach($rows as $row) {
            $pegiDescriptors[] = $this->rowToPegiDescriptor($row);
        }
        // Return an array of PegiDescriptor (if fetchAll() returned 0 row we will return an empty array)
        return $pegiDescriptors;
    }

    // UPDATE : Update the label of a pegi_descriptor (NOT MEANT FOR USE : use it only if miss-wrote)
    public function update(PegiDescriptor $pegiDescriptor) : void {
        // We prepare the SQL request string with named parameters (to avoid SQL injections)
        $sqlRequest = "UPDATE pegi_descriptor
                    SET label = :label
                    WHERE id = :id;";
        // We prepare the SQL request with the PDO connection, "this->pdo->prepare()" return a PDOStatement object
        $statement = $this->pdo->prepare($sqlRequest);
        // We execute the request
        $statement->execute([
            'label' => $pegiDescriptor->getLabel(),
            'id' => $pegiDescriptor->getId()
        ]);
    }
    // DELETE : Delete a pegi_descriptor from the database
    // (NOT MEANT FOR USE : once all the pegi_descriptor are on the DB you are not supposed to delete them)
    public function delete(int $id) : void {
        throw new LogicException("The deletion of a default pegi_descriptor is FORBIDDEN");
    }
}