<?php

namespace Coyote\Services\Grid;

class Order
{
    /**
     * @var string
     */
    protected $column;

    /**
     * @var string
     */
    protected $direction;

    /**
     * @param string $column
     * @param string $direction
     */
    public function __construct($column, $direction)
    {
        $this->column = $column;
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param string $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
}
