<?php

require_once ('QueryBuilder.php');
require_once ('AGrammar.php');
class Grammar extends AGrammar
{
    protected $operators = [];

    /**
     * The components that make up a select clause.
     *
     * @var array
     */
    protected $selectComponents = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
        'unions',
        'lock',
    ];

//Конечная постройка строки запроса
    public function compileSelect(QueryBuilder $query)
    {
       // var_dump($query->wheres);

        $original = $query->columns;

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

//concatenate - преобразует массив в строку
        $sql = trim($this->concatenate(
            $this->compileComponents($query))
        );

        $query->columns = $original;

      // var_dump($sql);
        return $sql;
    }
//компиляция частей массива в конечный результат
    protected function compileComponents(QueryBuilder $query)
    {
        $sql = [];

        echo "<pre>";
        // var_dump($query);
        echo "</pre>";

        foreach ($this->selectComponents as $component) {
           //var_dump($component);
            if (! is_null($query->$component)) {

                //var_dump($query->$component);

                $method = 'compile'.ucfirst($component);
                echo "<pre>";
               // var_dump($method);
                echo "</pre>";

                $sql[$component] = $this->$method($query, $query->$component);
                echo "<pre>";
               //var_dump($sql);
                echo "</pre>";

            }
        }
        echo "<pre>";
         //var_dump($sql);
        echo "</pre>";
        return $sql;
    }

    protected function compileColumns(QueryBuilder $query, $columns)
    {

        if (! is_null($query->aggregate)) {
            return;
        }

        $select = $query->distinct ? 'select distinct ' : 'select ';

        return $select.$this->columnize($columns);
    }



    protected function whereBasic(QueryBuilder $query, $where)
    {
       //var_dump($where['column']);
        $value = $this->parameter($where['value']);// return ?
       // var_dump($value);

        return array( $where['column'].' '.$where['operator'].' '.$value);
    }


    protected function compileFrom(QueryBuilder $query, $table)
    {
        return 'from user';
        //return 'from '.$this->wrapTable($table);
    }








    public function columnize(array $columns)
    {
        //var_dump($columns);
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    protected function concatenate($segments)
    {
       // var_dump($segments);
        return implode(' ', array_filter($segments, function ($value) {
          //  var_dump($value);
            return (string) $value !== '';
        }));
    }

    protected function compileWheres(QueryBuilder $query)
    {
        // Each type of where clauses has its own compiler function which is responsible
        // for actually creating the where clauses SQL. This helps keep the code nice
        // and maintainable since each clause has a very small method that it uses.
        if (is_null($query->wheres)) {
            return '';
        }

        // If we actually have some where clauses, we will strip off the first boolean
        // operator, which is added by the query builders for convenience so we can
        // avoid checking for the first clauses in each of the compilers methods.
       if (isset($query->wheres[0]) && count($query->wheres[0]) > 0) {
            $sql = $this->whereBasic($query,$query->wheres[0]);
           echo "<pre>";
          // var_dump($query->wheres[0]);
           echo "</pre>";
            return $this->concatenateWhereClauses($query, $sql);
        }
            //var_dump($query->wheres);

        return '';
    }

    protected function concatenateWhereClauses($query, $sql)
    {
       // var_dump($sql);
        $conjunction = 'where';

        return $conjunction.' '.$this->removeLeadingBoolean(implode('', $sql));
    }

    protected function removeLeadingBoolean($value)
    {

        return preg_replace('/and |or /i', '', $value, 1);
    }





            public function getOperators()
    {
        return $this->operators;
    }

}