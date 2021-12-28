<?php

namespace HZ\Illuminate\Mongez\Traits\Repository;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use HZ\Illuminate\Mongez\Services\Images\ImageResize;

trait Fillers
{
    /**
     * Storage directory path
     * 
     * @var string
     */
    protected string $storageDirectory;

    /**
     * Get request object with data
     *
     * @param  Request|array $data
     * @return Request
     */
    protected function getRequestWithData($data): Request
    {
        // keep original request untouched, just clone it
        if (is_array($data)) {
            $request = clone $this->request;
            $request->merge($data);
        } else {
            $request = clone $data;
        }

        return $request;
    }

    /**
     * Set data automatically from the DATA array
     *
     * @param  \Model $model
     * @return void
     */
    protected function setAutoData($model)
    {
        $this->setMainData($model);

        $this->setLocalizedData($model);

        $this->setArraybleData($model);

        $this->upload($model, static::UPLOADS);

        $this->setStringData($model);

        $this->setIntData($model);

        $this->setFloatData($model);

        $this->setDateData($model);

        $this->setBoolData($model);
    }

    /**
     * Set string data automatically from the DATA array
     *
     * @param  \Model $model
     * @param  \Request $request
     * @return void
     */
    protected function setStringData($model)
    {
        foreach (static::STRING_DATA as $input => $column) {
            if (is_numeric($input)) {
                $input = $column;
            }

            if ($this->isIgnorable($input)) continue;

            if ($input === 'password') {
                if ($password = $this->input('password')) {
                    $model->password = bcrypt($password);
                }

                continue;
            }

            $this->setToModel($model, $column, (string) $this->input($input));
        }
    }

    /**
     * Set main data automatically from the DATA array
     *
     * @param  \Model $model
     * @param  \Request $request
     * @return void
     */
    protected function setMainData($model)
    {
        foreach (static::DATA as $input => $column) {
            if (is_numeric($input)) {
                $input = $column;
            }

            if ($this->isIgnorable($input)) continue;

            if ($column === 'password') {
                if ($password = $this->input('password')) {
                    $model->password = bcrypt($password);
                }

                continue;
            }

            $this->setToModel($model, $column, $this->input($input));
        }
    }

    /**
     * Set localized data automatically from the LOCALIZED_DATA array
     *
     * @param  \Model $model
     * @param  \Request $request
     * @return void
     */
    protected function setLocalizedData($model)
    {
        foreach (static::LOCALIZED_DATA as $input => $column) {
            if (is_numeric($input)) {
                $input = $column;
            }

            if ($this->isIgnorable($input)) continue;

            $this->setToModel($model, $column, $this->input($input));
        }
    }

