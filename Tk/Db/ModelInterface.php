<?php
namespace Tk\Db;

/**
 * Interface ModelInterface
 *
 * I have implemented this so that the framework can use DB models
 * and depend on a set of functions.
 * 
 * 
 * 
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 * @todo: May not be needed yet? I have not implemented it anywhere mut the map...
 */
interface ModelInterface
{


    /**
     * Get the model primary DB key, usually ID
     *
     * @return mixed|int
     */
    public function getId();

    /**
     * Returns the object id if it is greater than 0 or the `nextInsertId` if is 0
     *
     * @return int
     */
    public function getVolatileId();

    
    /**
     * Insert the object into storage.
     * By default this is a database
     *
     * @return int The insert ID
     */
    public function insert();

    /**
     * Update the object in storage
     *
     * @return int
     */
    public function update();

    /**
     * A Utility method that checks the id and does and insert
     * or an update  based on the objects current state
     *
     */
    public function save();

    /**
     * Delete the object from the DB
     *
     * @return int
     */
    public function delete();

}