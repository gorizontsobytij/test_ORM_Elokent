<?php

class Collection
{
    public $items = [];

    public function __construct($items = [])
    {


        $this->items = $this->getArrayableItems($items);

    }



    public function map(callable $callback)
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    public function implode($value)
    {


        return implode($value, $this->items);
    }


    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            //dd($items);
            return $items;
        } elseif ($items instanceof self) {

            return $items->all();
            // dd($items);
        }

        return (array) $items;
    }

    public function all()
    {

        return $this->items;
    }

}