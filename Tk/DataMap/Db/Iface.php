<?php
namespace Tk\DataMap\Db;



/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
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
     */
    public function toColumnValue($object, $propertyName)
    {
        $v = parent::toColumnValue($object, $propertyName);
        if ($v === null) {
            $v = 'NULL';
        }
        return $v;
    }

}