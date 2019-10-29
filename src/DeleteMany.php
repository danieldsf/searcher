<?php

namespace Danieldsf\Searcher;

trait DeleteMany {

    public static function deleteMany($ids){
        return self::query()
        ->whereIn('id', $ids)
        ->delete();
    }
}
