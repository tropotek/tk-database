<?php
namespace Tk\Db\Map;

use Tk\Db\Pdo;
use Tk\Db\Exception;
use Tk\Db\Tool;

/**
 * Class Mapper
 *
 * Some reserved column names and assumed meanings:
 *  - `id`       => An integer that is assumed to be the records primary key
 *                  foreign keys are assumed to be named `<foreign_table>_id`
 *  - `modified` => A timestamp that gets incremented on updates
 *  - `created`  => A timestamp not really reserved but assumed
 *  - `del`      => If it exists the records are marked `del` = 1 rather than deleted
 *
 * If your columns conflict, then you should modify the mapper or DB accordingly
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Mapper implements Mappable
{
    static $DB_PREFIX = '';
    
    /**
     * @var Mapper[]
     */
    protected static $instance = array();

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $modelClass = '';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var Pdo
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $alias = 'a';

    /**
     * @var null
     */
    protected $tableInfo = null;

    /**
     *
     * @var string
     */
    protected $markDeleted = '';


    /**
     * Mapper constructor.
     *
     * @param null|Pdo $db
     */
    public function __construct($db = null)
    {
        if (!$db)
            $db = Pdo::getInstance();
        $this->setDb($db);
        $this->setModelClass($this->getDefaultModelClass());
        $this->setTable($this->getDefaultTable());
    }

    /**
     * Get/Create an instance of a data mapper.
     *
     * @param Pdo $db
     * @return static
     */
    static function create($db = null)
    {
        $mapperClass = get_called_class(); // PHP >= v5.5 use: $mapperClass = static::class;
        if (!isset(self::$instance[$mapperClass])) {
            self::$instance[$mapperClass] = new $mapperClass($db);
        }
        return self::$instance[$mapperClass];
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
     * @return \stdClass|\Tk\Db\Map\Model
     * @since 2.0.0
     */
    public function map($row, $obj = null)
    {
        return (object)$row;
    }

    /**
     * Un-map an object to an array ready for DB insertion.
     * All fields and types must match the required DB types.
     *
     * Input: This requires a \Tk\Db\Map\Model or \stdClass object as input
     *
     * Output: array (
     *   'tblColumn' => 'columnValue'
     * )
     *
     * @param \Tk\Db\Map\Model|\stdClass $obj
     * @param array $array
     * @return array
     * @since 2.0.0
     */
    public function unmap($obj, $array = array())
    {
        return (array)$obj;
    }


    /**
     * Insert
     *
     * @param mixed $obj
     * @return int Returns the new insert id
     */
    public function insert($obj)
    {
        $bind = $this->unmap($obj);
        if (isset($bind[$this->getPrimaryKey()]))
            unset($bind[$this->getPrimaryKey()]);
        $keys = array_keys($bind);
        $cols = implode(', ', $this->getDb()->quoteParameterArray($keys));
        $values = implode(', :', array_keys($bind));
        foreach ($bind as $col => $value) {
            if ($col == 'modified' || $col == 'created') {
                //$value = date('Y-m-d H:i:s.u');
                $value = date('Y-m-d H:i:s');
            }
            unset($bind[$col]);
            $bind[':' . $col] = $value;
        }
        $sql = 'INSERT INTO ' . $this->getDb()->quoteParameter($this->table) . ' (' . $cols . ')  VALUES (:' . $values . ')';

        $this->getDb()->prepare($sql)->execute($bind);

        $seq = '';
        if ($this->getDb()->getDriver() == 'pgsql') {   // Generate the seq key for Postgres only
            $seq = $this->getTable().'_'.$this->getPrimaryKey().'_seq';
        }
        $id = (int)$this->getDb()->lastInsertId($seq);
        return $id;
    }

    /**
     *
     * @param $obj
     * @return int
     */
    public function update($obj)
    {
        $pk = $this->getPrimaryKey();
        $bind = $this->unmap($obj);
        $set = array();
        foreach ($bind as $col => $value) {
            if ($col == 'modified') {
                $value = date('Y-m-d H:i:s');
            }
            unset($bind[$col]);
            $bind[':' . $col] = $value;
            $set[] = $this->getDb()->quoteParameter($col) . ' = :' . $col;
        }
        $where = $this->getDb()->quoteParameter($pk) . ' = ' . $bind[':'.$pk];
        $sql = 'UPDATE ' . $this->getDb()->quoteParameter($this->table) . ' SET ' . implode(', ', $set) . (($where) ? ' WHERE ' . $where : ' ');

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($bind);

        return $stmt->rowCount();
    }

    /**
     * Save the object, let the code decide weather to insert ot update the db.
     *
     *
     * @param Model $obj
     * @throws \Exception
     */
    public function save($obj)
    {
        $pk = $this->getPrimaryKey();
        if (!property_exists($obj, $pk)) {
            throw new \Exception('No valid primary key found');
        }
        if ($obj->$pk == 0) {
            $this->insert($obj);
        } else {
            $this->update($obj);
        }
    }

    /**
     * Delete object
     *
     * @param Model $obj
     * @return int
     */
    public function delete($obj)
    {
        $pk = $this->getPrimaryKey();
        $where = $this->getDb()->quoteParameter($pk) . ' = ' . $obj->$pk;
        if ($where) {
            $where = 'WHERE ' . $where;
        }
        $sql = sprintf('DELETE FROM %s %s LIMIT 1', $this->getDb()->quoteParameter($this->table), $where);
        if ($this->markDeleted) {
            $sql = sprintf('UPDATE %s SET %s = 1 %s LIMIT 1', $this->getDb()->quoteParameter($this->table), $this->getDb()->quoteParameter($this->getMarkDeleted()), $where);
        }
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * A select query using a prepared statement. Less control
     *
     *
     * @param array $bind
     * @param Tool $tool
     * @param string $boolOperator
     * @return ArrayObject
     * @see http://www.sitepoint.com/integrating-the-data-mappers/
     * @deprecated TODO: See if we need this ?
     */
    public function selectPrepared($bind = array(), $tool = null, $boolOperator = 'AND')
    {
        if (!$tool instanceof Tool) {
            $tool = new Tool();
        }

        $alias = $this->getAlias();
        if ($alias) {
            $alias = $alias . '.';
        }

        if ($this->getMarkDeleted() && !array_key_exists($this->getMarkDeleted(), $bind)) {
            $bind[$this->getMarkDeleted()] = '0';
        }

        $from = $this->getTable() . ' ' . $this->getAlias();
        $where = array();
        if ($bind) {
            foreach ($bind as $col => $value) {
                unset($bind[$col]);
                $bind[':' . $col] = $value;
                $where[] = $alias. $this->getDb()->quoteParameter($col) . ' = :' . $col;
            }
        }
        $where = implode(' ' . $boolOperator . ' ', $where);

        // Build Query
        $foundRowsKey = '';
        if ($this->getDb()->getDriver() == 'mysql') {
            $foundRowsKey = 'SQL_CALC_FOUND_ROWS';
        }
        $sql = sprintf('SELECT %s %s * FROM %s %s ',
            $foundRowsKey,
            $tool->isDistinct() ? 'DISTINCT' : '',
            $from,
            ($bind) ? ' WHERE ' . $where : ' '
        );
        $sql .= $tool->toSql();

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($bind);

        $arr = ArrayObject::createFromMapper($this, $stmt, $tool);
        return $arr;
    }

    /**
     * Select a number of elements from a database
     *
     * @param string $where EG: "`column1`=4 AND `column2`='string'"
     * @param Tool $tool
     * @return ArrayObject
     */
    public function select($where = '', $tool = null)
    {
        return $this->selectFrom('', $where, $tool);
    }

    /**
     * Select a number of elements from a database
     *
     * @param string $from
     * @param string $where EG: "`column1`=4 AND `column2`='string'"
     * @param Tool $tool
     * @return ArrayObject
     */
    public function selectFrom($from = '', $where = '', $tool = null)
    {
        if (!$tool instanceof Tool) {
            $tool = new Tool();
        }

        $alias = $this->getAlias();
        if ($alias) {
            $alias = $alias . '.';
        }

        if (!$from) {
            $from = sprintf('%s %s', $this->getDb()->quoteParameter($this->getTable()), $this->getAlias());
        }

        if ($this->getMarkDeleted() && strstr($where, $this->getDb()->quoteParameter($this->getMarkDeleted())) === false) {
            if ($where) {
                $where = sprintf('%s%s = 0 AND %s ', $alias, $this->getDb()->quoteParameter($this->getMarkDeleted()), $where);
            } else {
                $where = sprintf('%s%s = 0 ', $alias, $this->getDb()->quoteParameter($this->getMarkDeleted()));
            }
        }

        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $distinct = '';
        if ($tool->isDistinct()) {
            $distinct = 'DISTINCT';
        }

        // OrderBy, GroupBy, Limit, etc
        $toolStr = '';
        if ($tool) {
            $toolStr = $tool->toSql($alias, $this->getDb());
        }
        $foundRowsKey = '';
        if ($this->getDb()->getDriver() == 'mysql') {
            $foundRowsKey = 'SQL_CALC_FOUND_ROWS';
        }

        $sql = sprintf('SELECT %s %s %s* FROM %s %s %s ', $foundRowsKey, $distinct, $alias, $from, $where, $toolStr);

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();

        $arr = ArrayObject::createFromMapper($this, $stmt, $tool);
        return $arr;
    }

    /**
     *
     * @param $id
     * @return Model|null
     */
    public function find($id)
    {
        $where = sprintf('%s = %s', $this->getDb()->quoteParameter($this->getPrimaryKey()), (int)$id);
        $list = $this->select($where);
        return $list->current();
    }

    /**
     * Find all objects in DB
     *
     * @param Tool $tool
     * @return ArrayObject
     */
    public function findAll($tool = null)
    {
        return $this->select('', $tool);
    }




    /**
     * Generate the default model class from this mapper class
     * if a specific model class is required then use ::setModelClass()
     *
     * @return string
     */
    protected function getDefaultModelClass()
    {
        $mapperClass = get_class($this);
        if (preg_match('/(.+)(Map|Mapper)$/', $mapperClass, $regs)) {
            return $regs[1];
        }
        return '';
    }

    /**
     * Generate the default table class.
     * If a specific table name is required then use ::setTable()
     *
     * @return mixed|string
     */
    protected function getDefaultTable()
    {
        if ($this->modelClass) {
            $arr = explode('\\', $this->modelClass);
            $table = array_pop($arr);
            $table = $this->toDbProperty($table);
            return $table;
        }
        return '';
    }

    /**
     * If set to a column name then only mark the row deleted do not delete
     *
     * @param string $col Set to null string to remove this as an option
     * @return $this
     */
    public function setMarkDeleted($col = '')
    {
        $this->markDeleted = $col;
        return $this;
    }

    /**
     * Returns the name of the column to mark deleted. (update col to 1)
     * returns null if we are to physically delete the record
     *
     * @return string
     */
    public function getMarkDeleted()
    {
        return $this->markDeleted;
    }


    /**
     * Convert camelCase property names to underscore db property name
     *
     * EG: 'someProperty' is converted to 'some_property'
     *
     * @param $property
     * @return string
     */
    public function toDbProperty($property)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $property)), '_');
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @param string $modelClass
     * @return $this
     */
    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    /**
     * Get the table alias used for multiple table queries.
     * The default alias is 'a'
     *
     *   EG: a.`id`
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set the table alias
     *
     * @param string $alias
     * @return $this
     * @throws Exception
     */
    public function setAlias($alias)
    {
        $alias = trim($alias, '.');
        if (!preg_match('/[a-z0-9_]+/i', $alias))
            throw new Exception('Invalid Table alias value');
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     * @param bool $addPrefix   Set this to false to not add the table prefix.
     * @return $this
     */
    public function setTable($table, $addPrefix = true)
    {
        if ($addPrefix && self::$DB_PREFIX) {
            $table = self::$DB_PREFIX . $table;
        }

        $this->table = $table;
        if ($this->getDb()->tableExists($this->table)) {
            $this->tableInfo = $this->getDb()->getTableInfo($this->table);
        }
        return $this;
    }

    /**
     * Get the table db prefix if one is set.
     *
     * @return string
     */
    public function getPrefix()
    {
        return self::$DB_PREFIX;
    }

    /**
     * If a colum name is supplied then that column info is returned
     *
     * @param null|string $column
     * @return null|array
     */
    public function getTableInfo($column = null)
    {
        if ($column) {
            return $this->tableInfo[$column];
        }
        return $this->tableInfo;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     * @return $this
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * @return Pdo
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param Pdo $db
     * @return $this
     */
    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

}