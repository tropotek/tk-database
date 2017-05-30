<?php
namespace Tk\DataMap\Form;


/**
 * Class Date
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Date extends \Tk\DataMap\Map
{

    /**
     * @var string
     */
    protected  $format = 'd/m/Y';

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
            $value = \Tk\Date::createFormDate($value);
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
        if ($value !== null && $value instanceof \DateTime) {
            return $value->format($this->format);
        }
        return $value;
    }

}

