<?php
namespace Tk\Db\Map;

use Tk\Db\Pdo;

/**
 * Class Mapper
 *
 * Some reserved column names and assumed meanings:
 *  - `id`       => An integer that is assumed to be the records primary key
 *                  foreign keys are assumed to be named `<foreign_table>_id`
 *  - `modified` => A timestamp that gets incremented on updates
 *  - `created`  => A timestamp not really reserved but assumed
 *  - `del`      => If it exists the records are marked `del` = 1 rather than deleted
 *
 * If your columns conflict, then you should modify the mapper or DB accordingly
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
abstract class DelMapper extends Mapper
{

    /**
     * Mapper constructor.
     *
     * @param null|Pdo $db
     */
    public function __construct($db = null)
    {
        parent::__construct($db);
        $this->setMarkDeleted('del');
    }

}