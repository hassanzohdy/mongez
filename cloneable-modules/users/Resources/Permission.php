<?php
namespace App\Modules\Users\Resources;

use HZ\Illuminate\Mongez\Managers\Resources\JsonResourceManager;

class Permission extends JsonResourceManager 
{
    /**
     * Data that must be returned
     * 
     * @const array
     */
    const DATA = ['id', 'name', 'permission', 'key', 'group'];
    
    /**
     * Data that should be returned if exists
     * 
     * @const array
     */
    const WHEN_AVAILABLE = [];
    
    /**
     * Set that columns that will be formatted as dates
     * it could be numeric array or associated array to set the date format for certain columns
     * 
     * @const array
     */
    const DATES = [];
    
    /**
     * Data that has multiple values based on locale codes
     * Mostly this is used with mongodb driver
     * 
     * @const array
     */
    const LOCALIZED = [];
    
    /**
     * List of assets that will have a full url if available
     */
    const ASSETS = [''];

    /**
     * Data that will be returned as a resources
     * 
     * i.e [city => CityResource::class],
     * @const array
     */
    const RESOURCES = [];

    /**
     * Data that will be returned as a collection of resources
     * 
     * i.e [cities => CityResource::class],
     * @const array
     */
    const COLLECTABLE = [];
}