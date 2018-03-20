<?php
namespace Tk\DataMap\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class JsonArray extends Iface
{
    /**
     * Map an array column value to an object property value
     *
     * @param array $row
     * @param string $columnName
     * @return mixed|null
     */
    public function toPropertyValue($row, $columnName)
    {
        $value = parent::toPropertyValue($row, $columnName);
        if ($value) {
            $value = json_decode($value);
        }
        if (!$value) $value = array();  // Ensure an empty array is returned
        if ($value === null) $value = null;
        return $value;
    }

    /**
     * Map an object property value to an array column value
     *
     * @param mixed $object
     * @param string $propertyName
     * @return string|null
     * @throws \ReflectionException
     */
    public function toColumnValue($object, $propertyName)
    {
        $value = parent::toColumnValue($object, $propertyName);
        // Fixes bug where json_encode returns an array object instead of a string for empty arrays
        if (is_array($value) && !count($value)) return '';
        if ($value) {
            $value = json_encode($value);
        }
        return $value;
    }
    
}

