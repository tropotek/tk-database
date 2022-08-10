<?php
namespace Tk\DataMap\Form;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Boolean extends Iface
{

    /**
     * Map an array column value to an object property value
     *
     * @param array $row
     * @param string $columnName
     * @return bool|null
     */
    public function toPropertyValue($row, $columnName)
    {
        $value = parent::toPropertyValue($row, $columnName);
        if ($value !== null && $value !== '' && !is_bool($value)) {
            if ($value == $columnName || strtolower($value) == 'yes' || strtolower($value) == 'true' || ((int)$value)) {
                return true;
            } else {
                return false;
            }
        }
        return $value;
    }

    /**
     * Map an object property value to an array column value
     *
     * @param mixed $object
     * @param string $propertyName
     * @return string|null
     */
    public function toColumnValue($object, $propertyName)
    {
        $value = parent::toColumnValue($object, $propertyName);
        if ($value !== null) {
            $value = ((int)$value != 0) ? $propertyName : '';
        }
        return $value;
    }

    
}

