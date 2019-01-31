<?php

require_once ('Model.php');
class UserModel extends Model
{

}
$conn = new Connection();


$ebi = new QueryBuilder($conn);

$model = new UserModel();
//var_dump($model->sBuilder());
/*$res = $model->newConnection()->select('Select * from forum.user');
var_dump($res);*/
//var_dump($model->newConnection()->insert("INSERT INTO laravel.countries (title,people,langue)
//VALUES (?,?,?)", ['bur',321,'byr']));
//var_dump($model->connection);
/*$q = $model->newQueryBuilder();
$res = $q->where('user.id','=','20')->runSelect();
var_dump($res);*/
/*
echo "<pre>";
var_dump($q);
echo "</pre>";*/
$el = new ElocentBuilder($ebi);
$build = $model->sBuilder();
//var_dump($build);
//$model->newQueryBuilder()->get()->all();
//var_dump($model->newEloquentBuilder($ebi)->query->get()->all());
//$eb = $model->sBuilder();
//var_dump($eb->getModel()->getQualifiedKeyName());
//var_dump($eb->find(20));

echo "<pre>";
//$res = $el->get();
//print_r($res);
//var_dump($el->hydrate(['*']));
echo "</pre>";

echo "<pre>";
//var_dump($model);
$res = $model::find(20);

var_dump($res);
echo "</pre>";

