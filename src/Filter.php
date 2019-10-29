<?php

namespace Danieldsf\Searcher\src;

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

    

    public function whereNull($query, $key, $positive = true, $and = true){
        //
        if($positive){
            $query = $and ? $query->whereNull($key) : $query->orWhereNull($key);
        }else{
            $query = $and ? $query->whereNotNull($key) : $query->orWhereNotNull($key);
        }
        //
        return $query;
    }

    public function whereBetween($query, $key, $firstValue, $secondValue, $positive = true, $and = true){
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