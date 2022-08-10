<?php
namespace Tk\DataMap\Form;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Money extends Iface
{


    /**
     * @var string
     */
    protected  $currencyCode = 'AUD';


    /**
     * Map an array column value to an object property value
     *
     * @param array $row
     * @param string $columnName
     * @return \Tk\Money|null
     */
    public function toPropertyValue($row, $columnName)
    {
        $value = parent::toPropertyValue($row, $columnName);
        if ($value !== null && !$value instanceof \Tk\Money) {
            $value = \Tk\Money::parseFromString($value, \Tk\Currency::getInstance($this->currencyCode));
        }
        return $value;
    }

    /**
     * Map an object property value to an array column value
     *
     * @param mixed $object
     * @param string $propertyName
     * @return int|null
     * @throws \ReflectionException
     */
    public function toColumnValue($object, $propertyName)
    {
        $value = parent::toColumnValue($object, $propertyName);
        if ($value !== null && $value instanceof \Tk\Money) {
            return $value->toFloatString();
        }
        return $value;
    }

}

