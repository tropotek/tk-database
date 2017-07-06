<?php
namespace Tk\Db\Map;

use Tk\Db\PdoStatement;
use Tk\Db\Tool;

/**
 * This objected is essentially a wrapper around the PdoStatement object with added features
 * such as holding the Model Mapper, and Db\Tool objects.
 *
 * It automatically maps an objects data if the Model has the magic methods available
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ArrayObject implements \Iterator, \Countable
{

    /**
     * @var Mapper
     */
    protected $mapper = null;

    /**
     * The raw database rows as associative arrays.
     * @var PdoStatement
     */
    protected $statement = null;

    /**
     * The raw database rows as associative arrays.
     * @var array
     */
    protected $rows = null;

    /**
     * @var int
     */
    protected $idx = 0;

    /**
     * The total number of rows found without LIMIT clause
     * @var int
     */
    protected $foundRows = 0;

    /**
     * This may or may not exist depending on the source of the array data
     * @var Tool
     */
    protected $tool = null;



    /**
     * Create a DB array list object
     *
     * @param array $rows
     */
    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    /**
     * @param Mapper $mapper
     * @param PdoStatement $statement
     * @param Tool $tool
     * @return ArrayObject
     * @todo: remove the need for the statement and just use the array?????
     */
    static function createFromMapper(Mapper $mapper, PdoStatement $statement, $tool = null)
    {
        // TODO: One day \PDO may be able to do this serially, this is a big memory hog...
        //       Currently we cannot subclass the PDOStatement::fetch...() methods correctly [php: 5.6.27]
        // NOTE: For large datasets that could fill the memory, this object should not be used
        //       instead get statement and manually iterate the data.
        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $obj = new self($rows);
        $obj->foundRows = $mapper->getDb()->countFoundRows($statement->queryString);
        $obj->mapper = $mapper;
        $obj->statement = $statement;
        if (!$tool) {
            $tool = new Tool();
        }
        $obj->tool = $tool;
        return $obj;
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {
        $this->statement = null;
        $this->tool = null;
    }

    /**
     * Return the tool object associated to this result set.
     * May not exist.
     *
     * @return Tool
     */
    public function getTool()
    {
        return $this->tool;
    }

    /**
     * Return the tool object associated to this result set.
     * May not exist.
     *
     * @return Mapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * 
     * @return PdoStatement
     */
    public function getStatement()
    {
        return $this->statement;
    }


    /**
     * Get the result rows as a standard array.
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param int $i
     * @return mixed
     */
    public function get($i)
    {
        if (isset($this->rows[$i])) {
            if ($this->mapper) {
                //return $this->mapper->loadObject($this->rows[$i]);
                return $this->mapper->map($this->rows[$i]);
            }
            return (object)$this->rows[$i];
        }
        return null;
    }

    /**
     * Get the total rows available count.
     *
     * This value will be the available count without a limit.
     *
     * @return int
     */
    public function getFoundRows()
    {
        return $this->foundRows;
    }


    //   Iterator Interface

    /**
     * rewind
     *
     * @return $this
     */
    public function rewind()
    {
        $this->idx = 0;
        return $this;
    }

    /**
     * Return the element at the current index
     *
     * @return \Tk\Db\ModelInterface
     */
    public function current()
    {
        return $this->get($this->idx);
    }

    /**
     * Increment the counter
     *
     * @return \Tk\Db\ModelInterface
     */
    public function next()
    {
        $this->idx++;
        return $this->current();
    }

    /**
     * get the key value
     *
     * @return int
     */
    public function key()
    {
        return $this->idx;
    }

    /**
     * Valid
     *
     * @return bool
     */
    public function valid()
    {
        if ($this->current()) {
            return true;
        }
        return false;
    }

    //   Countable Interface

    /**
     * Count
     *
     * @return int
     */
    public function count()
    {
        return count($this->rows);
    }

    /**
     * If the keyField and/or value field are set then the this will
     * return the the array with a key and the required value.
     *
     * @param null $valueField
     * @param null $keyField
     * @return array
     */
    public function toArray($valueField = null, $keyField = null)
    {
        $arr = array();
        foreach($this as $k => $obj) {
            $v = $obj;
            if ($valueField && array_key_exists($valueField, get_object_vars($obj))) {
                $v = $obj->$valueField;
            }
            if ($keyField && array_key_exists($keyField, get_object_vars($obj))) {
                $k = $obj->$keyField;
            }
            $arr[$k] = $v;
        }
        return $arr;
    }
}
