<?php
namespace Tk\DataMap\Form;


/**
 * Class Number
 * Handle Integer types
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Integer extends \Tk\DataMap\Db\Integer
{

//    /**
//     * Map an array column value to an object property value
//     *
//     * @param array $row
//     * @param string $columnName
//     * @return int|null
//     */
//    public function toPropertyValue($row, $columnName)
//    {
//        $value = parent::toPropertyValue($row, $columnName);
//        if ($value !== null) $value = (int)$value;
//        return $value;
//    }
//
//    /**
//     * Map an object property value to an array column value
//     *
//     * @param mixed $object
//     * @param string $propertyName
//     * @return string|null
//     */
//    public function toColumnValue($object, $propertyName)
//    {
//        $value = parent::toColumnValue($object, $propertyName);
//        if ($value !== null) $value .= '';
//        return $value;
//    }
    
}

