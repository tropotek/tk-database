<?php
namespace Tk\DataMap;

use Tk\Db\Map\Model;

/**
 * Class DataMap
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class DataMap
{

    /**
     * @var Map[]|array
     */
    private $propertyMaps = array();


    /**
     * Using the datamap load an object with the values from an array
     *
     * @param array $row
     * @param null|Model $object
     * @param string $ignoreTag
     * @return \stdClass|Model
     * @throws \Tk\Db\Exception
     */
    public function loadObject($row, $object = null, $ignoreTag = '')
    {
        if ($object && !$object instanceof Model) {
            throw new \Tk\Db\Exception('Cannot load a non Model object.');
        }
        if (!$object) {
            $object = new \stdClass();
        }
        $reflect = new \ReflectionClass($object);
        /* @var Map $map */
        foreach ($this->getProperties() as $map) {
            if ($ignoreTag && $map->getTag() == $ignoreTag) continue;
            if (!array_key_exists($map->getColumnName(), $row)) continue;
            $pname = $map->getPropertyName();
            if ($object instanceof \stdClass) {
                $object->$pname = $map->findPropertyValue($row);
                continue;
            }
            $prop = $reflect->getProperty($pname);
            $prop->setAccessible(true);
            $prop->setValue($object, $map->findPropertyValue($row));
        }
        return $object;
    }

    /**
     * Using the datamap load an array with the values from an object
     *
     * @param Model|\stdClass $obj
     * @param array $array
     * @param string $ignoreTag
     * @return array
     */
    public function loadArray($obj, $array = array(), $ignoreTag = '')
    {
        /* @var $map Map */
        foreach ($this->getProperties() as $map) {
            if ($ignoreTag && $map->getTag() == $ignoreTag) continue;
            $cname = $map->getColumnName();
            $value = $map->findColumnValue($obj);
            $array[$cname] = $value;
        }
        return $array;
    }

    /**
     * Gets the list of property mappers.
     *
     * @param string $tag
     * @return array
     */
    public function getProperties($tag = null)
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
     * Gets a property map by its name
     *
     * @param string $name
     * @return Map
     */
    public function getProperty($name)
    {
        if (isset($this->propertyMaps[$name])) {
            return $this->propertyMaps[$name];
        }
    }

    /**
     * Add a property to this map
     *
     * @param Map $propertyMap
     * @param string $tag
     */
    public function addProperty($propertyMap, $tag = null)
    {
        if ($tag) $propertyMap->setTag($tag);
        $this->propertyMaps[$propertyMap->getPropertyName()] = $propertyMap;
    }

    /**
     * Return the property map at the top of the list
     *
     * @param string $tag
     * @return Map|null
     */
    public function currentProperty($tag = null)
    {
        return current($this->getProperties($tag));
    }
}