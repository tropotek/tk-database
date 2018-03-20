<?php
namespace Tk\DataMap\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Date extends Iface
{

    /**
     * @var string
     */
    protected  $format = 'Y-m-d H:i:s';

    /**
     * @param $format
     * @return $this
     */
    public function setDateFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Map an array column value to an object property value
     *
     * @param array $row
     * @param string $columnName
     * @return float|null
     */
    public function toPropertyValue($row, $columnName)
    {
        $value = parent::toPropertyValue($row, $columnName);
        if ($value !== null) {
            // TODO: parse from the $format
            $value = \Tk\Date::create($value);
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
            if ($value instanceof \DateTime) {
                return $value->format($this->format);
            }
        }
        return $value;
    }
    
}

