<?php

namespace Danieldsf\Searcher;
use Illuminate\Http\Request;

trait ListByName {
    //
    public function scopeListByName($query, Request $request){
        $name = $request->name;

        return $query->when($name, function($query) use ($name) {
            return $query->where('name', 'like', "%$name%");
        })->get(['id', 'name']);
    }
}
