<?php
namespace Tk\DataMap;

use Tk\Db\ModelInterface;

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

        /* @var Map $map */
        foreach ($this->getPropertyMaps() as $map) {
            if ($ignoreTag && $map->getTag() == $ignoreTag) continue;
            $map->loadObject($row, $object);
        }
        return $object;
    }

    /**
     * Using the datamap load an array with the values from an object
     *
     * @param ModelInterface|\stdClass $obj
     * @param array $array
     * @param string $ignoreTag
     * @return array
     */
    public function loadArray($obj, $array = array(), $ignoreTag = '')
    {
        /* @var $map Map */
        foreach ($this->getPropertyMaps() as $map) {
            if ($ignoreTag && $map->getTag() == $ignoreTag) continue;
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
     * Gets a property map by its name
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
     * Add a property to this map
     *
     * @param Map $propertyMap
     * @param string $tag
     */
    public function addPropertyMap($propertyMap, $tag = null)
    {
        if ($tag) $propertyMap->setTag($tag);
        $this->propertyMaps[$propertyMap->getPropertyName()] = $propertyMap;
    }

}