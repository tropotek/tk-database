<?php
namespace Tk\Db;

/**
 * Class Exception
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Exception extends \Tk\Exception
{

    public function __construct($message = "", $code = 0, \Throwable $previous = null, $dump = '')
    {
        $dump = explode("\n", str_replace(array(',', ' WHERE', ' FROM', ' LIMIT', ' ORDER', ' LEFT JOIN'), array(', ', "\n  WHERE", "\n  FROM", "\n  LIMIT", "\n  ORDER", "\n  LEFT JOIN"),$dump));
        foreach ($dump as $i => $s) {
            $dump[$i] = wordwrap($s, 120, "\n    ");
        }
        $dump = implode("\n", $dump);

        parent::__construct($message, $code, $previous, $dump);
    }

}
