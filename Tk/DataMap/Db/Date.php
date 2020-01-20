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
     * Valid date format `2000-12-31`
     */
    const FORMAT_DATE = 'Y-m-d';

    /**
     * Valid date format `2000-12-31 23:59:59`
     */
    const FORMAT_DATETIME = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    protected  $format = self::FORMAT_DATETIME;



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
     * @return \DateTime|null
     */
    public function toPropertyValue($row, $columnName)
    {
        $value = parent::toPropertyValue($row, $columnName);
        // This date is assumed as null
        if ($value == '0000-00-00 00:00:00') $value = null;
        if ($value != null) {
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
        if ($value != null) {
            if ($value instanceof \DateTime) {
                return $value->format($this->format);
            }
        }
        return $value;
    }
    
}

