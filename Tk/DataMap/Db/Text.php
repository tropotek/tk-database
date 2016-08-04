<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\Map;

/**
 * Class Text
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Text extends Map
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
            return $row[$cname];
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
            return $this->propertyValue($obj, $pname);
        }
        return '';
    }
    
}

