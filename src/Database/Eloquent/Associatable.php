<?php

namespace HZ\Illuminate\Mongez\Database\Eloquent;

use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Model;

trait Associatable
{
    /**
     * Associate the given value to the given key
     *
     * @param mixed $modelInfo
     * @param string $column
     * @return Model
     */
    public function associate($modelInfo, string $column): Model
    {
        $listOfValues = $this->{$column} ?? [];

        if ($modelInfo instanceof Model) {
            $listOfValues[] = $modelInfo->sharedInfo();
        } else {
            $listOfValues[] = $modelInfo;
        }

        $this->setAttribute($column, $listOfValues);

        return $this;
    }

    /**
     * Re-associate the given document
     *
     * @param   mixed $modelInfo
     * @param   string $column
     * @param   string $searchingColumn
     * @return Model
     */
    public function reassociate($modelInfo, string $column, string $searchingColumn = 'id'): Model
    {
        $documents = $this->{$column} ?? [];

        if ($modelInfo instanceof Model) {
            $modelInfo = $modelInfo->sharedInfo();
        }

        $found = false;

        foreach ($documents as $key => $document) {
            if (is_scalar($document) && $document === $modelInfo) {
                $documents[$key] = $modelInfo;
                $found = true;

                break;
            } else {
                $document = (array) $document;
                if (isset($document[$searchingColumn]) && $document[$searchingColumn] == $modelInfo[$searchingColumn]) {
                    $documents[$key] = $modelInfo;
                    $found = true;

                    break;
                }
            }
        }

        if (!$found) {
            $documents[] = $modelInfo;
        }

        $this->setAttribute($column, $documents);

        return $this;
    }

    /**
     * Disassociate the given value to the given key
     *
     * @param mixed $modelInfo
     * @param string $column
     * @param string $searchBy
     * @return Model
     */
    public function disassociate($modelInfo, string $column, string $searchBy = 'id'): Model
    {
        $array = $this->{$column} ?? [];

        $newArray = [];

        foreach ($array as $value) {
            if (
                is_scalar($modelInfo) && $modelInfo === $value ||
                is_array($value) && isset($value[$searchBy]) && $value[$searchBy] == $modelInfo[$searchBy]
            ) {
                continue;
            }

            $newArray[] = $value;
        }

        $this->setAttribute($column, $newArray);

        return $this;
    }
}
