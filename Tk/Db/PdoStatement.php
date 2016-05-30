<?php
namespace Tk\Db;

/**
 * Class PdoStatement
 *
 * NOTE: When using the statement in a foreach loop, any overriden
 * method calls to fetch, fetchObject, etc will not be called
 * in this object, it has something to do with the way the PDOStatement
 * object uses it Traversable methods internally
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @author Patrick S Scott<lazeras@kaoses.com>
 * @link http://www.kaoses.com
 * @license Copyright 2007 Michael Mifsud
 */
class PdoStatement extends \PDOStatement
{
    /**
     * @var Pdo
     */
    protected $pdo;


    protected $bindParams = null;


    /**
     * Represents a prepared statement and, after the statement is executed, an associated result set
     *
     * @see http://www.php.net/manual/en/class.pdostatement.php
     * @param Pdo $pdo
     */
    protected function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Executes a prepared statement
     *
     *  @see http://us3.php.net/manual/en/pdostatement.execute.php
     * @param array $args null
     *
     * @return boolean $boolean
     */
    public function execute($args = null)
    {
        $start  = microtime(true);
        if (!is_array($args)) {
            $args = func_get_args();
        }
        $this->bindParams = $args;
        $result = parent::execute($args);
        $this->pdo->addLog(
            array(
                'query'  => $this->queryString,
                'time'   => microtime(true) - $start,
                'values' => $args,
            )
        );
//        $err = $this->errorInfo();
//        vd($err[2]);
        return $result;
    }

    /**
     * Get the params bound upon the last call to execute()
     *
     * @return null|array
     */
    public function getBindParams()
    {
        return $this->bindParams;
    }


}