<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\Map;

/**
 * Class Number
 * Handle Integer types
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Number extends Map
{

    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return int
     */
    public function findPropertyValue($row)
    {
        $cname = $this->getColumnName();
        if (isset($row[$cname])) {
            return (int)$row[$cname];
        }
        return 0;
    }
    
    /**
     * Get the DB value
     * 
     * @param mixed $obj
     * @return int
     */
    public function findColumnValue($obj)
    {
        $pname = $this->getPropertyName();
        if ($this->propertyExists($obj, $pname)) {
            return (int)$this->propertyValue($obj, $pname);
        }
        return 0;       //TODO: should we return null instead if a value does not exist???? This would support null values in the DB
    }
    
}

