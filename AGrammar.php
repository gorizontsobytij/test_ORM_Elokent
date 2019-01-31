<?php

require_once ('Collection.php');
abstract class AGrammar
{


    protected $tablePrefix = '';

    public function wrapArray(array $values)
    {
        return array_map([$this, 'wrap'], $values);
    }


    public function wrapTable($table)
    {
        if (! $this->isExpression($table)) {
            return $this->wrap($this->tablePrefix.$table, true);
        }

        return $this->getValue($table);
    }


    public function wrap($value, $prefixAlias = false)
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        // If the value being wrapped has a column alias we will need to separate out
        // the pieces so we can wrap each of the segments of the expression on it
        // own, and then joins them both back together with the "as" connector.
        if (strpos(strtolower($value), ' as ') !== false) {
            return $this->wrapAliasedValue($value, $prefixAlias);
        }

        return $this->wrapSegments(explode('.', $value));
    }

    protected function wrapAliasedValue($value, $prefixAlias = false)
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        // If we are wrapping a table we need to prefix the alias with the table prefix
        // as well in order to generate proper syntax. If this is a column of course
        // no prefix is necessary. The condition will be true when from wrapTable.
        if ($prefixAlias) {
            $segments[1] = $this->tablePrefix.$segments[1];
        }

        return $this->wrap(
                $segments[0]).' as '.$this->wrapValue($segments[1]
            );
    }

   public  function collect($value = null)
    {
        return new Collection($value);
    }

    protected function wrapSegments($segments)
    {

        return $this->collect($segments)->map(function ($segment, $key) use ($segments) {
            return $key == 0 && count($segments) > 1
                ? $this->wrapTable($segment)
                : $this->wrapValue($segment);
        })->implode('.');
    }


    protected function wrapValue($value)
    {

        if ($value !== '*') {
              return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }


    public function columnize(array $columns)
    {
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }


    public function parameterize(array $values)
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    public function parameter($value)
    {
        return $this->isExpression($value) ? $this->getValue($value) : '?';
    }


    public function isExpression($value)
    {
        return $value instanceof Expression;
    }



    public function getValue($expression)
    {
        return $expression->getValue();
    }


    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }


    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }


    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        return $this;
    }


}