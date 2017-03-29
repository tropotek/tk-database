<?php
namespace Tk\DataMap\Form;

use Tk\DataMap\Map;

/**
 * Class Boolean
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Boolean extends Map
{

    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return boolean
     */
    public function findPropertyValue($row)
    {
        $cname = $this->getColumnName();
        if (isset($row[$cname])) {
            return ($row[$cname] != $cname) ? false : true;
        }
        return null;
    }
    
    /**
     * Get the DB value
     * 
     * @param mixed $obj
     * @return string|null
     */
    public function findColumnValue($obj)
    {
        $pname = $this->getPropertyName();
        if ($this->propertyExists($obj, $pname)) {
            return $this->propertyValue($obj, $pname) ? $pname : '';
        }
        return null;
    }
    
}

