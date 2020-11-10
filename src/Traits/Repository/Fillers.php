<?php

namespace HZ\Illuminate\Mongez\Traits\Repository;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

trait Fillers
{
    /**
     * Get request object with data
     * 
     * @param  Request|array $data
     * @return Request
     */
    protected function getRequestWithData($data): Request
    {
        if (is_array($data)) {
            $request = $this->request;
            foreach ($data as $key => $value) {
                Arr::set($request, $key, $value);
            }
        } else {
            $request = $data;
        }

        return $request;
    }

    /**
     * Set data automatically from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void
     */
    protected function setAutoData($model, $request)
    {
        $this->setMainData($model, $request);

        $this->setArraybleData($model, $request);

        $this->upload($model, $request);

        $this->setIntData($model, $request);

        $this->setFloatData($model, $request);

        $this->setDateData($model, $request);

        $this->setBoolData($model, $request);
    }

    /**
     * Set main data automatically from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function setMainData($model, $request)
    {
        foreach (static::DATA as $column) {
            if ($this->isIgnorable($request, $column)) continue;

            if ($column === 'password') {
                if ($request->password) {
                    $model->password = bcrypt($request->password);
                }

                continue;
            }

            if (!isset($request->$column)) {
                $model->$column = null;
            } else {
                $model->$column = $request->$column;
            }
        }
    }

    /**
     * Set Arrayble data automatically from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function setArraybleData($model, $request)
    {
        foreach (static::ARRAYBLE_DATA as $column) {
            if ($this->isIgnorable($request, $column)) continue;
            $value = array_filter((array) $request->$column);
            $value = $this->handleArrayableValue($value);
            $model->$column = $value;
        }
    }

    /**
     * Pare the given arrayed value
     *
     * @param array $value
     * @return mixed
     */
    protected function handleArrayableValue(array $value)
    {
        return json_encode($value);
    }

    /**
     * Set uploads data automatically from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function upload($model, $request, $columns = null)
    {
        if (!$columns) {
            $columns = static::UPLOADS;
        }

        $storageDirectory = $this->getUploadsStorageDirectoryName();

        if (true === static::UPLOADS_KEEP_FILE_NAME) {
            $storageDirectory .= '/' . $model->getId();
        }

        $getFileName = function (UploadedFile $fileObject): string {
            $originalName = $fileObject->getClientOriginalName();

            $extension = File::extension($originalName) ?: $fileObject->guessExtension();

            $fileName = false === static::UPLOADS_KEEP_FILE_NAME ? Str::random(40) . '.' . $extension : $originalName;

            return $fileName;
        };

        foreach ((array) $columns as $column => $name) {
            if (is_numeric($column)) {
                $column = $name;
            }

            $file = $request->file($name);

            if (!$file) continue;

            if (is_array($file)) {
                $files = [];

                foreach ($file as $index => $fileObject) {
                    if (!$fileObject->isValid()) continue;

                    $files[$index] = $fileObject->storeAs($storageDirectory, $getFileName($fileObject));
                }

                $model->$column = $files;
            } else {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $model->$column = $file->storeAs($storageDirectory, $getFileName($file));
                }
            }
        }
    }

    /**
     * Get the uploads storage directory name
     * 
     * @return string
     */
    protected function getUploadsStorageDirectoryName(): string
    {
        return 'data/' . (static::UPLOADS_DIRECTORY ?: static::NAME);
    }

    /**
     * Set date data
     * 
     * @param  Model $model
     * @param  Request $request
     * @return void
     */
    protected function setDateData($model, $request, $columns = null)
    {
        if (!$columns) {
            $columns = static::DATE_DATA;
        }

        $isMongoDb = strtolower(config('database.default')) === 'mongodb';

        foreach ((array) $columns as $column) {
            if ($this->isIgnorable($request, $column)) continue;

            $date = $request->input($column);

            if (!$date) continue;

            $time = Carbon::parse($date);
            $model->$column = $isMongoDb ? new \MongoDB\BSON\UTCDateTime($time) : $time;
        }
    }

    /**
     * Cast specific data automatically to int from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function setIntData($model, Request $request)
    {
        foreach (static::INTEGER_DATA as $column) {
            if ($this->isIgnorable($request, $column)) continue;

            $model->$column = (int) $request->input($column);
        }
    }

    /**
     * Cast specific data automatically to float from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function setFloatData($model, Request $request)
    {
        foreach (static::FLOAT_DATA as $column) {
            if ($this->isIgnorable($request, $column)) continue;

            $model->$column = (float) $request->input($column);
        }
    }

    /**
     * Cast specific data automatically to bool from the DATA array
     * 
     * @param  \Model $model
     * @param  \Request $request
     * @return void  
     */
    protected function setBoolData($model, Request $request)
    {
        foreach (static::BOOLEAN_DATA as $column) {
            if ($this->isIgnorable($request, $column)) continue;

            $model->$column = (bool) $request->input($column);
        }
    }

    /**
     * Check if the given column is ignorable
     * 
     * @param  Request $request
     * @param  string $column
     * @return bool 
     */
    protected function isIgnorable(Request $request, string $column): bool
    {
        return (static::WHEN_AVAILABLE_DATA === true || in_array($column, static::WHEN_AVAILABLE_DATA)) && !isset($request->$column);
    }
}
