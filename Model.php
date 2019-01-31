<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 29.12.18
 * Time: 8:14
 */

require_once ('Connection.php');
require_once ('ElocentBuilder.php');
require_once ('QueryBuilder.php');

class Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    public $connection;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    //public static $method;
    //public static $param;
    protected $primaryKey = 'id';

    public $attributes = [];

    public function __construct()
    {
        $this->BootModel();
    }

    private function BootModel(){
        $this->table =  static::class;
    }



    public function qualifyColumn($column)
    {

        return $this->getTable().'.'.$column;
    }
    public static function all($columns = ['*'])
    {
        //создает обьект билдера
         // var_dump((new static)->newQuery());
        return (new static)->newQuery()->get(
            is_array($columns) ? $columns : func_get_args()
        );
    }


    public function newInstance($attributes = [])
    {
        $model = new static((array) $attributes);



        return $model;
    }
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = $this->newInstance([]);

        $model->attributes = (array)$attributes;

        $model->setConnection();

        // var_dump($model);
        return $model;
    }



    public function newCollection(array $models = [])
    {

        return new Collection($models);
    }



    public static function query()
    {
        return (new static)->newQuery();
    }

    public  function newQuery(){
        return $this->sBuilder();
    }


//Создание элокентБилдера с обьектами  QueryBilder and MODEL
    public function sBuilder(){
        $builder  = $this->newEloquentBuilder($this->newQueryBuilder());
        $builder->setModel($this);
        return $builder;

    }


    public function newEloquentBuilder(QueryBuilder $queryBuilder)
    {
        return new ElocentBuilder($queryBuilder);
    }

    public function newQueryBuilder(){

        return new QueryBuilder($this->newConnection());
    }

    public function newConnection(){

        return new Connection();
    }










    public function setConnection(){
        $this->connection = $this->newConnection()->getPdo();
        return;
    }



    public function setTable($table){
        $this->table = $table;
        return $this;
    }

    public function getTable(){
       // var_dump($this->table);
       $table =  strtolower(substr($this->table,0,-5));
       return $table;
    }

    public function getKeyName()
    {
        // dd($this->primaryKey);
        return $this->primaryKey;
    }

    /**
     * Set the primary key for the model.
     *
     * @param  string  $key
     * @return $this
     */
    public function setKeyName($key)
    {
        $this->primaryKey = $key;

        return $this;
    }


    //получение таблицы по ключу имени
    public function getQualifiedKeyName()
    {
        // dd($this->qualifyColumn($this->getKeyName()));
        return $this->qualifyColumn($this->getKeyName());
    }


    public function __call($method, $parameters)
    {

        return $this->newQuery()->$method($parameters);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        //еесли не находит такого метода сдесь , он ищет в методе __call
       //  var_dump( (new static));
            //static::$param = $parameters;
            //static::$method = $method;
             return (new static)->$method(...$parameters);
        //return (new static());

    }





}