<?php
namespace Tk\Util;


/**
 * Class Migrate
 *
 * A script that iterated the project files and executes *.sql files
 * once the files are executed they are then logs and not executed again.
 * Great for upgrading and installing a systems DB
 *
 * Files should reside in a folder named `.../sql/{type}/*`
 *
 * For a mysql file it could look like `.../sql/mysql/000001.sql`
 * for a postgress file `.../sql/pgsql/000001.sql`
 *
 * It is a good idea to start with a number to ensure that the files are
 * executed in the required order. Files found will be sorted alphabetically.
 *
 * <code>
 *   $migrate = new \Tk\Db\Migrate(Factory::getDb(), $this->config->getSitePath());
 *   $migrate->run()
 * </code>
 *
 * Migration files can be of type .sql or .php.
 * The php files are called with the include() command.
 * It will then be up to the developer to include a script to install the required sql.
 * 
 * @todo Should this be moved to the installers lib?
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class SqlMigrate
{

    static $DB_TABLE = 'migration';

    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $sitePath = '';

    /**
     * @var string
     */
    protected $tempPath = '/tmp';


    /**
     * SqlMigrate constructor.
     *
     * @param \Tk\Db\Pdo $db
     * @param string $tempPath
     * @throws \Tk\Db\Exception
     */
    public function __construct($db, $tempPath = '/tmp')
    {
        $this->sitePath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $this->tempPath = $tempPath;
        $this->setDb($db);
    }

    /**
     * Run the migration script and find all non executed sql files
     *
     * @param $path
     * @return array
     * @throws \Exception
     * @throws \Tk\Db\Exception
     * @todo: considder removeing the backup/restore from this method and maybe have a new mothod like safeMigrate()
     * @todo:   This would allow the programmer to migrate and take care of the backup themself
     */
    public function migrate($path)
    {
        $list = $this->getFileList($path);
        $backupFile = '';
        $mlist = array();
        $sqlFiles = array();
        $phpFiles = array();

        try { 
            // Find any migration files
            foreach ($list as $file) {
                if (preg_match('/\.php$/i', basename($file))) {   // Include .php files
                    $phpFiles[] = $file;
                } else {
                    $sqlFiles[] = $file;
                }
            }

            if (count($sqlFiles) || count($phpFiles)) {
                $dump = new SqlBackup($this->db);
                $backupFile = $dump->save($this->tempPath);     // Just in case
                foreach ($sqlFiles as $file) {
                    if ($this->migrateFile($file)) {
                        $mlist[] = $this->toRelative($file);
                    }
                }
                foreach ($phpFiles as $file) {
                    if ($this->migrateFile($file)) {
                        $mlist[] = $this->toRelative($file);
                    }
                }
            }

        } catch (\Exception $e) {
            if ($backupFile) {
                $dump->restore($backupFile);
                unlink($backupFile);
                $backupFile = '';
            }
            throw $e;
        }

        if ($backupFile) {
            unlink($backupFile);
            $backupFile = '';
        }
        return $mlist;
    }

    /**
     * Check to see if there are any new migration sql files pending execution
     *
     * @param $path
     * @return bool
     * @throws \Tk\Db\Exception
     */
    public function isPending($path)
    {
        $list = $this->getFileList($path);
        $pending = false;
        foreach ($list as $file) {
            if (!$this->hasPath($file)) {
                $pending = true;
                break;
            }
        }
        return $pending;
    }

    /**
     * Set the temp path for db backup file
     * Default '/tmp'
     *
     * @param string $path
     * @return $this
     */
    public function setTempPath($path)
    {
        $this->tempPath = $path;
        return $this;
    }

    /**
     * search the path for *.sql files, also search the $path.'/'.$driver folder
     * for *.sql files.
     *
     * @param string $path
     * @return array
     */
    public function getFileList($path)
    {
        $list = array();
        $list = array_merge($list, $this->search($path));
        $list = array_merge($list, $this->search($path.'/'.$this->db->getDriver()));
        sort($list);
        return $list;
    }

    /**
     * Execute a migration class or sql script...
     * the file is then added to the db and cannot be executed again.
     *
     * @param string $file
     * @return bool
     * @throws \Tk\Db\Exception
     */
    protected function migrateFile($file)
    {
        $file = $this->sitePath . $this->toRelative($file);
        if ($this->hasPath($file)) return false;
        if (!is_readable($file)) return false;

        if (substr(basename($file), 0, 1) == '_') return false;

        if (preg_match('/\.php$/i', basename($file))) {   // Include .php files
            if (is_file($file)) {
                include($file);
            } else {
                return false;
            }
        } else {    // is sql
            // replace any table prefix
            $sql = file_get_contents($file);
            $stm = $this->db->prepare($sql);
            $stm->execute();

            // Bugger of a way to get the error:
            // https://stackoverflow.com/questions/23247553/how-can-i-get-an-error-when-running-multiple-queries-with-pdo
            $i = 0;
            do { $i++; } while ($stm->nextRowset());
            $error = $stm->errorInfo();
            if ($error[0] != "00000") {
              throw new \Tk\Db\Exception("Query $i failed: " . $error[2], 0, null, $sql);
            }
        }
        $this->insertPath($file);
        return true;
    }

    /**
     * Search a path for sql files
     *
     * @param $path
     * @return array
     */
    public function search($path)
    {
        $list = array();
        if (!is_dir($path)) return $list;
        $iterator = new \DirectoryIterator($path);
        foreach(new \RegexIterator($iterator, '/\.(php|sql)$/') as $file) {
            if (preg_match('/^(_|\.)/', $file->getBasename())) continue;
            $list[] = $file->getPathname();
        }
        return $list;
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
     * @return \Tk\Db\Pdo
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param \Tk\Db\Pdo $db
     * @return $this
     * @throws \Tk\Db\Exception
     */
    public function setDb($db)
    {
        $this->db = $db;
        $this->install();
        return $this;
    }

    /**
     * install the migration table to track executed scripts
     *
     * @todo This must be tested against mysql, pgsql and sqlite....
     * So far query works with mysql and pgsql drvs sqlite still to test
     * @throws \Tk\Db\Exception
     */
    protected function install()
    {
        if($this->db->hasTable($this->getTable())) {
            return;
        }
        $tbl = $this->db->quoteParameter($this->getTable());
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  path VARCHAR(128) NOT NULL DEFAULT '',
  created TIMESTAMP,
  PRIMARY KEY (path)
);
SQL;
        $this->db->exec($sql);
    }

    /**
     * exists
     *
     * @param string $path
     * @return bool
     * @throws \Tk\Db\Exception
     */
    protected function hasPath($path)
    {
        $path = $this->db->escapeString($this->toRelative($path));
        $sql = sprintf('SELECT * FROM %s WHERE path = %s LIMIT 1', $this->db->quoteParameter($this->getTable()), $this->db->quote($path));
        $res = $this->db->query($sql);
        if ($res->rowCount()) {
            return true;
        }
        return false;
    }

    /**
     * insert
     *
     * @param string $path
     * @return \PDOStatement
     * @throws \Tk\Db\Exception
     */
    protected function insertPath($path)
    {
        $path = $this->db->escapeString($this->toRelative($path));
        $sql = sprintf('INSERT INTO %s (path, created) VALUES (%s, NOW())', $this->db->quoteParameter($this->getTable()), $this->db->quote($path));
        return $this->db->exec($sql);
    }

    /**
     * delete
     *
     * @param string $path
     * @return \PDOStatement
     * @throws \Tk\Db\Exception
     */
    protected function deletePath($path)
    {
        $path = $this->db->escapeString($this->toRelative($path));
        $sql = sprintf('DELETE FROM %s WHERE path = %s LIMIT 1', $this->db->quoteParameter($this->getTable()), $this->db->quote($path));
        return $this->db->exec($sql);
    }

    /**
     * Return the relative path
     *
     * @param $path
     * @return string
     */
    private function toRelative($path)
    {
        return rtrim(str_replace($this->sitePath, '', $path), '/');
    }
    
}