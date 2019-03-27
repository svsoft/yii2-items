<?php
namespace svsoft\yii\items\filter;

/**
 * Class FilterAttributeList
 * @package svsoft\yii\items\filter
 * @property array $values
 */
class FilterAttributeList extends FilterAttribute
{
    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';
    const SORT_NATURAL = 'natural';

    private $_values = [];
    public $multiple = false;

    public $decorator;


    public function init()
    {
        parent::init();

        if ($this->decorator && is_callable($this->decorator))
        {
            $this->values = call_user_func($this->decorator, $this->values);
        }
    }

    /**
     * @param $values
     */
    public function setValues(array $values)
    {
        if ($this->decorator && is_callable($this->decorator))
        {
            $values = call_user_func($this->decorator, $values);

        }

        $this->_values = $values;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }

    public function getSortValues($sort)
    {
        $values = $this->values;
        if ($sort == self::SORT_ASC)
            asort($values);
        elseif ($sort == self::SORT_DESC)
            arsort($values);
        elseif ($sort == self::SORT_NATURAL)
            natsort($values);

        return $values;
    }
}