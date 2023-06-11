<?php
class DB {
    private $connect;
    private static $credentials = null;

    public function __construct(string $host, string $database, string $username, string $password, int $port = 3306) {
        self::$credentials = [
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password
        ];
        $this->connect();
    }

    private function connect() {
        $this->connect = new PDO(
            'mysql:host=' . DB::$credentials['host'] .
            ';dbname=' . DB::$credentials['database'] .
            ';port=' . DB::$credentials['port'],
            DB::$credentials['username'],
            DB::$credentials['password']
        );
        $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connect->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
        $this->connect->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    public function rawQuery(string $query) {
        if (DB::$credentials != null) {
            $query = $this->connect->query($query);
            return $query;
        } else {
            throw new Exception("Not connected to database server");
            return false;
        }
    }

    public function preparedQuery(string $query, array $parameters) {
        if (DB::$credentials != null) {
            $query = $this->connect->prepare($query);
            $query->execute($parameters);
            return $query;
        } else {
            throw new Exception("Not connected to database server");
            return false;
        }
    }
}
?>