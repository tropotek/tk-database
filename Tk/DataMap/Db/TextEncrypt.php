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
class TextEncrypt extends Map
{
    /**
     * @var \Tk\Encrypt
     */
    private $encrypt = '';


    /**
     * TextEncrypt constructor.
     *
     * @param string $propertyName
     * @param string $columnName
     * @param null|\Tk\Encrypt $encrypt If null lthe default encrypt object will be created
     */
    public function __construct($propertyName, $columnName = '', $encrypt = null)
    {
        parent::__construct($propertyName, $columnName);
        if (!$encrypt) {
            $encrypt = new \Tk\Encrypt();
        }
        $this->encrypt = $encrypt;
    }

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
            return $this->encrypt->decode($row[$cname]);
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
            return $this->encrypt->decode($this->propertyValue($obj, $pname));
        }
        return '';
    }
    
}

