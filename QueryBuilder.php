<?php

require_once ('Connection.php');
class QueryBuilder
{


    public $connection;


    public $grammar;



    /**
     * The current query value bindings.
     *
     * @var array
     */
    public $bindings = [
        'select' => [],
        'join'   => [],
        'where'  => [],
        'having' => [],
        'order'  => [],
        'union'  => [],
    ];

    /**
     * An aggregate function and column to be run.
     *
     * @var array
     */
    public $aggregate;

    /**
     * The columns that should be returned.
     *
     * @var array
     */
    public $columns;

    /**
     * Indicates if the query returns distinct results.
     *
     * @var bool
     */
    public $distinct = false;

    /**
     * The table which the query is targeting.
     *
     * @var string
     */
    public $from = 'user';

    /**
     * The table joins for the query.
     *
     * @var array
     */
    public $joins;
    public $unions;

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    public $wheres = [];

    /**
     * The groupings for the query.
     *
     * @var array
     */
    public $groups;

    /**
     * The having constraints for the query.
     *
     * @var array
     */
    public $havings ;

    /**
     * The orderings for the query.
     *
     * @var array
     */
    public $orders;

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    public $limit;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    public $offset;


    /**
     * Indicates whether row locking is being used.
     *
     * @var string|bool
     */
    public $lock;

    /**
     * All of the available clause operators.
     *
     * @var array
     */
       public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    /**
     * Whether use write pdo for select.
     *
     * @var bool
     */
    public $useWritePdo = false;


    public function __construct(Connection $connection,
                                Grammar $grammar = null)
    {
        $this->connection = $connection;
        $this->grammar = $grammar ?: $connection->getGrammar();

    }

    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();

        return $this;
    }



    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {

        /*list($value, $operator) = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() == 2
        );*/


       // $type = 'Basic';
// compact -
        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );
      //var_dump($this->wheres);

        //Добавление в свойство binding значение velue(id)
        if ( $value ) {
            //dd($value);
            $this->addBinding($value, 'where');
        }
        //var_dump($this);
        return $this;
    }


    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }


    public function addBinding($value, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            
            $this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
        } else {
            // dd(21);
          //  echo "ELSE";
            $this->bindings[$type][] = $value;

        }
        // dd($this);
        return $this;
    }

    public function get($columns = ['*'])
    {
        $original = $this->columns;


        if (is_null($original)) {
            $this->columns = $columns;
        }

        $results = $this->runSelect();
        // dd($this);
     //   $this->columns = $original;
        //var_dump($results);
        return new Collection($results);
    }


    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    public function runSelect()
    {
        //var_dump($this->toSql());
       // var_dump($this->toSql());echo "<br>";
        //var_dump($this->getBindings());
        return $this->connection->select(
            $this->toSql(), $this->getBindings()
        );
    }

    public function toSql()
    {
        // dd($this->grammar->compileSelect($this));
        return $this->grammar->compileSelect($this);
    }


    public function getBindings()
    {
        //var_dump($this->bindings['where']);
        //  dd($this->bindings);получаем where = id(2)
        return $this->bindings['where'];
    }


    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) &&
            ! in_array($operator, ['=', '<>', '!=']);
    }

    /**
     * Determine if the given operator is supported.
     *
     * @param  string  $operator
     * @return bool
     */
    protected function invalidOperator($operator)
    {
        return ! in_array(strtolower($operator), $this->operators, true) &&
            ! in_array(strtolower($operator), $this->grammar->getOperators(), true);
    }



    public function find($id, $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }









    public function from($table)
    {
        $this->from = $table;

        return $this;
    }

}