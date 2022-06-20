<?php

namespace HZ\Illuminate\Mongez\Database\Eloquent;

use Illuminate\Support\Str;
use HZ\Illuminate\Mongez\Database\Eloquent\MongoDB\Model;

trait ModelEvents
{
    /**
     * @var string
     */
    public static string $modelClass;

    /**
     * @var array
     */
    public static array $modelOptions = [];

    /**
     * @var string
     */
    public static string $sharedInfoMethod = 'sharedInfo';

    /**
     * Handle model create events.
     *
     * @param $model
     * @return void
     */
    public static function handleCreated($model)
    {
        static::handleCreateSingleModel($model);

        static::handleCreateArrayModel($model);
    }

    /**
     * Handle model update events.
     *
     * @param $model
     * @return void
     */
    public static function handleUpdated($model)
    {
        static::handleUpdateSingleModel($model);

        static::handleUpdateArrayModel($model);
    }

    /**
     * Handle model delete events.
     *
     * @param $model
     * @return void
     */
    public static function handleDeleted($model)
    {
        static::handleUnsetSingleModel($model);

        static::handlePullArrayModel($model);

        static::handleDeleteSingleModel($model);
    }

    /**
     * Handle create model record as single document in related models.
     *
     * @param Model $model
     * @return void
     */
    public static function handleCreateSingleModel(Model $model)
    {
        $singleModelsList = array_merge(
            config('mongez.database.onModel.create.' . static::class, []),
            !empty(static::ON_MODEL_CREATE) ? static::ON_MODEL_CREATE : [],
            !empty(static::MODEL_LINKS) ? static::MODEL_LINKS : [],
        );

        collect($singleModelsList)->each(function ($modelOptions, $modelClass) use ($model) {
            static::$modelClass = $modelClass;

            static::setCreateModelOptions($model, $modelOptions);

            collect(static::$modelOptions)->each(function ($options) use ($model) {
                $records = static::getCreateRelatedModels($model, $options);

                foreach ($records as $record) {
                    $record->{$options['foreignColumn']} = $model->{$options['sharedInfoMethod']}();

                    $record->save();
                }
            });
        });
    }

    /**
     * Handle create model record as array of documents in related models.
     *
     * @param $model
     * @return void
     */
    public static function handleCreateArrayModel($model)
    {
        $arrayModelsList = array_merge(
            config('mongez.database.onModel.createArray.' . static::class, []),
            !empty(static::ON_MODEL_CREATE_PUSH) ? static::ON_MODEL_CREATE_PUSH : [],
            !empty(static::MODEL_LINKS_ARRAY) ? static::MODEL_LINKS_ARRAY : [],
        );

        collect($arrayModelsList)->each(function ($modelOptions, $modelClass) use ($model) {
            static::$modelClass = $modelClass;

            static::setCreateModelOptions($model, $modelOptions);

            collect(static::$modelOptions)->each(function ($options) use ($model) {
                $records = static::getCreateRelatedModels($model, $options);

                foreach ($records as $record) {
                    $record->reassociate($model->{$options['sharedInfoMethod']}(), $options['foreignColumn'])->save();
                }
            });
        });
    }

    /**
     * Handle update model record as single document in related models.
     *
     * @param Model $model
     * @return void
     */
    public static function handleUpdateSingleModel(Model $model)
    {
        $singleModelsList = array_merge(
            config('mongez.database.onModel.update.' . static::class, []),
            !empty(static::ON_MODEL_UPDATE) ? static::ON_MODEL_UPDATE : [],
            !empty(static::MODEL_LINKS) ? static::MODEL_LINKS : [],
        );

        // the model options is can be an string or array
        // the array can have up to 3 elements: search-column, updating field and shared info method
        // if the model options is set to string, then it will be converted to
        // $modelOptions.id, $modelOptions, sharedInfo

        collect($singleModelsList)->each(function ($modelOptions, $modelClass) use ($model) {
            static::$modelClass = $modelClass;

            static::setModelOptions($modelOptions);

            collect(static::$modelOptions)->each(function ($options) use ($model) {
                $records = static::getRelatedModels($model, $options);

                foreach ($records as $record) {
                    $record->{$options['foreignColumn']} = $model->{$options['sharedInfoMethod']}();

                    $record->save();
                }
            });
        });
    }

