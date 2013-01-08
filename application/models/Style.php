<?php
/**
 * Created by JetBrains PhpStorm.
 * User: evgeny
 * Date: 25.10.12
 * Time: 15:11
 * To change this template use File | Settings | File Templates.
 */
class Application_Model_Style
{
    //contains setters end getters for neccessary fields
    private $id;
    private $style;
    private $descr;
    private $font;

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid channel property');
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid channel property');
        }
        return $this->$method();
    }

    public function setDescr($descr)
    {
        $this->descr = $descr;
    }

    public function getDescr()
    {
        return $this->descr;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function getStyle()
    {
        return $this->style;
    }

    public function setFont($font)
    {
        $this->font = $font;
    }

    public function getFont()
    {
        return $this->font;
    }


}