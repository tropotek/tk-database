<?php
namespace Tk\Db;


/**
 * Class Mapper
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
abstract class Mapper extends \Tk\Db\Map\Mapper
{

    /**
     * @var \Tk\DataMap\DataMap
     */
    protected $dbMap = null;

    /**
     * @var \Tk\DataMap\DataMap
     */
    protected $formMap = null;

    /**
     * Mapper constructor.
     * @param null|Pdo $db
     */
    public function __construct($db)
    {
        parent::__construct($db);
        $this->getDbMap();
        $this->getFormMap();
    }

    /**
     *
     * @return \Tk\DataMap\DataMap
     */
    abstract public function getDbMap();

    /**
     *
     * @return \Tk\DataMap\DataMap
     */
    abstract public function getFormMap();


    /**
     * Map the data from a DB row to the required object
     *
     * Input: array (
     *   'tblColumn' => 'columnValue'
     * )
     *
     * Output: Should return an \stdClass or \Tk\Model object
     *
     * @param array $row
     * @param null|mixed $obj If null then \stdClass will be returned
     * @return \stdClass|Map\Model
     * @since 2.0.0
     */
    public function map($row, $obj = null)
    {
        if (!$obj) {
            $class = $this->getModelClass();
            $obj = new $class();
        }
        return $this->getDbMap()->loadObject($row, $obj);
    }

    /**
     * Un-map an object to an array ready for DB insertion.
     * All fields and types must match the required DB types.
     *
     * Input: This requires a \Tk\Model or \stdClass object as input
     *
     * Output: array (
     *   'tblColumn' => 'columnValue'
     * )
     *
     * @param Map\Model|\stdClass $obj
     * @param array $array
     * @return array
     * @since 2.0.0
     */
    public function unmap($obj, $array = array())
    {
        return $this->getDbMap()->loadArray($obj, $array);
    }


    /**
     * Map the form fields data to the object
     *
     * @param array $row
     * @param mixed $obj
     * @param string $ignore
     * @return mixed
     */
    public function mapForm($row, $obj = null, $ignore = 'key')
    {
        if (!$obj) {
            $class = $this->getModelClass();
            $obj = new $class();
        }
        return $this->getFormMap()->loadObject($row, $obj, $ignore);
    }

    /**
     * Unmap the object to an array for the form fields
     *
     * @param mixed $obj
     * @param array $array
     * @return array
     */
    public function unmapForm($obj, $array = array())
    {
        return $this->getFormMap()->loadArray($obj, $array);
    }

    /**
     * Override this to modify the tool's orderBy in-case the Model property has been used instead of the
     * mapped DB column name
     *
     * @param string $from
     * @param string $where
     * @param null|\Tk\Db\Tool $tool
     * @return Map\ArrayObject
     */
    public function selectFrom($from = '', $where = '', $tool = null)
    {
        if ($tool && $tool->getOrderProperty()) {   // Do nothing if a property cannot be found in the tool
            $mapProperty = $this->getDbMap()->getProperty($tool->getOrderProperty());
            if ($mapProperty) {
                $orderBy = $tool->getOrderBy();
                $orderBy = str_replace($tool->getOrderProperty(), $mapProperty->getColumnName(), $orderBy);
                $tool->setOrderBy($orderBy);
            }
        }
        return parent::selectFrom($from, $where, $tool);
    }




}