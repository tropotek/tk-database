<?php
namespace Tk\DataMap\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Text extends Iface
{

    /**
     * Map an array column value to an object property value
     *
     * @param array $row
     * @param string $columnName
     * @return string|null
     */
    public function toPropertyValue($row, $columnName)
    {
        $value = parent::toPropertyValue($row, $columnName);
        if ($value !== null) {
            $value .= '';
        }
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
        if ($value !== null) {
            $value .= '';
        }
        return $value;
    }
    
}

