<?php
namespace Tk\Db\Map;

use Tk\Db\Exception;
use \Tk\Db\Pdo;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class Model implements \Tk\Db\ModelInterface
{

    /**
     * @var string
     */
    public static $MAPPER_APPEND = 'Map';


    /**
     * Get this object's DB mapper
     *
     * The Mapper class will be taken from this class's name if not supplied
     * By default the Database is attempted to be set from the Tk\Config object if it exists
     * Also the Default table name is generated from this object: EG: /App/Db/WebUser = 'webUser'
     * This would in-turn look for the mapper class /App/Db/WebUserMap
     * Change the self::$APPEND parameter to change the class append name
     * The method setDb() must be called after calling getMapper() if you do not wish to use the DB from the config
     *
     * @param string $mapperClass 
     * @param Pdo $db 
     * @return Mapper
     */
    static function createMapper($mapperClass = '', $db = null)
    {
        if (!$mapperClass) {
            $mapperClass = get_called_class() . self::$MAPPER_APPEND;
//            if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
//                $mapperClass = static::class . self::$MAPPER_APPEND;
//            }
        }
        //return new $mapperClass($db);
        return $mapperClass::create($db);
    }

    /**
     * Get the model primary DB key, usually ID
     *
     * @return mixed
     */
    public function getId()
    {
        $pk = self::createMapper()->getPrimaryKey();
        if (property_exists($this, $pk)) {
            return $this->$pk;
        }
        return null;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    private function setId($id)
    {
        $pk = self::createMapper()->getPrimaryKey();
        if (property_exists($this, $pk)) {
            $this->$pk = $id;
        }
        return $this;
    }

    /**
     * Insert the object into storage.
     * By default this is a database
     *
     * @return int The insert ID
     */
    public function insert()
    {
        $id = self::createMapper()->insert($this);
        $this->setId($id);  // Has to be here cause of private property
        return $id;
    }

    /**
     * Update the object in storage
     *
     * @return int
     */
    public function update()
    {
        $r = self::createMapper()->update($this);
        return $r;
    }

    /**
     * A Utility method that checks the id and does and insert
     * or an update  based on the objects current state
     *
     * @throws \Exception
     */
    public function save()
    {
        self::createMapper()->save($this);
    }

    /**
     * Delete the object from the DB
     *
     * @return int
     */
    public function delete()
    {
        return self::createMapper()->delete($this);
    }

    /**
     * Returns the object id if it is greater than 0 or the nextInsertId if is 0
     *
     * @return int
     */
    public function getVolatileId()
    {
        if (!$this->getId()) {
            try {
                return self::createMapper()->getDb()->getNextInsertId(self::createMapper()->getTable());
            } catch (Exception $e) {
                \Tk\Log::warning('Cannot get Model::getVolatileId() value, returning 0');
                return 0;
            }
        }
        return $this->getId();
    }

    /**
     * Override this to create a validation method to check your model for errors
     * Handy for use with forms and before saving the data to the DB
     *
     * @toto See if this is really needed. Maybe create an interface....
     */
    public function validate() { return array(); }

    /**
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return \Tk\Config::getInstance();
    }

}