    /**
     * Handle update model record as array of documents in related models.
     *
     * @param Model $model
     * @return void
     */
    public static function handleUpdateArrayModel(Model $model)
    {
        $arrayModelsList = array_merge(
            config('mongez.database.onModel.updateArray.' . static::class, []),
            !empty(static::ON_MODEL_UPDATE_ARRAY) ? static::ON_MODEL_UPDATE_ARRAY : [],
            !empty(static::MODEL_LINKS_ARRAY) ? static::MODEL_LINKS_ARRAY : [],
        );

        // the model options is can be an string or array
        // the array can have up to 3 elements: search-column, updating field and shared info method
        // if the model options is set to string, then it will be converted to
        // $modelOptions.id, $modelOptions, sharedInfo

        collect($arrayModelsList)->each(function ($modelOptions, $modelClass) use ($model) {
            static::$modelClass = $modelClass;

            static::setModelOptions($modelOptions);

            collect(static::$modelOptions)->each(function ($options) use ($model) {
                $records = static::getRelatedModels($model, $options);

                foreach ($records as $record) {
                    $record->reassociate($model->{$options['sharedInfoMethod']}(), $options['foreignColumn'])->save();
                }
            });
        });
    }

    /**
     * Handle unset model record as documents in related models.
     *
     * @param $model
     * @return void
     */
    public static function handleUnsetSingleModel($model)
    {
        $singleModelsList = array_merge(
            config('mongez.database.onModel.deleteUnset.' . static::class, []),
            !empty(static::ON_MODEL_DELETE_UNSET) ? static::ON_MODEL_DELETE_UNSET : [],
            !empty(static::MODEL_LINKS) ? static::MODEL_LINKS : [],
        );

        collect($singleModelsList)->each(function ($searchingOptions, $modelClass) use ($model) {
            static::$modelClass = $modelClass;

            static::setModelOptions($searchingOptions);

            collect(static::$modelOptions)->each(function ($options) use ($model) {
                $records = static::getRelatedModels($model, $options);

                foreach ($records as $record) {
                    $record->unset($options['foreignColumn']);
                    // Force saving again as the model in some is not triggering the update event
                    // so we will force the update by updating the updatedAt column;
                    $record->updatedAt = now();
                    $record->save();
                }
            });
        });
    }

    /**
     * Handle pull model record as array of documents in related models.
     *
     * @param $model
     * @return void
     */
    public static function handlePullArrayModel($model)
    {
        $arrayModelsList = array_merge(
            config('mongez.database.onModel.deletePull.' . static::class, []),
            !empty(static::ON_MODEL_DELETE_PULL) ? static::ON_MODEL_DELETE_PULL : [],
            !empty(static::MODEL_LINKS_ARRAY) ? static::MODEL_LINKS_ARRAY : [],
        );

        collect($arrayModelsList)->each(function ($searchingOptions, $modelClass) use ($model) {
            static::$modelClass = $modelClass;

            static::setModelOptions($searchingOptions);

            collect(static::$modelOptions)->each(function ($options) use ($model) {
                $records = static::getRelatedModels($model, $options);

                foreach ($records as $record) {
                    $record->disassociate($model, $options['foreignColumn'])->save();
                }
            });
        });
    }

    /**
     * Handle delete related models of the model record.
     *
     * @param $model
     * @return void
     */
    public static function handleDeleteSingleModel($model)
    {
        $singleModelsList = array_merge(
            config('mongez.database.onModel.delete.' . static::class, []),
            !empty(static::ON_MODEL_DELETE) ? static::ON_MODEL_DELETE : [],
            !empty(static::MODEL_LINKS_DELETE) ? static::MODEL_LINKS_DELETE : [],
        );

        collect($singleModelsList)->each(function ($searchingOptions, $modelClass) use ($model) {
            static::$modelClass = $modelClass;

            static::setModelOptions($searchingOptions);

            collect(static::$modelOptions)->each(function ($options) use ($model) {
                $records = static::getRelatedModels($model, $options);

                foreach ($records as $record) {
                    $record->delete();
                }
            });
        });
    }

