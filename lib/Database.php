<?php

/**
 * Class to manage database interaction
 *
 * @author Christopher Bitler
 */
class Database {
    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $host;

    /** @var string */
    private $database;

    /** @var PDO */
    private $databaseConnection;

    /**
     * @param string $username
     * @param string $password
     * @param string $host
     * @param string $database
     */
    public function __construct(
        string $username,
        string $password,
        string $host,
        string $database
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->database = $database;
    }

    /**
     * Connect to the database using provided credentials
     */
    public function connect()
    {
        $this->databaseConnection = new PDO(
            'mysql:host=' . $this->host . ';dbname=' . $this->database,
            $this->username,
            $this->password
        );
    }

    /**
     * Query the database and retrieve the results of the query
     *
     * @param string $query SQL query
     * @param array $parameters The parameters for the SQL query
     * @return array Array of rows that fit the query results
     */
    public function query(string $query, array $parameters)
    {
        $statement = $this->databaseConnection->prepare($query);

        foreach ($parameters as $key => $value) {
            $statement->bindParam($key, $value);
        }

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update the database (INSERT, UPDATE, DELETE, etc)
     * @param string $query SQL query
     * @param array $parameters The parameters for the SQL update statement
     * @return bool True if the query succeeded, false otherwise.
     */
    public function update(string $query, array $parameters)
    {
        $statement = $this->databaseConnection->prepare($query);

        foreach ($parameters as $key => $value) {
            $statement->bindParam($key, $value);
        }

        return $statement->execute();
    }
}
