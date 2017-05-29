<?php
namespace Tk\DataMap\Form;


/**
 * Class Date
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Date extends \Tk\DataMap\Db\Date
{

    /**
     * @var string
     */
    protected  $format = 'd/m/Y';

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

