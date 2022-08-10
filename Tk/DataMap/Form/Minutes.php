<?php
namespace Tk\DataMap\Form;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Minutes extends Iface
{


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
        if ($value !== null && preg_match('/^([0-9]+):([0-9]+)$/', $value, $regs)) {
            $value = (int)($regs[1] * 60) + (int)$regs[2];
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
            $h = $value/60;
            $m = $value%60;
            $str = sprintf('%d:%02d', $h, $m);
            return $str;
        }
        return $value;
    }

}