    /**
     * Set model options on create events.
     *
     * @param $model
     * @param $options
     * @return void
     */
    public static function setCreateModelOptions($model, $options)
    {
        $options = static::getOptionsArray($options);

        collect($options)->each(function ($option) use ($model) {
            $modelOptions['searchingColumn'] = $option[0];

            switch (count($option)) {
                case 1:
                    // resolves related (Model::class) namespace to camelCase model name (model)
                    $relationalModel = Str::camel(str_replace('Models\\', '', strstr(static::$modelClass, 'Models')));

                    // searching in the model attributes for key asymptotic to resolved (Model::class) name to get the searching key
                    $foreignColumn = array_key_exists($relationalModel, $model->toArray()) ? $relationalModel :
                        array_key_first(array_filter($model->toArray(), function ($key) use ($relationalModel) {
                            return strpos($key, $relationalModel) !== false;
                        }, ARRAY_FILTER_USE_KEY));

                    $modelOptions['foreignColumn'] = $foreignColumn;
                    $modelOptions['sharedInfoMethod'] = static::$sharedInfoMethod;

                    break;
                case 2:
                    $modelOptions['foreignColumn'] = $option[1];
                    $modelOptions['sharedInfoMethod'] = static::$sharedInfoMethod;

                    break;
                case 3:
                    $modelOptions['foreignColumn'] = $option[1];
                    $modelOptions['sharedInfoMethod'] = $option[2];
            }

            static::$modelOptions[] = $modelOptions;
        });
    }

    /**
     * Set model options on update and delete events.
     *
     * @param $options
     * @return void
     */
    public static function setModelOptions($options)
    {
        $options = static::getOptionsArray($options);

        collect($options)->each(function ($option) {
            $modelOptions['searchingColumn'] = "{$option[0]}.id";

            switch (count($option)) {
                case 1:
                    $modelOptions['foreignColumn'] = $option[0];
                    $modelOptions['sharedInfoMethod'] = static::$sharedInfoMethod;

                    break;
                case 2:
                    $modelOptions['foreignColumn'] = $option[1];
                    $modelOptions['sharedInfoMethod'] = static::$sharedInfoMethod;

                    break;
                case 3:
                    $modelOptions['foreignColumn'] = $option[1];
                    $modelOptions['sharedInfoMethod'] = $option[2];
            }

            static::$modelOptions[] = $modelOptions;
        });
    }

    /**
     * Get model options as array of arrays.
     *
     * @param $options
     * @return array|mixed
     */
    public static function getOptionsArray($options)
    {
        static::$modelOptions = [];

        switch ($options) {
            case is_string($options):
                $options = [(array) $options];

                break;
            case is_array($options) && count($options) === count($options, COUNT_RECURSIVE):
                $options = [$options];
        }

        return $options;
    }

    /**
     * Get related models records on create events.
     *
     * @param Model $model
     * @param array $options
     * @return mixed
     */
    public static function getCreateRelatedModels(Model $model, array $options)
    {
        $searchingId = isset($model->{$options['searchingColumn']}) ? (int) $model->{$options['searchingColumn']}['id'] ?:
            array_map(function ($item) {
                return  (int) $item['id'];
            }, $model->{$options['searchingColumn']} ?: []) : null;

        return static::$modelClass::query()->whereIn('id', (array) $searchingId)->get();
    }

    /**
     * Get related models records on update and delete events.
     *
     * @param Model $model
     * @param array $options
     * @return mixed
     */
    public static function getRelatedModels(Model $model, array $options)
    {
        return static::$modelClass::query()->where($options['searchingColumn'], $model->id)->get();
    }
}
