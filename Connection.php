<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 29.12.18
 * Time: 8:12
 */

require_once ("DBConn.php");
require_once ("Grammar.php");

class Connection
{
    protected $pdo;

    /**
     * The active PDO connection used for reads.
     *
     * @var \PDO|\Closure
     */
    protected $readPdo;

    /**
     * The name of the connected database.
     *
     * @var string
     */
    protected $database;

    /**
     * The table prefix for the connection.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * The database connection configuration options.
     *
     * @var array
     */
    protected $config = [];


    public $pretending = false;

    protected $queryGrammar;

    protected $schemaGrammar;


    protected $fetchMode = PDO::FETCH_OBJ;

    /**
     * The number of active transactions.
     *
     * @var int
     */
    protected $transactions = 0;

    /**
     * Indicates if changes have been made to the database.
     *
     * @var int
     */
    protected $recordsModified = false;


    public function __construct()
    {
        $this->pdo = DbConn::getInstance();

    }




    public function table($table)
    {
        return $this->query()->from($table);
    }

    public function query()
    {
        return new QueryBuilder(
            $this, $this->getGrammar()
        );
    }


    public function select($query, $bindings = [])
    {
        var_dump($query);//сктрока запроса

        var_dump($bindings);//массив при использовании bindValues при inserte и тд
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                // true and false
                return [];
            }

           // var_dump($bindings);
            $statement = $this->prepared($this->getPdo()
                ->prepare($query));
          //  var_dump($statement);
            // dd($statement);/// PDOStatement с строкой запроса
            // dd($query);
            $this->bindValues($statement, $this->prepareBindings($bindings));
           // var_dump($statement->execute());
          var_dump(  $statement->execute() );
            return $statement->fetchAll();
        });
    }

    protected function prepared(PDOStatement $statement)
    {
        $statement->setFetchMode($this->fetchMode);


        return $statement;
    }


    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    public function prepareBindings(array $bindings)
    {
        //Создаем обьект грамара
        $grammar = $this->getGrammar();
        //  dd($grammar);


        //  dd($bindings);
        return $bindings;
    }

    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            // For update or delete statements, we want to get the number of rows affected
            // by the statement and return that back to the developer. We'll first need
            // to execute the statement and then we'll use PDO to fetch the affected.
            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            $this->recordsHaveBeenModified(
                ($count = $statement->rowCount()) > 0
            );

            return $count;
        });
    }


    public function recordsHaveBeenModified($value = true)
    {
        if (! $this->recordsModified) {
            $this->recordsModified = $value;
        }
    }






    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));



            return $statement->execute();
        });
    }


    //Функция для использования в SElect
    protected function run($query, $bindings, Closure $callback)
    {

            $result = $this->runQueryCallback($query, $bindings, $callback);

        // dd($result);
        return $result;
    }

        protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        $result = $callback($query, $bindings);



        return $result;
    }

    public function insert($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }






    public function pretending()
    {
        return $this->pretending === true;
    }

    public function getGrammar()
    {
        return new Grammar;
    }


    public function getPdo()
    {
        /*  if ($this->pdo instanceof Closure) {
              return $this->pdo = call_user_func($this->pdo);
          }*/

        return $this->pdo->getDb();
    }


}
