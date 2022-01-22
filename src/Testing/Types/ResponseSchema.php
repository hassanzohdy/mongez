<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Types;

use Exception;
use HZ\Illuminate\Mongez\Contracts\Testing\ResponseSchemaInterface;
use HZ\Illuminate\Mongez\Managers\Testing\UnitType;

class ResponseSchema extends ObjectType implements ResponseSchemaInterface
{
    /**
     * List of Unit types aliases
     * 
     * @var array
     */
    protected static array $unitTypesAliases = [];

    /**
     * Constructor
     * 
     * @param array $unitTypes
     */
    public function __construct(array $unitTypes)
    {
        $unitTypes = $this->getProperUnitTypes($unitTypes);
    }

    /**
     * Handle the given unit types
     * 
     * @param  array $unitTypes
     * @param  string $keyNamespace
     * @return array
     */
    protected function getProperUnitTypes(array $unitTypes, string $keyNamespace = ''): array
    {
        $finalUnitTypes = [];

        foreach ($unitTypes as $index => $unitType) {
            if ($unitType instanceof UnitType) {
                $finalUnitTypes[] = $unitType;
                continue;
            }

            // if the index is a string
            // then it will be the key
            // and the $unitType variable will be the unit type for that key
            if (is_string($unitType)) {
                $unitKey = $index;
                $unitTypeMethods = explode('|', $unitType);
                foreach ($unitTypeMethods as $methodIndex => $method) {
                    $unitTypeMethods[$methodIndex] = explode(':', $method);
                }

                $unitTypeWithArgs = array_shift($unitTypeMethods);

                $unitType = array_shift($unitTypeWithArgs);

                $unitTypeClassName = $this->getUnitTypeAlias($unitType);

                $finalUnitType = new $unitTypeClassName($unitKey, ...$unitTypeWithArgs);

                $finalUnitType->setKeyNamespace($keyNamespace);
            }
        }

        return $finalUnitTypes;
    }

    /**
     * Get unit type class name for the given unit type alias
     * 
     * @param  string $unitTypeAlias
     * @return string
     * @throws \Exception
     */
    protected function getUnitTypeAlias(string $unitTypeAlias): string
    {
        if (! static::$unitTypesAliases) {
            static::$unitTypesAliases = config('mongez.testing.unitTypes', []);
        }

        if (! isset(static::$unitTypesAliases[$unitTypeAlias])) {
            throw new Exception(sprintf('Call to undefined unit type alias %s', $unitTypeAlias));
        }

        return static::$unitTypesAliases[$unitTypeAlias];
    }
}
