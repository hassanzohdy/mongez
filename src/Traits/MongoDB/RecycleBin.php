<?php

namespace HZ\Illuminate\Mongez\Traits\MongoDB;

use DB;
use Illuminate\Support\Collection;

trait RecycleBin
{
    /**
     * {@inheritDoc}
     */
    public function delete()
    {
        $recordInfo = $this->info();

        $tableName = $this->getTable();

        $trashTable = static::trashTable();

        $primaryId = $this->id;

        DB::collection($trashTable)->insert([
            'primaryId' => $primaryId,
            'record' => $recordInfo,
        ]);

        parent::delete();
    }

    /**
     * Get the deleted records
     * 
     * @return Collection
     */
    public static function getDeleted()
    {
        $records = DB::collection(static::trashTable())->pluck('record');

        return $records->map(function ($record) {
            return new static($record);
        });
    }

    /**
     * Find the deleted record for the given id
     * 
     * @param  int $id
     * @return static
     */
    public static function findDeleted($id)
    {
        $record = DB::collection(static::trashTable())->where('primaryId', (int) $id)->first();

        if (!$record) return null;

        return new static($record['record']);
    }

    /**
     * Restore all deleted records
     * 
     * @return Collection
     */
    public static function restoreAll()
    {
        $records = static::getDeleted();

        $restoredIds = [];

        foreach ($records as $record) {
            // re-insert the record again
            $record->save();
            // remove it from the trashed table
            $restoredIds[] = $record->id;
        }

        DB::collection(static::trashTable())->whereIn('primaryId', $restoredIds)->delete();

        return $records;
    }

    /**
     * Get trash table name
     * 
     * @return string
     */
    public static function trashTable()
    {
        return defined('static::TRASH_TABLE') ? static::TRASH_TABLE : static::getTableName() . 'Trash';
    }
}
