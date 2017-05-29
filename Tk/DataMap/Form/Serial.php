<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\Map;

/**
 * Class Serial
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Serial extends \Tk\DataMap\Db\Serial
{

//    /**
//     * Map an array column value to an object property value
//     *
//     * @param array $row
//     * @param string $columnName
//     * @return mixed|null
//     */
//    public function toPropertyValue($row, $columnName)
//    {
//        $value = parent::toPropertyValue($row, $columnName);
//        if ($value) {
//            $value = unserialize(base64_decode($value));
//        }
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
//        if ($value) {
//            $value = base64_encode(serialize($value));
//        }
//        return $value;
//    }

}

