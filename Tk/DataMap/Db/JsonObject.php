<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\Map;

/**
 * Class String
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class JsonObject extends Map
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
            return  json_decode($row[$cname]);
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
            return json_encode($this->propertyValue($obj, $pname));
        }
        return '';
    }
    
}

