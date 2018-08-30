<?php
namespace Tk\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
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
     * @param null|Pdo $db
     * @throws \Exception
     */
    public function __construct($db)
    {
        parent::__construct($db);
        $map = $this->getDbMap();
        if (count($map->getPropertyMaps('key'))) {
            $this->setPrimaryKey(current($map->getPropertyMaps('key'))->getColumnName());
        }
        $this->getFormMap();
    }

    /**
     * Override to return a valid DataMap
     * @return \Tk\DataMap\DataMap|null
     */
    public function getDbMap()
    {
        return null;
    }

    /**
     * Override to return a valid DataMap
     * @return \Tk\DataMap\DataMap|null
     */
    public function getFormMap()
    {
        return null;
    }

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
     * @throws \Exception
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
     * Input: This requires a Tk.Model or stdClass object as input
     *
     * Output: array (
     *   'tblColumn' => 'columnValue'
     * )
     *
     * @param Map\Model|\stdClass $obj
     * @param array $array
     * @return array
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function selectFrom($from = '', $where = '', $tool = null)
    {
        if ($tool && $tool->getOrderProperty()) {   // Do nothing if a property cannot be found in the tool
            $tool = clone $tool; // Clone this so the orderBy properties are not changed in the original tool object.
            $mapProperty = $this->getDbMap()->getPropertyMap($tool->getOrderProperty());
            if ($mapProperty) {
                $orderBy = $tool->getOrderBy();
                $orderBy = str_replace($tool->getOrderProperty(), $mapProperty->getColumnName(), $orderBy);
                $tool->setOrderBy($orderBy);
            }
        }
        return parent::selectFrom($from, $where, $tool);
    }

    /**
     * Create a sql query string from an array.
     * Handy for testing multiple values
     * EG:
     *   "a.type = 'Self Assessment' AND a.type != 'Testing' AND 'Thinking'"
     *
     * @param array|mixed $value
     * @param string $columnName
     * @param string $logic   Logical Operator
     * @param string $compare  Comparison Operator
     * @return string
     */
    public function makeMultiQuery($value, $columnName, $logic = 'OR', $compare = '=')
    {
        if (!is_array($value)) $value = array($value);
        $w = '';
        foreach ($value as $r) {
            if (!$r) continue;
            $w .= sprintf('%s %s %s %s ', $columnName, $compare, $this->getDb()->quote($r), $logic);
        }
        if ($w)
            $w = rtrim($w, ' '.$logic.' ');
        return $w;
    }

}