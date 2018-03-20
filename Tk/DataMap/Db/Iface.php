<?php
namespace Tk\DataMap\Db;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Iface extends \Tk\DataMap\Map
{

    /**
     * Map an object property value to an array column value
     * Returns null if property not found or value is null
     *
     * @param mixed $object
     * @param string $propertyName
     * @return string|null
     * @throws \ReflectionException
     */
    public function toColumnValue($object, $propertyName)
    {
        $v = parent::toColumnValue($object, $propertyName);
        // TODO: We should not need this null values should be converted into NULL in the PDO
//        if ($v === null) {
//            $v = 'NULL';
//        }
        return $v;
    }

}