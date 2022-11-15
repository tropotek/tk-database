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
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
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
        $this->setFetchMode(\PDO::FETCH_OBJ);
    }

    /**
     * Executes a prepared statement
     *
     * @see http://us3.php.net/manual/en/pdostatement.execute.php
     * @param array $args null
     * @return bool $boolean (Hum, shouldn't it return this so we can query the results???)
     * @throws Exception
     */
    #[\ReturnTypeWillChange]
    public function execute($args = null)
    {
        $start  = microtime(true);
        if (!is_array($args) && count(func_get_args())) {
            $args = func_get_args();
        }
        $this->bindParams = $args;
        $this->pdo->setLastQuery($this->queryString);

        try {
            $result = parent::execute($args);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), null, $this->queryString, $args);
        }
        $this->pdo->addLog(
            array(
                'query'  => $this->queryString,
                'time'   => microtime(true) - $start,
                'values' => $args,
            )
        );
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

    /**
     * @return Pdo
     */
    public function getPdo()
    {
        return $this->pdo;
    }


}