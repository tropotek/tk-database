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
     * @var string
     */
    private $encryptKey = null;


    /**
     * TextEncrypt constructor.
     *
     * @param string $propertyName
     * @param string $columnName
     */
    public function __construct($propertyName, $columnName = '')
    {
        parent::__construct($propertyName, $columnName);

    }

    /**
     * @param $key
     */
    public function setEncryptKey($key)
    {
        $this->encryptKey = $key;
    }

    /**
     * Map an array column value to an object property value
     *
     * @param array $row
     * @param string $columnName
     * @return mixed|null
     */
    public function toPropertyValue($row, $columnName)
    {
        $value = parent::toPropertyValue($row, $columnName);
        if ($value) {
            $value = \Tk\Encrypt::create($this->encryptKey)->decode($value);
        }
        return $value;
    }

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
        if ($value) {
            $value = \Tk\Encrypt::create($this->encryptKey)->encode($value);
        }
        return $value;
    }
    
}

