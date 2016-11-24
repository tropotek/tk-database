<?php
namespace Tk\Db;

/**
 * Class Data
 * 
 * A database object to manage misc data values.
 *
 * Use this for application persistent storage of data
 * 
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Data extends \Tk\Collection
{
    /**
     * Table name
     * Can be changed in the config if needed Data::$DB_DATA = '';
     *
     * @var string
     */
    public static $DB_TABLE = 'data';

    /**
     * The delete identifier for internal delete operation.
     * Can be changed in the config if needed Data::$DB_DEL = '';
     *
     * @var string
     */
    public static $DB_DEL = 'd___';

    /**
     * @var Pdo
     */
    protected $db = null;
    
    /**
     * @var int
     */
    protected $foreignId = 0;
    
    /**
     * @var string
     */
    protected $foreignKey = 'system';


    /**
     * Data constructor.
     * 
     * @param int $foreignId
     * @param string $foreignKey
     */
    public function __construct($foreignId = 0, $foreignKey = 'system')
    {
        parent::__construct();
        $this->foreignId = $foreignId;
        $this->foreignKey = $foreignKey;

    }

    /**
     * Creates an instance of the Data object and loads that data from the DB
     * By Default this method uses the Tk\Config::getDb() to get the database.
     *
     * @param int $foreignId
     * @param string $foreignKey
     * @return static
     */
    public static function create($foreignId = 0, $foreignKey = 'system')
    {
        $obj = new static($foreignId, $foreignKey);
        $obj->setDb(\Tk\Config::getInstance()->getDb());
        $obj->load();
        return $obj;
    }

    /**
     * This sql should be DB generic (tested on: mysql, pgsql)
     *
     * @throws Exception
     * @return $this
     */
    private function install()
    {
        if ($this->getDb()->tableExists($this->getTable())) return;
        $tbl = $this->getDb()->quoteParameter($this->getTable());
        // mysql
        $sql = '';
        if ($this->getDb()->getDriver() == 'mysql') {
            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `foreign_id` INT(10) NOT NULL DEFAULT 0,
  `foreign_key` VARCHAR(128) NOT NULL DEFAULT '',
  `key` VARCHAR(255) NOT NULL DEFAULT '',
  `value` TEXT,
  UNIQUE KEY `data_foreign_fields` (`foreign_id`, `foreign_key`, `key`)
) ENGINE=InnoDB;
SQL;
        } else if ($this->getDb()->getDriver() == 'pgsql') {
            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  id SERIAL PRIMARY KEY,
  foreign_id INTEGER NOT NULL DEFAULT 0,
  foreign_key VARCHAR(128) NOT NULL DEFAULT '',
  "key" VARCHAR(255),
  "value" TEXT,
  CONSTRAINT data_foreign_fields UNIQUE (foreign_id, foreign_key, "key")
);
SQL;
        } else if ($this->getDb()->getDriver() == 'sqlite') {
            // todo
        }

        if ($sql) {
            $this->getDb()->exec($sql);
        }
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
        $this->install();
        return $this;
    }

    /**
     * @return int
     */
    public function getForeignId()
    {
        return $this->foreignId;
    }

    /**
     * @param int $foreignId
     * @return $this
     */
    public function setForeignId($foreignId)
    {
        $this->foreignId = $foreignId;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param string $foreignKey
     * @return $this
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;
        return $this;
    }

    /**
     * Get the table name for queries
     * 
     * @return string
     */
    protected function getTable()
    {
        return self::$DB_TABLE;
    }

    /**
     * Load this object with available data from the DB
     * 
     * @return $this
     */
    public function load()
    {
        $sql = sprintf('SELECT * FROM %s WHERE foreign_id = %d AND foreign_key = %s ', $this->db->quoteParameter($this->getTable()),
            (int)$this->foreignId, $this->db->quote($this->foreignKey));
        Pdo::$logLastQuery = false;
        $stmt = $this->db->query($sql);
        $stmt->setFetchMode(\PDO::FETCH_OBJ);
        foreach ($stmt as $row) {
            $this->set($row->key, $row->value);
        }
        Pdo::$logLastQuery = true;
        return $this;
    }

    /**
     * Save modified Data to the DB
     * 
     * @return $this
     */
    public function save()
    {
        Pdo::$logLastQuery = false;
        foreach($this as $k => $v) {
            if (preg_match('/^'.self::$DB_DEL.'(.+)/', $k, $reg)) {   // Marked for delete
                $this->dbDelete($reg[1]);
            } else {
                $this->dbSet($k, $v);
            }
        }
        Pdo::$logLastQuery = true;
        return $this;
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     * @return $this
     */
    public function remove($key)
    {
        $this->data[self::$DB_DEL.$key] = $this->data[$key];
        unset($this->data[$key]);
        return $this;
    }

    /**
     * Remove all items from collection
     *
     * @return $this
     */
    public function clear()
    {
        foreach ($this->data as $k => $v) {
            $this->remove($k);
        }
        return $this;
    }

    /**
     * Set a single data value in the Database 
     * 
     * @param $key
     * @param $value
     * @return Data
     */
    protected function dbSet($key, $value)
    {
        if (is_array($value) || is_object($value)) {
            return $this;
        }
        if ($this->dbHas($key)) {
            $sql = sprintf('UPDATE %s SET value = %s WHERE %s = %s AND foreign_id = %d AND foreign_key = %s ',
                $this->db->quoteParameter($this->getTable()), $this->db->quote($value), $this->db->quoteParameter('key'), $this->db->quote($key),
                (int)$this->foreignId, $this->db->quote($this->foreignKey) );
        } else {
            $sql = sprintf('INSERT INTO %s (foreign_id, foreign_key, %s, value) VALUES (%d, %s, %s, %s) ',
                $this->db->quoteParameter($this->getTable()), $this->db->quoteParameter('key'), (int)$this->foreignId, $this->db->quote($this->foreignKey),
                $this->db->quote($key), $this->db->quote($value));
        }
        Pdo::$logLastQuery = false;
        $this->db->exec($sql);
        Pdo::$logLastQuery = true;
        return $this;
    }

    /**
     * Get a value from the database
     * 
     * @param $key
     * @return string
     */
    protected function dbGet($key)
    {
        $sql = sprintf('SELECT * FROM %s WHERE %s = %s AND foreign_id = %d AND foreign_key = %s ', $this->db->quoteParameter($this->getTable()),   $this->db->quoteParameter('key'),
            $this->db->quote($key), (int)$this->foreignId, $this->db->quote($this->foreignKey));
        Pdo::$logLastQuery = false;
        $row = $this->db->query($sql)->fetchObject();
        Pdo::$logLastQuery = true;
        if ($row) {
            return $row->value;
        }
        return '';
    }

    /**
     * Check if a value exists in the DB
     * 
     * @param $key
     * @return bool
     */
    protected function dbHas($key)
    {
        $sql = sprintf('SELECT * FROM %s WHERE %s = %s AND foreign_id = %d AND foreign_key = %s ', $this->db->quoteParameter($this->getTable()), $this->db->quoteParameter('key'),
            $this->db->quote($key), (int)$this->foreignId, $this->db->quote($this->foreignKey));
        Pdo::$logLastQuery = false;
        $res = $this->db->query($sql);
        Pdo::$logLastQuery = true;
        if ($res && $res->rowCount()) return true;
        return false;
    }

    /**
     * Remove a value from the DB
     * 
     * @param $key
     * @return $this
     */
    protected function dbDelete($key)
    {
        $sql = sprintf('DELETE FROM %s WHERE %s = %s AND foreign_id = %d AND foreign_key = %s ', $this->db->quoteParameter($this->getTable()),  $this->db->quoteParameter('key'),
            $this->db->quote($key), (int)$this->foreignId, $this->db->quote($this->foreignKey));
        Pdo::$logLastQuery = false;
        $this->db->exec($sql);
        Pdo::$logLastQuery = true;
        return $this;
    }
    
}