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
     * Restore a sql
     *
     * @param $sqlFile
     */
    public function restore($sqlFile)
    {
        $this->db->dropAllTables(true);
        $this->db->multiQuery(file_get_contents($sqlFile));
    }

    /**
     * Save the sql to a path.
     *
     * The file name will be in the format of: {DbName}_2016-01-01-12-00-00.sql
     *
     *
     * @param $path
     * @return bool|string Return the sql filename  on success false on fail
     * @throws Exception
     */
    public function save($path)
    {
        $path = rtrim($path, '/');
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (!is_writable($path)) {
            throw new \Tk\Db\Exception('Cannot access path: ' . $path);
        }

        $file = $this->db->getDatabaseName() . "_" . $this->db->getDriver() . "_" . date("Y-m-d-H-i-s").".sql";
        $filepath = $path.'/'.$file;

        $host = escapeshellarg($this->db->getOption('db.host'));
        $name = escapeshellarg($this->db->getOption('db.name'));
        $user = escapeshellarg($this->db->getOption('db.user'));
        $pass = escapeshellarg($this->db->getOption('db.pass'));

        $command = '';
        // TODO: create a windows valid commands ????
        if ('mysql' == $this->db->getDriver()) {
            //$command = sprintf('mysqldump --opt -h %s -u %s -p%s %s', $host, $user, $pass, $name);
            $command = sprintf('mysqldump --opt -h %s -u %s -p%s %s > %s', $host, $user, $pass, $name, $filepath);
        } else if ('pgsql' == $this->db->getDriver()) {
            //$command = sprintf('export PGPASSWORD=%s && pg_dump -h %s -U %s %s', $pass, $host, $user, $name);
            $command = sprintf('export PGPASSWORD=%s && pg_dump --inserts -O -h %s -U %s %s > %s', $pass, $host, $user, $name, $filepath);
        }

        // Error check
        if(!$command) {
            throw new \Tk\Db\Exception('Database driver not supported:  '.$this->db->getDriver());
        }

        exec($command, $out, $ret);

        if ($ret != 0) {
            throw new \Tk\Db\Exception(file_get_contents($filepath));
        }
        if(filesize($filepath) <= 0) {
            throw new \Tk\Db\Exception('Size of file '.$filepath.' is ' . filesize($filepath));
        }

        return $filepath;
    }

}