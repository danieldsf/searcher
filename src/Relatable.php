<?php

namespace Danieldsf\Searcher;

use Illuminate\Support\Facades\DB;

trait Relatable {

    public function getRelatedQuery(){
        $pieces = explode(" ", $this->name);

        $query = self::select(DB::raw('id,name'))
        ->where(function($query) use ($pieces){
            $size = count($pieces);
            for ($i = 0; $i < $size; $i++) {
                $key = strtolower($pieces[$i]);
                $query = Filter::whereContains($query, 'name', ['%s', '%s'], $key, true, false);
            }
        })
        ->orderBy('name')
        ->groupBy(DB::raw('id,name'));

        return $query;
    }

    public function getRelated()
    {
        return $this
        ->getRelatedQuery()
        ->where('id', '<>', $this->id)->get();
    }
}
