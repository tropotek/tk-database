<?php
namespace Tk\Util;

/**
 * Class IpThrottle
 * 
 * This object can track and give you the ability to throttle
 *  the number of actions/hits/submissions that a single IP can
 *  do within a given time-frame
 *
 *  <code>
 *     $ipt = IpThrottle::create($this->getDb());
 *
 *     $max = 5;
 *     $minutes = 10;
 *     $from = \Tk\Date::create()->sub(new \DateInterval('PT'.$minutes.'M'));
 *     $list = $ipt->getIpSubmissions($this->getRequest()->getClientIp(), $from, null, $this->getRequest()->getTkUri()->getRelativePath());
 *     vd(count($list));
 *     if (count($list) >= $max) {
 *        // TODO: exit/redirect or whatever suits the situation
 *     }
 *
 *      ...
 *
 *     // Log a page hit
 *     $ipt->logIp(IpThrottle::getClientIp(), IpThrottle::getUri());
 *
 *  </code>
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class IpThrottle
{

    /**
     * EG: 2009-12-31 24:59:59
     */
    const FORMAT_ISO_DATETIME = 'Y-m-d H:i:s';

    /**
     * The default table name to use
     * Can be changed in the config if needed Data::$DB_TABLE = '';
     *
     * @var string
     */
    public static $DB_TABLE = '_ipthrottle';

    /**
     * @var \PDO
     */
    protected $db = null;

    /**
     * @param \PDO $db
     */
    public function __construct($db)
    {
        $this->setDb($db);
    }

    /**
     * @param \PDO|null $db
     * @return static
     */
    public static function create($db = null)
    {
        if (!$db) $db = \Tk\Config::getDb();
        $obj = new static($db);
        return $obj;
    }

    /**
     *
     * @param string $ip
     * @param \DateTime $dateFrom
     * @param null|\DateTime $dateTo (optional) Defaults to NOW if not used
     * @param null|string $key
     * @return array|bool
     * @throws \Exception
     */
    public function getIpSubmissions($ip, $dateFrom, $dateTo = null, $key = '')
    {
        if (!$this->hasTable($this->getTable())) return array();
        if (!$dateTo) $dateTo = new \DateTime();
        if ($dateFrom >= $dateTo) return array();
        if ($key) $key = sprintf("AND `key` LIKE '%s'", $key);
        $sql = sprintf("SELECT * FROM %s WHERE ip LIKE '%s' %s AND timestamp > '%s' AND timestamp <= '%s' ",
            $this->getTable(),
            $ip,
            $key,
            $dateFrom->format(self::FORMAT_ISO_DATETIME),
            $dateTo->format(self::FORMAT_ISO_DATETIME)
        );
        $res = $this->getDb()->query($sql);
        return $res->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * @param $key
     * @param \DateTime $dateFrom
     * @param null|\DateTime $dateTo (optional) Defaults to NOW if not used
     * @param string $ip
     * @return array|bool
     * @throws \Exception
     */
    public function getKeySubmissions($key, $dateFrom, $dateTo = null, $ip = '')
    {
        if (!$this->hasTable($this->getTable())) return array();
        if (!$dateTo) $dateTo = new \DateTime();
        if ($dateFrom >= $dateTo) return array();
        if ($ip) $ip = sprintf("AND `ip` LIKE %s", $ip);

        //$sql = sprintf("SELECT * FROM %s WHERE ip LIKE '%s' AND timestamp > (DATE_ADD(NOW(), INTERVAL -{$timeToRestrict} HOUR))"
        $sql = sprintf("SELECT * FROM %s WHERE key LIKE '%s' %s AND timestamp > '%s' AND timestamp <= '%s' ",
            $this->getTable(),
            $key,
            $ip,
            $dateFrom->format(self::FORMAT_ISO_DATETIME),
            $dateTo->format(self::FORMAT_ISO_DATETIME)
        );
        $res = $this->getDb()->query($sql);
        return $res->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Log an IP hit to the DB
     *
     * @param string $ip
     * @param string $key
     * @return false|\PDOStatement
     * @throws \Exception
     */
    public function logIp($ip, $key = '')
    {
        if (
            !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
            !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
        ) {
            return false;
        }

        $this->install();
        $now = new \DateTime();
        $sql = sprintf("INSERT INTO %s (ip, `key`, timestamp) VALUES ('%s', '%s', '%s')",
            $this->getTable(), $ip, $key, $now->format(self::FORMAT_ISO_DATETIME)
        );
        return $this->getDb()->query($sql);
    }

    /**
     * This sql should be DB generic (tested on: mysql)
     *
     * @return $this
     * @throws \Exception
     */
    protected function install()
    {
        if (!$this->getDb() || $this->hasTable($this->getTable())) return $this;
        $tbl = $this->getTable();
        if ($this->getDb()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql') {
            $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS $tbl (
      `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `ip` VARCHAR(32) NOT NULL DEFAULT '',
      `key` VARCHAR(255) NOT NULL DEFAULT '',
      `timestamp` DATETIME NOT NULL,
      KEY `ip` (`ip`),
      KEY `key` (`key`),
      KEY `ip_key` (`ip`, `key`)
    ) ENGINE=InnoDB;
SQL;
            $this->getDb()->exec($sql);
        } else {
            throw new \Exception(get_class($this) . ' does not support the '.$this->getDb()->getAttribute(\PDO::ATTR_DRIVER_NAME).' Database driver');
        }
        return $this;
    }


    /**
     * @return \PDO
     */
    protected function getDb()
    {
        return $this->db;
    }

    /**
     * @param \PDO $db
     * @return $this
     */
    protected function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @return string
     */
    protected function getTable()
    {
        return self::$DB_TABLE;
    }

    /**
     * Check if a table exists in the current database
     *
     * @param string $table
     * @return bool
     * @version 2.0.15
     */
    protected function hasTable($table)
    {
        $list = $this->getTableList();
        return in_array($table, $list);
    }

    /**
     * @return array
     */
    protected function getTableList()
    {
        $list = array();
        if ($this->getDb()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql') {
            $sql = 'SHOW TABLES';
            $result = $this->getDb()->query($sql);
            $list = $result->fetchAll(\PDO::FETCH_COLUMN, 0);
        }
        return $list;
    }


    /**
     * @return string
     */
    public static function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @return mixed
     */
    public static function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}