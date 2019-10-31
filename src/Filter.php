<?php

namespace Danieldsf\Searcher;

class Filter {

    private static $databaseDriver = '';

    public function __construct()
    {
        self::$databaseDriver = $this->getDatabaseDriverName();
    }

    /**
    * Método que retorna se a conexão está sendo feita com 'mysql', 'sqlite' ou outra opção
    *
    * @return String nome do driver para o SGBD usado.
    */
    private static function getDatabaseDriverName(){
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        return $driver;
    }


    public static function whereContains($query, $key, $operation, $value, $positive = true, $and = true){
        ##dd($value);
        $term = strtolower($value);

        $term = $operation[0].$term.$operation[1];

        $db = self::getDatabaseDriverName();

        if($positive){

            if($db == 'mysql'){
                $query = $and ? $query->whereRaw("lower($key) like ?", [$term]) : $query->orWhereRaw("lower($key) like ?", [$term]);
            }else{
                $query = $and ? $query->where($key, 'ilike', $term) : $query->orWhere($key, 'ilike', $term);
            }
        }else{
            if($db == 'mysql'){
                $query = $and ? $query->whereNotRaw("lower($key) like ?", [$term]) : $query->orWhereNotRaw("lower($key) like ?", [$term]);
            }else{
                $query = $and ? $query->whereNot($key, 'ilike', $term) : $query->orWhereNot($key, 'ilike', $term);
            }
        }

        return $query;
    }

    public static function whereNull($query, $key, $positive = true, $and = true){
        //
        if($positive){
            $query = $and ? $query->whereNull($key) : $query->orWhereNull($key);
        }else{
            $query = $and ? $query->whereNotNull($key) : $query->orWhereNotNull($key);
        }
        //
        return $query;
    }

    public static function whereBetween($query, $key, $firstValue, $secondValue, $positive = true, $and = true){
        //
        if($positive){
            $query = $and ? $query->whereBetween($key, [$firstValue, $secondValue]) : $query->orWhereBetween($key, [$firstValue, $secondValue]);
        }else{
            $query = $and ? $query->whereNotBetween($key, [$firstValue, $secondValue]) : $query->orWhereNotBetween($key, [$firstValue, $secondValue]);
        }
        //
        return $query;
    }


}
