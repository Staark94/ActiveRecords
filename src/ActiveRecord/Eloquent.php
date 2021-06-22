<?php
namespace Staark\Support\ActiveRecord;

abstract class Eloquent implements ActiveRecords
{
    // The name of the "created at" column.
    const CREATED_AT = 'created_at';

    // The name of the "updated at" column.
    const UPDATED_AT = 'updated_at';

    /**
     * The connection name for the model.
     * @return mixed
     */
    protected static $connection;

    /**
     * The model's attributes.
     * @var array
     */
    protected $attributes = [];

    /**
     * The attributes that should be hidden for serialization.
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be visible in serialization.
     * @var array
     */
    protected $visible = [];

    /**
     * The primary key for the model.
     * @var null
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [];

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = "";

    /**
     * Indicates if the model should be timestamped.
     * @var array
     */
    public $timestamps = false;

    /**
     * Initialize database connection for this model
     * @param mixed $connection
     */
    public static function setConnection(
        string $driver = NULL, string $host = NULL, string $dbname = NULL,
        string $user = NULL, $pass = NULL, $port = '3307'
    ) {
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        if($driver == null) {
            $driver= "mysql:";
        } else {
            $driver = $driver . ":";
        }

        $dns = "{$driver}host={$host};dbname={$dbname};port={$port};charset=utf8";

        self::$connection = new \PDO($dns, $user, $pass, $options);
    }

    /**
     * Handle dynamic method calls into the model.
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->call($method, $args);
    }

    /**
     * Handle dynamic static method calls into the method.
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return (new static())->call($method, $args);
    }

    public function __set($name = "", $value = "")
    {
        $this->attributes[$name] = $value;
    }

    public function __get($name = "")
    {
        return $this->attributes[$name];
    }

    /**
     * Eloquent constructor.
     * @throws \Exception
     */
    public function __construct() {
        if(self::$connection == null) {
            throw new \Exception("Connection with database is not ininialized");
        }
    }

    public function save(): bool
    {
        if(empty($this->table) || !is_string($this->table)) {
            throw new \Exception("SQL Table not set in " . get_class(), 1);
        }

        $bindQuery = [];
        $bindValues = [];

        foreach ($this->attributes as $key => &$value) {
            if(empty($value)) continue;
            $bindQuery[] = $key . " = :" . $key;

            $bindValues[":" . $key] = $value;
        }

        $sqlQuery = "UPDATE `{$this->table}` SET ". implode(', ', $bindQuery) ." WHERE id = :id";
        $bindValues[":id"] = $this->id ?? 1;

        if($query = self::$connection->prepare($sqlQuery)) {
            try {
                $query->execute($bindValues);
            } catch (\PDOException $e) {
                var_dump($e->getMessage() . $e->getCode() . __LINE__);
            }
        }

        return false;
    }
    public function delete(): bool
    {
        // TODO: Implement delete() method.
    }
    public function insert(): void
    {
        if(empty($this->table) || !is_string($this->table)) {
            throw new \Exception("SQL Table not set in " . get_class(), 1);
        }

        $sqlQuery = "INSERT INTO `{$this->table}`(". $this->getFieldsName() .") VALUES (". implode(', ', $this->getFields()) .")";

        if($query = self::$connection->prepare($sqlQuery)) {
            try {
                //$this->query->execute($this->attributes);
                var_dump($query);
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage(), 1);
            }
        }

        $query = null;

