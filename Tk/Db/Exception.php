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

    /**
     * Exception constructor.
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param string $sql
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null, $sql = '')
    {
        //format dump query
        $sql = explode("\n", str_replace(array(',', ' WHERE', ' FROM', ' LIMIT', ' ORDER', ' LEFT JOIN'),
            array(', ', "\n  WHERE", "\n  FROM", "\n  LIMIT", "\n  ORDER", "\n  LEFT JOIN"),$sql));
        foreach ($sql as $i => $s) {
            $sql[$i] = '  ' . wordwrap($s, 120, "\n  ");
        }
        $sql = "\n\nQuery: \n" . implode("\n", $sql);

        parent::__construct($message, $code, $previous, $sql);
    }

}
