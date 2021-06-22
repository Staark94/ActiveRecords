<?php
namespace Staark\Support\ActiveRecord\Interfaces;

interface ActiveRecords
{
    /**
     * Set connection for model query
     * @return mixed
     */
    static function setConnection(
        string $driver = NULL, string $host = NULL, string $dbname = NULL,
        string $user = NULL, string $pass = NULL, string $port = '3307'
    );

    /**
     * User or Model store methods
     * @return mixed
     */
    public function save();
    public function delete();
    public function insert();
    public function update();

    /**
     * User or Model static query methods
     * @return mixed
     */
    static public function find(int $id);
    static public function findAll();
    static public function first();
    static public function findById(int $id);
    static public function findByEmail(string $email);

    /**
     * Database query from functions
     * @param string $sqlQuery
     * @return mixed
     */
    public function select(string $sqlQuery = "");
    public function from(string $sqlQuery = "");
    public function where(array $sqlQuery = []);
    public function limit(int $limit = -1, int $offset = -1);
    public function get();
}
