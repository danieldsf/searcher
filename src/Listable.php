<?php

namespace Danieldsf\Searcher;

use Illuminate\Support\Facades\DB;

trait Listable {

    public static $filterCriterias = [
        'gt' => '>',
        'ge' => '>=',
        'lt' => '<=',
        'le' => '<',
        'eq' => '=',
        'neq' => '<>',

        'nl' => 'has no value',
        'nnl' => 'has value',

        'em' => 'is empty',

        'bt' => 'between',

        'ct' => ['%', '%'],
        'sw' => ['', '%'], //'starts with',
        'ew' => ['%', ''], //'ends with',

        'nct' => ['%', '%'],
        'nsw' => ['', '%'], //'starts with',
        'new' => ['%', ''],
        // Dates:
        'last_months' => '',
        'last_days' => '',
        'last_years' => '',

        'next_months' => '',
        'next_days' => '',
        'next_years' => '',
        //
        'greater_than' => '',
        'greater_than_equals' => '',

        'lower_than' => '',
        'lower_than_equals' => '',

        'equals_to' => '',
        //
    ];

    private static function fixSearch($query, $key, $value, $and = false){
        try {

            $value = explode(",", $value);
            $count = count($value);

            if($count == 1){
                $query = Filter::whereNull($query, $key, $value[0] == 'nl', $and);
            }else if($count == 3){
                $query = Filter::whereBetween($query, $key, $value[1], $value[2], $value[0] == 'bt', $and);
            }else if($count == 2){
                switch ($value[0]) {
                    case 'gt':
                    case 'lt':
                    case 'lte':
                    case 'gte':
                    case 'eq':
                    case 'neq':
                        $query = $and ?
                            $query->where($key, self::$filterCriterias[$value[0]], $value[1]) :
                            $query->orWhere($key, self::$filterCriterias[$value[0]],$value[1]);
                    break;

                    // Strings:
                    case 'ct':
                    case 'sw':
                    case 'ew':
                    # code...
                        $query = Filter::whereContains($query, $key, self::$filterCriterias[$value[0]], $value[1], true, $and);
                    break;

                    // Strings:
                    case 'nct':
                    case 'nsw':
                    case 'new':
                    # code...
                        $query = Filter::whereContains($query, $key, self::$filterCriterias[$value[0]], $value[1], false, $and);
                    break;

                    default:
                        # code...
                    break;
                }
            }

            return $query;
        } catch (\Exception $exception) {
            app('sentry')->captureException($exception);
            return $query;
        }
    }

    public function scopePaginateSearch($query){
        $query2 = clone $query;

        $sort = request('sort', 'id');
        $orientation = 'desc';
        $sorts = [];

        if($sort[0] == '-'){
            $sort = substr($sort, 1);
            $orientation = 'asc';
        }

        $self = self::class;

        if(method_exists($self, 'getSorts')){
            $sorts = $self::getSorts();
        }

        $table = self::getTable();

        if(method_exists($self, 'getIncludes')){
            if (!(strpos($sort, '.') !== false)) {
                $sort = $table.'.'.$sort;
            }
        }

        if(in_array($sort, $sorts)){
            $query = $query->orderBy($sort, $orientation);
        }

        $per_page = request('per_page', 'all');

        if($per_page == 'all'){
            $per_page = DB::table($table)->count();
        }

        return $query->paginate($per_page);
    }



    public function scopeListAll($query, $request, $paginate = true){

        try {
            $a = $request->all();

            $and = $request->and;

            $filters = $sorts = ['id', 'comments'];

            $self = self::class;

            $groupBy = '';

            $table = self::getTable();

            if(method_exists($self, 'getFilters')){
                $filters = $self::getFilters();
                foreach($filters as $filter){
                    if(strpos($filter, $table) === 0){
                        if($groupBy == ''){
                            $groupBy = $filter;
                        }
                        $groupBy .= ',' . $filter;
                    }
                }
            }

            if(method_exists($self, 'getIncludes')){
                $includes = $self::getIncludes();

                foreach ($includes as $key => $value) {
                    if(count($value) == 3){
                        $query = $query
                        ->leftJoin($value[0], $value[1], '=', $value[2]);
                    }else{
                        $query = $query
                        ->leftJoin($value[0], $value[1], '=', $value[2])
                        ->leftJoin($value[3], $value[4], '=', $value[5]);
                    }
                }

                $query = $query
                ->select($table . '.*')
                ->groupBy(DB::raw($groupBy));
            }

            foreach ($a as $key => $value) {
                $key = preg_replace('/_/', '.', $key, 1);
                if(in_array($key, $filters)){

                    foreach ($value as $item) {
                        $query = self::fixSearch($query, $key, $item, $and);
                    }
                }
            }

            if($paginate)
                return $query->paginateSearch();
            return $query;

        } catch (\Exception $exception) {

            app('sentry')->captureException($exception);

            if($paginate)
                return $query->paginateSearch();
            return $query;
        }
    }

    public function scopeFuzzySearch($query, $value, $paginate = true){
        #$fuzzySearch = $value;

        $columns = self::getFilters();

        $self = self::class;

        $groupBy = '';

        $table = self::getTable();

        if(method_exists($self, 'getFilters')){
            $filters = $self::getFilters();
            foreach($filters as $filter){
                if(strpos($filter, $table) === 0){
                    if($groupBy == ''){
                        $groupBy = $filter;
                    }
                    $groupBy .= ',' . $filter;
                }
            }
        }

        if(method_exists($self, 'getIncludes')){
            $includes = $self::getIncludes();

            foreach ($includes as $key => $value) {
                if(count($value) == 3){
                    $query = $query
                    ->leftJoin($value[0], $value[1], '=', $value[2]);
                }else{
                    $query = $query
                    ->leftJoin($value[0], $value[1], '=', $value[2])
                    ->leftJoin($value[3], $value[4], '=', $value[5]);
                }
            }

            $query = $query
            ->select($table . '.*')
            ->groupBy(DB::raw($groupBy));
        }

        foreach ($columns as $key => $column) {
            dd($column);
            if($key == 0){
                $query = Filter::whereContains($query, $key, ['%', '%'], $value, true, true);
            }else{
                $query = Filter::whereContains($query, $key, ['%', '%'], $value, true, false);
            }
        }

        if($paginate)
            return $query->paginateSearch()->toSql();
        return $query->toSql();
    }
}
