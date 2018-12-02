<?php
namespace Tk\DataMap;

use Tk\Db\ModelInterface;

/**
 * Class DataMap
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class DataMap
{

    /**
     * @var Map[]|array
     */
    private $propertyMaps = array();

    /**
     * Same as propertyMaps but the ky is the column name for fast searching
     * @var Map[]|array
     */
    private $columnMaps = array();


    /**
     * Using the datamap load an object with the values from an array
     * If the property does not exist as a map the value is added to
     * the object as a dynamic property.
     *
     * @link http://krisjordan.com/dynamic-properties-in-php-with-stdclass
     * @param array $row
     * @param null|ModelInterface $object
     * @param string $ignoreTag
     * @return \stdClass|ModelInterface
     * @throws \Tk\Db\Exception
     */
    public function loadObject($row, $object = null, $ignoreTag = '')
    {
        if ($object && !$object instanceof ModelInterface) {
            throw new \Tk\Db\Exception('Cannot load a non Model object.');
        }
        if ($object === null) $object = new \stdClass();
        foreach ($row as $k => $v) {
            $map = $this->getPropertyMapByColumnName($k);
            if ($ignoreTag && $map->getTag() == $ignoreTag) continue;
            if ($map) {
                $map->loadObject($row, $object);
            } else {
                $object->$k = $v;
            }
        }

//        /* @var Map $map */
//        foreach ($this->getPropertyMaps() as $map) {
//            if ($ignoreTag && $map->getTag() == $ignoreTag) continue;
//            if (!$map->hasColumn($row)) continue;
//            $map->loadObject($row, $object);
//        }
        return $object;
    }

    /**
     * Using the datamap load an array with the values from an object
     *
     * @param ModelInterface|\stdClass $obj
     * @param array $array
     * @param string $ignoreTag
     * @return array
     * @throws \ReflectionException
     */
    public function loadArray($obj, $array = array(), $ignoreTag = '')
    {
        /* @var $map Map */
        foreach ($this->getPropertyMaps() as $map) {
            if ($ignoreTag && $map->getTag() == $ignoreTag) continue;
            //if (!$map->hasProperty($obj)) continue;       // TODO: not sure this is needed here, keep an eye on it
            $array = $map->loadArray($obj, $array);
        }
        return $array;
    }

    /**
     * Gets the list of property mappers.
     *
     * @param string $tag
     * @return array
     */
    public function getPropertyMaps($tag = null)
    {
        if ($tag) {
            $list = array();
            foreach ($this->propertyMaps as $map) {
                if ($map->getTag() == $tag)
                    $list[] = $map;
            }
            return $list;
        }
        return $this->propertyMaps;
    }

    /**
     * Gets a property map by its property name
     *
     * @param string $name
     * @return Map
     */
    public function getPropertyMap($name)
    {
        if (isset($this->propertyMaps[$name])) {
            return $this->propertyMaps[$name];
        }
    }

    /**
     * Gets a property map by its column name
     *
     * @param string $columnName
     * @return Map
     */
    public function getPropertyMapByColumnName($columnName)
    {
        if (isset($this->columnMaps[$columnName])) {
            return $this->columnMaps[$columnName];
        }
    }

    /**
     * Add a property to this map
     *
     * @param Map $propertyMap
     * @param string $tag
     * @return Map
     */
    public function addPropertyMap($propertyMap, $tag = null)
    {
        if ($tag) $propertyMap->setTag($tag);
        $this->propertyMaps[$propertyMap->getPropertyName()] = $propertyMap;
        $this->columnMaps[$propertyMap->getColumnName()] = $propertyMap;
        return $propertyMap;
    }

}