        self::$connection = null;
    }
    public function update(): void
    {
        if(empty($this->table) || !is_string($this->table)) {
            throw new \Exception("SQL Table not set in " . get_class(), 1);
        }

        $bindQuery = [];
        $bindValues = [];

        foreach ($this->attributes as $key => &$value) {
            if(empty($value)) continue;
            $bindQuery[] = $key . " = :" . $key;

            $bindValues[":" . $key] = $value;
        }

        $sqlQuery = "UPDATE users SET ". implode(', ', $bindQuery) ." WHERE id = :id LIMIT 0,1";
        var_dump($sqlQuery);
    }

    /**
     * User or Model static query methods
     * @return mixed
     */
    static public function find(int $id): object
    {
        if(!$id || $id == -1) {
            throw new \Exception("User ID is not set or is empty", 1);
        }

        if($query = self::$connection->prepare("SELECT * FROM ". (new static())->getTable() ." WHERE id = :id" . (new static())->limit(0, 1))) {
            try {
                $query->execute([
                    'id' => $id
                ]);

                return $query->fetch(5);
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }
    static public function findAll(): array
    {
        if(self::$connection == null) {
            throw new \Exception("[SQLSTATE] MySQL Database not initialize", 1);
        }

        if( $query = self::$connection->prepare("SELECT * FROM " . (new static())->getTable()) ) {
            try {
                $query->execute();

                return $query->fetchAll(2);
            } catch (\PDOException $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }
    static public function first(): array
    {
        // TODO: Implement first() method.
    }
    static public function findById(int $id): ?self
    {
        if(!$id || $id == -1) {
            throw new \Exception("User ID is not set or is empty", 1);
        }

        $model = get_called_class();
        $findQuery = "SELECT * FROM ". (new static())->getTable() ." WHERE id = :id" . (new static())->limit(0, 1);
        $query = self::$connection->prepare($findQuery);

        try {
            $query->execute([':id' => $id]);
            $result = $query->fetch(5);

            return (new $model($result->id));
        } catch (\Exception $e) {
            // Should properly handle this.
        }

        return null;
    }
    static public function findByEmail(string $email): ?self
    {
        if(!$email || $email == "") {
            throw new \Exception("User ID is not set or is empty", 1);
        }

        $model = get_called_class();

        $findQuery = "SELECT * FROM ". (new static())->getTable() ." WHERE email = :email" . (new static())->limit(0, 1);
        $query = self::$connection->prepare($findQuery);

        try {
            $query->execute([':email' => $email]);
            $result = $query->fetch(5);

            return (new $model($result));
        } catch (\Exception $e) {
            // Should properly handle this.
        }

        return null;
    }

    /**
     * Database query from functions
     * @param string $sqlQuery
     * @return mixed
     */
    public function select(string $sqlQuery = ""): bool
    {
        // TODO: Implement findByEmail() method.
    }
    public function from(string $sqlQuery = ""): bool
    {
        // TODO: Implement findByEmail() method.
    }
    public function where(array $sql = []): bool
    {
        // TODO: Implement where() method.
    }
    public function limit(int $limit = -1, int $offset = -1): string
    {
        return " LIMIT {$limit}, {$offset}";
    }
    public function get(): void
    {
        // TODO: Implement findByEmail() method.
    }

    /**
     * Get the fillable attributes for the model.
     * @return array
     * @throws \Exception
     */
    public function getFields(): array
    {
        if(empty($this->getFillable()) || !is_array($this->getFillable())) {
            throw new \Exception("Failed to get informations of fileds array on " . get_class(), 1);
        }

        foreach ($this->getFillable() as $key => $val):
            $this->fields[$key] = ':' . $val;
        endforeach;

        return $this->fields;
    }

    /**
     * Get the fillable name attributes for the model.
     * @return string
     * @throws \Exception
     */
    public function getFieldsName(): string
    {
        if(empty($this->getFillable()) || !is_array($this->getFillable())) {
            throw new \Exception("Failed to get informations of fileds array on " . get_class(), 1);
        }

        return implode(', ', $this->getFillable());
    }

    /**
     * Get an table from the model
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get an attribute from the model.
     * @return mixed
     */
    public function getAttribute(string $key = ""): string
    {
        if(in_array($key, $this->attributes)) {
            return $key;
        }

        return "";
    }

    /**
     * Get the fillable attributes for the model.
     * @return array
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * Set the fillable attributes for the model.
     * @param array $fillable
     * @return $this
     */
    public function fillable(array $fillable): ?self
    {
        $this->fillable = $fillable;

        return $this;
    }

    /**
     * Determine if the given attribute may be mass assigned.
     * @param string $key
     * @return bool
     */
    public function isFillable(string $key): bool
    {
        if(in_array($key, $this->attributes)) {
            return true;
        }

        return empty($this->getFillable());
    }
}
