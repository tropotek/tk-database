<?php
namespace Tk\Util;

use Tk\Db\Pdo;
use Tk\Db\Exception;

/**
 * Class SqlDump
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 * @see https://raw.githubusercontent.com/kakhavk/database-dump-utility/master/SqlDump.php
 */
class SqlBackup
{

    /**
     * @var Pdo
     */
    private $db = null;


    /**
     * construct
     *
     * @param Pdo $db
     */
    function __construct(Pdo $db)
    {
        $this->db = $db;
    }

    /**
     * Restore an sql file
     *
     * @param string $sqlFile
     * @param array $options  TODO:
     * @throws Exception
     * @throws \Tk\Exception
     */
    public function restore($sqlFile, $options = array())
    {
        //  $this->db->dropAllTables(true);     // TODO: leave this up to the user
        //  $this->db->exec(file_get_contents($sqlFile));           // Remove, no good for large backups....
        if (!is_readable($sqlFile)) {
            return;
        }

        $host = escapeshellarg($this->db->getOption('host'));
        $name = escapeshellarg($this->db->getOption('name'));
        $user = escapeshellarg($this->db->getOption('user'));
        $pass = escapeshellarg($this->db->getOption('pass'));

        $command = '';
        // TODO: create a windows valid commands ????
        if ('mysql' == $this->db->getDriver()) {
            $command = sprintf('mysql %s -h %s -u %s -p%s < %s', $name, $host, $user, $pass, escapeshellarg($sqlFile));
        } else if ('pgsql' == $this->db->getDriver()) {
            //$command = sprintf('export PGPASSWORD=%s && pg_dump -h %s -U %s %s', $pass, $host, $user, $name);
            //$command = sprintf('export PGPASSWORD=%s && pg_dump --inserts -O -h %s -U %s %s > %s', $pass, $host, $user, $name, $filepath);
            throw new \Tk\Exception('Not implemented yet!!!! Finders Keepers, Guess you have some work to do...');
        }
        exec($command, $out, $ret);
        if ($ret != 0) {
            throw new \Tk\Db\Exception(implode("\n", $out));
        }


    }

    /**
     * Save the sql to a path.
     *
     * The file name will be in the format of: {DbName}_2016-01-01-12-00-00.sql
     * if the path does not already contain a .sql file extension
     *
     * @param string $path
     * @param array $options  TODO:
     * @return bool|string Return the sql filename on success false on fail
     * @throws Exception
     */
    public function save($path = '', $options = array())
    {
        $sqlFile = $path;
        if (!preg_match('/\.sql$/', $sqlFile)) {
            $path = rtrim($path, '/');
            if (!is_dir($path)) {
                mkdir($path, \Tk\Config::getInstance()->getDirMask(), true);
            }
            if (!is_writable($path)) {
                throw new \Tk\Db\Exception('Cannot access path: ' . $path);
            }
            $file = $this->db->getDatabaseName() . "_" . $this->db->getDriver() . "_" . date("Y-m-d-H-i-s").".sql";
            $sqlFile = $path.'/'.$file;
        }

        $host = escapeshellarg($this->db->getOption('host'));
        $name = escapeshellarg($this->db->getOption('name'));
        $user = escapeshellarg($this->db->getOption('user'));
        $pass = escapeshellarg($this->db->getOption('pass'));

        $command = '';
        // TODO: create a windows valid commands ????
        if ('mysql' == $this->db->getDriver()) {
            $command = sprintf('mysqldump --opt -h %s -u %s -p%s %s > %s', $host, $user, $pass, $name, escapeshellarg($sqlFile));
        } else if ('pgsql' == $this->db->getDriver()) {
            $command = sprintf('export PGPASSWORD=%s && pg_dump --inserts -O -h %s -U %s %s > %s', $pass, $host, $user, $name, escapeshellarg($sqlFile));
        }

        // Error check
        if(!$command) {
            throw new \Tk\Db\Exception('Database driver not supported:  '.$this->db->getDriver());
        }

        exec($command, $out, $ret);

        if ($ret != 0) {
            throw new \Tk\Db\Exception(implode("\n", $out));
        }
        if(filesize($sqlFile) <= 0) {
            throw new \Tk\Db\Exception('Size of file '.$sqlFile.' is ' . filesize($sqlFile));
        }
        return $sqlFile;
    }

    /**
     * @param array $options
     * @return string   Return the sql dump generated
     * @throws Exception
     * @todo: See how this goes as it could possably have a memory issues with large databases, use SqlBackup:save() in those cases
     */
    public function dump($options = array())
    {
        $host = escapeshellarg($this->db->getOption('host'));
        $name = escapeshellarg($this->db->getOption('name'));
        $user = escapeshellarg($this->db->getOption('user'));
        $pass = escapeshellarg($this->db->getOption('pass'));

        $command = '';
        // TODO: create a windows valid commands ????
        if ('mysql' == $this->db->getDriver()) {
            $command = sprintf('mysqldump --opt -h %s -u %s -p%s %s', $host, $user, $pass, $name);
        } else if ('pgsql' == $this->db->getDriver()) {
            $command = sprintf('export PGPASSWORD=%s && pg_dump --inserts -O -h %s -U %s %s', $pass, $host, $user, $name);
        }
        if(!$command) {
            throw new \Tk\Db\Exception('Database driver not supported:  '.$this->db->getDriver());
        }

        exec($command, $out, $ret);

        if ($ret != 0) {
            throw new \Tk\Db\Exception(implode("\n", $out));
        }
        return implode("\n", $out);
    }

}