<?php
namespace HZ\Illuminate\Mongez\Helpers\Database\MongoDB;

use DB;

class Database
{
    /**
     * Get all collections list
     * 
     * @return array
     */
    public static function collectionsList(): array
    {
        $collections = [];
    
        $collectionList = DB::connection()->getMongoDB()->listCollections();
     
        foreach ($collectionList as $collection) {
            $collections[] = $collection->getName();
        }

        return $collections;
    }
}