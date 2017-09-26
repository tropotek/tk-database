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
     * The default table name to use
     * Can be changed in the config if needed Data::$DB_TABLE = '';
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
     * @var string
     */
    protected $table = null;
    
    /**
     * @var int
     */
    protected $fid = 0;
    
    /**
     * @var string
     */
    protected $fkey = 'system';


    /**
     * Data constructor.
     * 
     * @param int $fid
     * @param string $fkey
     * @param string $table
     */
    public function __construct($fkey = 'system', $fid = 0, $table = '')
    {
        parent::__construct();
        if (!$table) $table = self::$DB_TABLE;
        $this->table = $table;
        $this->fkey = $fkey;
        $this->fid = $fid;

    }


    public function __sleep()
    {
        return array('table', 'fid', 'fkey');
    }

    public function __wakeup()
    {
        // TODO: hacky
        $this->db = \Tk\Config::getInstance()->getDb();
    }

    /**
     * Creates an instance of the Data object and loads that data from the DB
     * By Default this method uses the Tk\Config::getDb() to get the database.
     *
     * @param string $fkey
     * @param int $fid
     * @param string $table
     * @param Pdo|null $db
     * @return static
     */
    public static function create($fkey = 'system', $fid = 0, $table = '', $db = null)
    {
        $obj = new static($fkey, $fid, $table);
        if (!$db) $db = \Tk\Config::getInstance()->getDb();
        $obj->setDb($db);
        $obj->load();
        return $obj;
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
     * This sql should be DB generic (tested on: mysql, pgsql)
     *
     * @throws Exception
     * @return $this
     */
    private function install()
    {
        if (!$this->getDb() || $this->getDb()->tableExists($this->getTable())) return $this;
        $tbl = $this->getDb()->quoteParameter($this->getTable());
        // mysql
        $sql = '';
        if ($this->getDb()->getDriver() == 'mysql') {
            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `fid` INT(10) NOT NULL DEFAULT 0,
  `fkey` VARCHAR(64) NOT NULL DEFAULT '',
  `key` VARCHAR(128) NOT NULL DEFAULT '',
  `value` TEXT,
  UNIQUE `data_foreign_fields` (`fid`, `fkey`, `key`),
  KEY `fid` (`fid`)
) ENGINE=InnoDB;
SQL;
        } else if ($this->getDb()->getDriver() == 'pgsql') {
            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  id SERIAL PRIMARY KEY,
  fid INTEGER NOT NULL DEFAULT 0,
  fkey VARCHAR(64) NOT NULL DEFAULT '',
  "key" VARCHAR(128),
  "value" TEXT,
  UNIQUE (fid, fkey, "key"),
  KEY fid (fid)
);
SQL;
        } else if ($this->getDb()->getDriver() == 'sqlite') {
            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  id SERIAL PRIMARY KEY,
  fid INTEGER NOT NULL DEFAULT 0,
  fkey VARCHAR(64) NOT NULL DEFAULT '',
  "key" VARCHAR(128),
  "value" TEXT,
  UNIQUE (fid, fkey, "key"),
  KEY fid (fid)
);
SQL;
        }

        if ($sql) {
            $this->getDb()->exec($sql);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getFid()
    {
        return $this->fid;
    }

    /**
     * @param int $fid
     * @return $this
     */
    public function setFid($fid)
    {
        $this->fid = $fid;
        return $this;
    }

    /**
     * @return string
     */
    public function getFkey()
    {
        return $this->fkey;
    }

    /**
     * @param string $fkey
     * @return $this
     */
    public function setFkey($fkey)
    {
        $this->fkey = $fkey;
        return $this;
    }

    /**
     * Get the table name for queries
     * 
     * @return string
     */
    protected function getTable()
    {
        return $this->table;
    }

    /**
     * Load this object with available data from the DB
     * 
     * @return $this
     */
    public function load()
    {
        $sql = sprintf('SELECT * FROM %s WHERE fid = %d AND fkey = %s ', $this->db->quoteParameter($this->getTable()),
            (int)$this->fid, $this->db->quote($this->fkey));
        Pdo::$logLastQuery = false;
        $stmt = $this->db->query($sql);
        $stmt->setFetchMode(\PDO::FETCH_OBJ);
        foreach ($stmt as $row) {
            $this->set($row->key, $this->prepareGetValue($row->value));
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
        //
        //  DELETE FROM `company_data` WHERE TRIM(TRIM(BOTH '\n' FROM(TRIM(BOTH '\r' FROM value)))) = '';
        //  -- 9539 => 6805   -- Again removing null values gives us 1/3 reduction in row data,
        //
        // TODO: look into removing null or '' value rows as these can take up needed space...
//        if ($value === '') {            // TODO: Test if this is what we want, it would save data
//           return $this->dbDelete($key);
//        }
        $value = $this->prepareSetValue($value);

        if ($this->dbHas($key)) {
            $sql = sprintf('UPDATE %s SET value = %s WHERE %s = %s AND fid = %d AND fkey = %s ',
                $this->db->quoteParameter($this->getTable()), $this->db->quote($value), $this->db->quoteParameter('key'), $this->db->quote($key),
                (int)$this->fid, $this->db->quote($this->fkey) );
        } else {
            $sql = sprintf('INSERT INTO %s (fid, fkey, %s, value) VALUES (%d, %s, %s, %s) ',
                $this->db->quoteParameter($this->getTable()), $this->db->quoteParameter('key'), (int)$this->fid, $this->db->quote($this->fkey),
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
        $sql = sprintf('SELECT * FROM %s WHERE %s = %s AND fid = %d AND fkey = %s ', $this->db->quoteParameter($this->getTable()),   $this->db->quoteParameter('key'),
            $this->db->quote($key), (int)$this->fid, $this->db->quote($this->fkey));
        Pdo::$logLastQuery = false;
        $row = $this->db->query($sql)->fetchObject();
        Pdo::$logLastQuery = true;
        if ($row) {
            return $this->prepareGetValue($row->value);
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
        $sql = sprintf('SELECT * FROM %s WHERE %s = %s AND fid = %d AND fkey = %s ', $this->db->quoteParameter($this->getTable()), $this->db->quoteParameter('key'),
            $this->db->quote($key), (int)$this->fid, $this->db->quote($this->fkey));
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
        $sql = sprintf('DELETE FROM %s WHERE %s = %s AND fid = %d AND fkey = %s ', $this->db->quoteParameter($this->getTable()),  $this->db->quoteParameter('key'),
            $this->db->quote($key), (int)$this->fid, $this->db->quote($this->fkey));
        Pdo::$logLastQuery = false;
        $this->db->exec($sql);
        Pdo::$logLastQuery = true;
        return $this;
    }




    protected function prepareGetValue($value)
    {
        if (preg_match('/^(___JSON:)/', $value)) {
            $value = json_decode(substr($value, 8));
        }
        return $value;
    }

    protected function prepareSetValue($value)
    {
        if (is_array($value) || is_object($value)) {
            $value = '___JSON:' . json_encode($value);
        }
        return $value;
    }


    
}