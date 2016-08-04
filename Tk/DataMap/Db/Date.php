<?php
namespace Tk\DataMap\Db;

use Tk\DataMap\Map;

/**
 * Class Date
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Date extends Map
{

    /**
     * getPropertyValue
     * 
     * @param array $row
     * @return null|\DateTime
     */
    public function findPropertyValue($row)
    {
        $cname = $this->getColumnName();
        if (isset($row[$cname])) {
            return \Tk\Date::create($row[$cname]);
        }
        return null;
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
            $value = $this->propertyValue($obj, $pname);
            if ($value instanceof \DateTime) {
                return $value->format(\Tk\Date::ISO_DATE);
            }
        }
        return null;      // <<<----TODO null or 'NULL'
    }
    
}

