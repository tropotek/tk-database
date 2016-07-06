<?php
namespace Tk\Db;

/**
 * Class Migrate
 *
 * A script that iterated the project files and executes *.sql files
 * once the files are executed they are then logs and not executed again.
 * Great for upgrading and installing a systems DB
 *
 * Files should reside in a folder named `.../sql/{type}/*`
 *
 * For a mysql file it would look like `.../sql/mysql/000-install.sql`
 * for a postgress file `.../sql/pgsql/000-install.sql`
 *
 * It is a good idea to start with a number to ensure that the files are
 * executed in the required order. Files found will be sorted alphabetically.
 *
 * <code>
 *   $migrate = new \Tk\Db\Migrate(Factory::getDb(), $this->config->getSitePath());
 *   $migrate->run()
 * </code>
 *
 *
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Migrate
{

    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $sitePath = '';


    /**
     * Migrate constructor.
     *
     * @param \Tk\Db\Pdo $db
     * @param string $sitePath
     * @param string $table
     */
    public function __construct($db, $sitePath, $table = 'migration')
    {
        $this->db = $db;
        $this->table = $table;
        $this->sitePath = $sitePath;

        if(!$this->db->tableExists($this->table)) {
            $this->installMigrationTable();
        }

    }

    /**
     * Run the migration script and find all non executed .sql files
     *
     */
    public function run()
    {
        $list = $this->getFileList($this->sitePath);


        $success = false;
        $this->error = null;
        $bakPath = $this->getConfig()->getTmpPath();

        //$this->db->createBackup($bakPath);
        try {
            foreach ($list as $o) {
                if ($this->pathExists($o->path)) continue;
                $this->executeFile($o->path, $o->class);
                $this->getConfig()->getLog()->write('  M: ' . $o->path . ' - ' . $o->class);
            }
            $success = true;
            $this->getConfig()->getLog()->write('--------- MIGRATION SUCCESSFUL -----------');
        } catch (\Exception $e) {
            $this->error = $e;
            $success = false;
            //$this->db->restoreBackup($bakPath);
            $this->getConfig()->getLog()->write('--------- MIGRATION FAILED -----------');
            $this->getConfig()->getLog()->write($e->getMessage());
        }
        if (is_file($bakPath))
            unlink($bakPath);
        return $success;




vd($fileList, $this->sitePath);

    }



    /**
     * Recursivly get all SQL/PHP file in the supplied folder
     * Use an underscore as the files first character to hide the file.
     * Also dot files are ignored
     *
     *  - Direct children of the /sql folder are considered driver generic and executed
     *  - Driver sub-folders are executed accordingly (IE: /sql/mysql/* will be run for mysql DB's only)
     *
     * @param string $path
     * @return array
     */
    protected function getFileList($path)
    {
        $directory = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::CHILD_FIRST);
        $list = array();
        foreach(new \RegexIterator($iterator, '/\/sql$/') as $file) {
            $list = array_merge($list, $this->search($file->getPathname()));
            $list = array_merge($list, $this->search($file->getPathname().'/'.$this->db->getDriver()));
        }
        usort($list, function ($a, $b) {
            //vd($a->path, $b->path);
            // do we need to so this str comparison?
//            $a1 = (int)substr(basename($a->path), 0, strpos(basename($a->path), '-'));
//            $b1 = (int)substr(basename($b->path), 0, strpos(basename($b->path), '-'));

            $a1 = $a->path;
            $b1 = $b->path;

            if ($a1 > $b1) {
                return 1;
            }
            if ($a1 < $b1) {
                return -1;
            }
            return 0;
        });
        return $list;
    }

    /**
     * Search a path for sql files
     *
     * @param $path
     * @return array
     */
    protected function search($path)
    {
        $iterator = new \DirectoryIterator($path);
        $list = array();
        foreach(new \RegexIterator($iterator, '/\.(php|sql)$/') as $f2) {
            if (preg_match('/^(_|\.)/', $f2->getBasename())) continue;
            $o = new \stdClass();
            $o->path = str_replace($this->sitePath, '', $f2->getPathname());
            $o->date = \Tk\Date::create()->format(\Tk\Date::ISO_DATE);
            $list[] = $o;
        }
        return $list;
    }




    // Migration DB access methods


    /**
     * install the migration table to track executed scripts
     *
     */
    protected function installMigrationTable()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$this->table} (
  path varchar(255) NOT NULL DEFAULT '',
  date TIMESTAMP,
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
     */
    protected function hasPath($path)
    {
        $path = $this->db->escapeString($path);
        $sql = sprintf('SELECT * FROM %s WHERE path = %s LIMIT 1', $this->table, $this->db->quote($path));
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
     * @return PDOStatement
     */
    protected function insertPath($path, $class)
    {
        $path = $this->db->escapeString($path);
        $class = $this->db->escapeString($class);
        $sql = sprintf('INSERT INTO %s (path, class) VALUES (%s, %s)', $this->table, $this->db->quote($path), $this->db->quote($class));
        return $this->db->exec($sql);
    }

    /**
     * delete
     *
     * @param string $path
     * @return PDOStatement
     */
    protected function deletePath($path)
    {
        $path = $this->db->escapeString($path);
        $sql = sprintf('DELETE FROM %s WHERE path = %s LIMIT 1', $this->table, $this->db->quote($path));
        return $this->db->exec($sql);
    }

}