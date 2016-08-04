<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\Map;

/**
 * Class Decimal
 * Handle Float types
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Decimal extends Map
{

    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return float
     */
    public function findPropertyValue($row)
    {
        $cname = $this->getColumnName();
        if (isset($row[$cname])) {
            return (float)$row[$cname];
        }
        return 0.0;
    }
    
    /**
     * Get the DB value
     * 
     * @param mixed $obj
     * @return float
     */
    public function findColumnValue($obj)
    {
        $pname = $this->getPropertyName();
        if ($this->propertyExists($obj, $pname)) {
            return (float)$this->propertyValue($obj, $pname);
        }
        return 0.0;
    }
    
}

