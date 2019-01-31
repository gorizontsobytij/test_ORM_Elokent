<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 29.12.18
 * Time: 8:13
 */

require_once ('QueryBuilder.php');

class ElocentBuilder
{

    public $model;//object model

    public $query;//object queryBuilder



    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->query = $queryBuilder;

    }

    public function find($id, $columns = ['*'])
    {
        /*if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id, $columns);
        }*/

       // var_dump($this->whereKey(20));
        return $this->whereKey($id)->runSelect();
    }


    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {

            $res = $this->query->where(...func_get_args());

       // var_dump($res);
        return $res;
    }



    public function whereKey($id)
    {
       /* if (is_array($id) || $id instanceof Arrayable) {
            $this->query->whereIn($this->model->getQualifiedKeyName(), $id);

            return $this;
        }*/
        //  dd($this->where($this->model->getQualifiedKeyName(), '=', $id));
        return $this->where($this->getModel()->getQualifiedKeyName(), '=', $id);
    }

    public function get($columns = ['*'])
    {

        $builder = $this->applyScopes();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded, which will solve the
        // n+1 query issue for the developers to avoid running a lot of queries.
        if (count($models = $builder->getModels($columns)) > 0) {
            //$models = $builder->getModels($columns);
           // var_dump($models);
            return $builder->getModel()->newCollection($models);
        }

        return false;////Додумать

    }



    public function hydrate(array $items)
    {
        $instance = $this->newModelInstance();

        return  $instance->newCollection(array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder($item);
        }, $items));

    }

    public function getModels($columns = ['*'])
    {
        return $this->model->hydrate(
            $this->query->get($columns)->all()
        )->all();
    }

    public function newModelInstance($attributes = [])
    {

        return $this->model->newInstance($attributes);
    }


    public function applyScopes()
    {


        $builder = clone $this;


        return $builder;
    }



    public function setModel(Model $model){
        $this->model = $model;
    }

    public function getModel(){
        return $this->model;
    }

}