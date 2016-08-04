<?php
namespace Tk\DataMap;

/**
 * Class Iface
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
abstract class Map
{

    /**
     * @var string
     */
    protected $columnName = '';

    /**
     * @var string
     */
    protected $propertyName = '';

    /**
     * @var string
     */
    protected $tag = '';



    /**
     * __construct
     *
     * @param string $propertyName The object property to map the column to.
     * @param string $columnName (optional)The Db column name to map this property to. uses $propertyName if null
     */
    public function __construct($propertyName, $columnName = '')
    {
        $this->propertyName = $propertyName;
        if (!$columnName) {
            $columnName = $propertyName;
        }
        $this->columnName = $columnName;
    }

    /**
     * Return a value ready for insertion into the Model object
     *
     * @param array $row
     * @return string
     */
    abstract public function findPropertyValue($row);

    /**
     * Return a value ready for insertion into the storage (DB)
     *
     * @param \Tk\Db\Map\Model $obj
     * @return string
     */
    abstract public function findColumnValue($obj);


    /**
     * allows for finding private properties
     *
     * NOTE: Accessing private properties is possible, but care must be taken
     * if that private property was defined lower into the inheritance chain.
     * For example, if class A extends class B, and class B defines a private
     * property called 'foo', getProperty will throw a ReflectionException.
     *
     * Instead, you can loop over getParentClass until it returns false to
     * look for the private property, at which point you can access and/or
     * modify its value as needed. (modify this method if needed)
     *
     * @param $obj
     * @param $pname
     * @return bool
     */
    protected function propertyExists($obj, $pname)
    {
        try {
            $reflect = new \ReflectionClass($obj);
            $prop = $reflect->getProperty($pname);
            if ($prop) return true;
        } catch (\Exception $e) {}
        return false;
    }

    /**
     * Allows for getting private properties
     *
     * NOTE: For private properties of subclasses see the note above.,.
     *
     * @param $obj
     * @param $pname
     * @return mixed|null
     */
    protected function propertyValue($obj, $pname)
    {
        $reflect = new \ReflectionClass($obj);
        $prop = $reflect->getProperty($pname);
        if ($prop) {
            $prop->setAccessible(true);
            return $prop->getValue($obj);
        }
        return null;
    }

    /**
     * The object's instance property name
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * This is the data source (DB) column name.
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }


    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

}