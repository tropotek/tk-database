<?php

namespace Tk\Db;


/**
 * Use this object to enhance your Mapper filtered queries
 *
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
class Filter extends \Tk\Collection
{
    /**
     * @var null|Tool
     */
    protected $tool = null;

    /**
     * @var string
     */
    protected $from = '';

    /**
     * @var string
     */
    protected $where = '';



    /**
     * Filter constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param null|array $params
     * @param Tool|null $tool
     * @return Filter
     */
    public static function create(?array $params, ?Tool $tool)
    {
        if ($params instanceof Filter) return $params;
        $obj = new self();
        $obj->replace($params);
        $obj->setTool($tool);
        return $obj;
    }

    /**
     * @return Tool|null
     */
    public function getTool(): ?Tool
    {
        return $this->tool;
    }

    /**
     * @param Tool|null $tool
     * @return Filter
     */
    public function setTool(?Tool $tool): Filter
    {
        $this->tool = $tool;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
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
        return $this->where;
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