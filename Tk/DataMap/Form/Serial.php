<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\Map;

/**
 * Class Serial
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Serial extends Map
{

    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return string
     */
    public function findPropertyValue($row)
    {
        $cname = $this->getColumnName();
        if (isset($row[$cname])) {
            return unserialize(base64_decode($row[$cname]));
        }
        return '';
    }
    
    /**
     * Get the DB value
     * 
     * @param mixed $obj
     * @return string 
     */
    public function findColumnValue($obj)
    {
        $pname = $this->getPropertyName();
        if ($this->propertyExists($obj, $pname)) {
            return base64_encode(serialize($this->propertyValue($obj, $pname)));
        }
        return '';
    }

}

