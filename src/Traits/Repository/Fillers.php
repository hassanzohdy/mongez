<?php

namespace HZ\Illuminate\Mongez\Traits\Repository;

use Carbon\Carbon;
use HZ\Illuminate\Mongez\Services\Images\ImageResize;
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
                $this->setToModel($request, $key, $value);
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

            $this->setToModel($model, $column, $this->input($column));
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

            $value = array_filter((array) $this->input($column));

            $value = $this->handleArrayableValue($value);

            $this->setToModel($model, $column, $value);
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

        $storageDirectory .= '/' . $model->getId();

        foreach ((array) $columns as $column => $name) {
            if (is_numeric($column)) {
                $column = $name;
            }

            $file = $request->file($name);

            if (!$file) {
                $files = $this->mergeOldAndNewFiles([], $column, $request, $model);
                $this->setToModel($model, $column, $files);

                continue;
            }

            if (is_array($file)) {
                $files = [];

                foreach ($file as $index => $fileObject) {
                    if (!$fileObject->isValid()) continue;

                    $files[$index] = $fileObject->storeAs($storageDirectory, $this->getFileName($fileObject));
                }

                $files = $this->mergeOldAndNewFiles($files, $column, $request, $model);

                $this->setToModel($model, $column, $files);
            } else {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $filePath = $file->storeAs($storageDirectory, $this->getFileName($file));
                    $this->setToModel($model, $column, $filePath);
                }
            }
        }
    }

    /**
     * Get file name
     * 
     * @param UploadedFile $fileObject
     * @return string
     */
    protected function getFileName(UploadedFile $fileObject): string
    {
        static $keepFileName = null;

        if ($keepFileName === null) {
            $keepFileName = defined('static::UPLOADS_KEEP_FILE_NAME') ? static::UPLOADS_KEEP_FILE_NAME : config('mongez.repository.uploads.keepUploadsName', true);
        }

        $originalName = $fileObject->getClientOriginalName();

        $extension = File::extension($originalName) ?: $fileObject->guessExtension();

        $fileName = false === $keepFileName ? Str::random(40) . '.' . $extension : $this->adjustFileName($originalName);

        return $fileName;
    }

    /**
     * Adjust the given file name
     * 
     * @param  string $fileName
     * @return string
     */
    private function adjustFileName($fileName)
    {
        $fileName = preg_replace('/(\-+)/', '-', str_replace([
            '-', '(', ')', '%', '#', ' ',
        ], '-', $fileName));

        return preg_replace('/\-\./', '.', $fileName);
    }

    /**
     * Merge the given files with the old uploaded ones
     * 
     * @param  array $files
     * @param  string $column
     * @return array
     */
    private function mergeOldAndNewFiles(array $files, $column, $request, $model)
    {
        $filesFromRequest = array_map(function ($file) {
            return ltrim($file, '/');
        }, (array) $request->{$column . 'String'});

        $images = Arr::get($model, $column);

        if ($images && is_string($images) || (empty($filesFromRequest) && empty($files))) return $images;

        foreach ($filesFromRequest as $key => $oldFile) {
            if (!isset($files[$key])) continue;

            $this->unlink($oldFile);
            unset($filesFromRequest[$key]);
        }

        return array_merge($filesFromRequest, $files);
    }

    /**
     * Create File options
     *
     * @param string $uploadedFile
     * @param array  $options
     * @return
     */
    protected function fileOptions($uploadedFile, $options)
    {
        $fileOptions = [];
        if (array_key_exists('thumbnailImage', $options)) {
            $ImageResize = new ImageResize($uploadedFile);
            $thumbnailImage = $ImageResize->resize(
                $options['thumbnailImage']['width'],
                $options['thumbnailImage']['height'],
                $options['thumbnailImage']['quality']
            );
            $fileOptions['thumbnailImage'] = $thumbnailImage;
        }

        if (array_key_exists('mediumImage', $options)) {
            $ImageResize = new ImageResize($uploadedFile);
            $mediumImage = $ImageResize->resize(
                $options['mediumImage']['width'],
                $options['mediumImage']['height'],
                $options['mediumImage']['quality']
            );
            $fileOptions['mediumImage'] = $mediumImage;
        }
        return $fileOptions;
    }

    /**
     * Get the uploads storage directory name
     *
     * @return string
     */
    protected function getUploadsStorageDirectoryName(): string
    {
        $baseDirectory = config('mongez.repository.uploads.uploadsDirectory', -1);

        if ($baseDirectory === -1) {
            $baseDirectory = 'data';
        }

        if ($baseDirectory) {
            $baseDirectory .= '/';
        }

        return $baseDirectory . (static::UPLOADS_DIRECTORY ?: static::NAME);
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

        foreach ((array) $columns as $column) {
            if ($this->isIgnorable($request, $column)) continue;

            $date = $this->input($column);

            if (!$date) continue;

            $this->setToModel($model, $column, Carbon::parse($date));
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

            $this->setInt($model, $column, $this->input($column));
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

            $this->setFloat($model, $column, $this->input($column));
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

            if (($inputValue = $this->input($column)) === 'false') {
                $this->setToModel($model, $column, false);
            }

            $this->setBool($model, $column, $inputValue);
        }
    }

    /**
     * Set the given key/value to the model
     * 
     * @param  Model $model
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    protected function setToModel($model, string $key, $value)
    {
        $model->$key = $value;
    }

    /**
     * Set boolean value
     * 
     * @param  Model $model
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    protected function setBool($model, string $key, $value)
    {
        $this->setToModel($model, $key, (bool) $value);
    }

    /**
     * Set int value
     * 
     * @param  Model $model
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    protected function setInt($model, string $key, $value)
    {
        $this->setToModel($model, $key, (int) $value);
    }

    /**
     * Set float value
     * 
     * @param  Model $model
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    protected function setFloat($model, string $key, $value)
    {
        $this->setToModel($model, $key, (float) $value);
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
        return (static::WHEN_AVAILABLE_DATA === true || in_array($column, static::WHEN_AVAILABLE_DATA)) && !$request->has($column);
    }

    /**
     * Get input value
     * 
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    protected function input(string $key, $default = null)
    {
        $value = $this->request->input($key) ?? $this->request->$key;

        return $value ?: $default;
    }
}