    /**
     * Set Arrayble data automatically from the DATA array
     *
     * @param  \Model $model
     * @param  \Request $request
     * @return void
     */
    protected function setArraybleData($model)
    {
        foreach (static::ARRAYBLE_DATA as $input => $column) {
            if (is_numeric($input)) {
                $input = $column;
            }

            if ($this->isIgnorable($input)) continue;

            $value = array_filter((array) $this->input($input));

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
     * @param  array|null $columns
     * @return void
     */
    protected function upload($model, $columns = null)
    {
        if (!$columns) {
            $columns = static::UPLOADS;
        }

        $this->storageDirectory = $this->getUploadsStorageDirectoryName() . '/' . $model->getId();

        foreach ((array) $columns as $name => $column) {
            $options = [
                'clearable' => false,
                'arrayable' => null, // auto check
            ];

            if (is_array($column)) {
                $options = $column;
            } elseif (is_numeric($name)) {
                $options['column'] = $column;
                $options['input'] = $column;
            } else {
                $options['input'] = $name;
                $options['column'] = $column;
            }

            $column = $options['column'] ?? $options['input'];
            $input = $options['input'] ?? $options['column'];
            $clearable = $options['clearable'] ?? false;
            $arrayable = $options['arrayable'] ?? null;

            $file = $this->request->file($input);

            if (is_null($arrayable)) {
                $arrayable = is_array($file);
            }

            if (!$file) {
                if ($clearable) {
                    $storedValue = $this->input($input . 'String', $arrayable ? [] : '');

                    $this->setToModel($model, $column, $storedValue);
                } else {
                    $files = $this->mergeOldAndNewFiles([], $column, $model);
                    $this->setToModel($model, $column, $files);
                }

                continue;
            }

            if ($arrayable) {
                $files = [];

                foreach ($file as $index => $fileObject) {
                    if (!$fileObject->isValid()) continue;

                    $files[$index] = $this->uploadFile($fileObject);
                }

                $files = $this->mergeOldAndNewFiles($files, $column, $model);

                // based on inherited manager, multiple uploads are stored differently
                // if set to true, then encode the listed files

                if (static::SERIALIZE_MULTIPLE_UPLOADS === true) {
                    $files = json_encode($files);
                }

                $this->setToModel($model, $column, $files);
            } else {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $filePath = $this->uploadFile($file);
                    $this->setToModel($model, $column, $filePath);
                }
            }
        }
    }

    /**
     * Upload the given file and return the new path
     * 
     * @param  UploadedFile $file
     * @return string
     */
    public function uploadFile($file)
    {
        return $file->storeAs($this->storageDirectory ?: $this->getUploadsStorageDirectoryName(), $this->getFileName($file));
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
    private function mergeOldAndNewFiles(array $files, $column, $model)
    {
        $filesFromRequest = array_map(function ($file) {
            return ltrim($file, '/');
        }, (array) $this->input($column . 'String', []));

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
    protected function setDateData($model, $columns = null)
    {
        if (!$columns) {
            $columns = static::DATE_DATA;
        }

        foreach ((array) $columns as $input => $column) {
            if (is_numeric($input)) {
                $input = $column;
            }

            if ($this->isIgnorable($input)) continue;

            $date = $this->input($input);

            if (!$date) continue;

            $this->setToModel($model, $column, Carbon::parse($date));
        }
    }

    /**
     * Cast specific data automatically to int from the DATA array
     *
     * @param  \Model $model
     * @return void
     */
    protected function setIntData($model)
    {
        foreach (static::INTEGER_DATA as $input => $column) {
            if (is_numeric($input)) {
                $input = $column;
            }

            if ($this->isIgnorable($input)) continue;

            $this->setInt($model, $column, $this->input($input));
        }
    }

    /**
     * Cast specific data automatically to float from the DATA array
     *
     * @param  \Model $model
     * @return void
     */
    protected function setFloatData($model)
    {
        foreach (static::FLOAT_DATA as $input => $column) {
            if (is_numeric($input)) {
                $input = $column;
            }

            if ($this->isIgnorable($input)) continue;

            $this->setFloat($model, $column, $this->input($input));
        }
    }

    /**
     * Cast specific data automatically to bool from the DATA array
     *
     * @param  \Model $model
     * @return void
     */
    protected function setBoolData($model)
    {
        foreach (static::BOOLEAN_DATA as $input => $column) {
            if (is_numeric($input)) {
                $input = $column;
            }

            if ($this->isIgnorable($input)) continue;


            if (($inputValue = $this->input($input)) === 'false') {
                $this->setToModel($model, $column, false);
            } else {
                $this->setBool($model, $column, $inputValue);
            }
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
        $model->setAttribute($key, $value);
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
     * Check if the given input is ignorable
     *
     * @param  string $input
     * @return bool
     */
    protected function isIgnorable(string $input): bool
    {
        return (static::WHEN_AVAILABLE_DATA === true || in_array($input, static::WHEN_AVAILABLE_DATA)) && $this->input($input) === null;
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
        return $this->request->has($key) ? $this->request->input($key) : ($this->request->__get($key) ?: $default);
    }

    /**
     * Get int input value
     * 
     * @param  string $key
     * @param  mixed $default
     * @return int
     */
    protected function intInput(string $key, $default = null): int
    {
        return (int) $this->input($key, $default);
    }

    /**
     * Get float input value
     * 
     * @param  string $key
     * @param  mixed $default
     * @return float
     */
    protected function floatInput(string $key, $default = null): float
    {
        return (float) $this->input($key, $default);
    }

    /**
     * Get a boolean value
     * 
     * @param  string $key
     * @param  mixed $default
     * @return bool
     */
    public function boolInput(string $key, $default = null): bool
    {
        $value = $this->input($key, $default);

        if ($value === 'false') {
            $value = false;
        } elseif ($value === 'true') {
            $value = true;
        }

        return (bool) $value;
    }
}
