<?php

namespace Tk\Db;


/**
 * Use this object to enhance your Mapper filtered queries
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
class Filter extends \Tk\Collection
{

    /**
     * @var string
     */
    protected $select = '';

    /**
     * @var string
     */
    protected $from = '';

    /**
     * @var string
     */
    protected $where = '';



    /**
     * @param null|array $params
     * @return Filter
     */
    public static function create(?array $params)
    {
        if ($params instanceof Filter) return $params;
        $obj = new self();
        $obj->replace($params);
        return $obj;
    }

    /**
     * @return string
     */
    public function getSelect(): string
    {
        $str = $this->select;
        $str = trim($str);
        $str = rtrim($str, ',');
        return $str;
    }

    /**
     * @param string $select
     * @return Filter
     */
    public function setSelect(string $select): Filter
    {
        $this->select = $select;
        return $this;
    }

    /**
     * @param string $select
     * @param array $args
     * @return Filter
     */
    public function prependSelect(string $select, ...$args): Filter
    {
        if ($args)
            $this->select = vsprintf($select, $args) . $this->select;
        else
            $this->select = $select . $this->select;
        return $this;
    }

    /**
     * @param string $select
     * @param array $args
     * @return Filter
     */
    public function appendSelect(string $select, ...$args): Filter
    {
        if ($args)
            $this->select .= vsprintf($select, $args);
        else
            $this->select .= $select;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        $str = $this->from;
        $str = trim($str);
        $str = rtrim($str, ',');
        return $str;
    }

    /**
     * @param string $from
     * @return Filter
     */
    public function setFrom(string $from): Filter
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @param string $from
     * @param array $args
     * @return Filter
     */
    public function prependFrom(string $from, ...$args): Filter
    {
        if ($args)
            $this->from = vsprintf($from, $args) . $this->from;
        else
            $this->from = $from . $this->from;
        return $this;
    }

    /**
     * @param string $from
     * @param array $args
     * @return Filter
     */
    public function appendFrom(string $from, ...$args): Filter
    {
        if ($args)
            $this->from .= vsprintf($from, $args);
        else
            $this->from .= $from;
        return $this;
    }


    /**
     * @return string
     */
    public function getWhere(): string
    {
        $str = $this->where;
        $str = trim($str);
        $str = rtrim($str, 'AND');
        $str = rtrim($str, 'OR');
        return $str;
    }

    /**
     * @param string $where
     * @return Filter
     */
    public function setWhere(string $where): Filter
    {
        $this->where = $where;
        return $this;
    }

    /**
     * @param string $where
     * @param array $args
     * @return Filter
     */
    public function prependWhere(string $where, ...$args): Filter
    {
        if ($args)
            $this->where = vsprintf($where, $args) . $this->where;
        else
            $this->where = $where . $this->where;
        return $this;
    }

    /**
     * @param string $where
     * @param array $args
     * @return Filter
     */
    public function appendWhere(string $where, ...$args): Filter
    {
        if ($args)
            $this->where .= vsprintf($where, $args);
        else
            $this->where .= $where;
        return $this;
    }

}