<?php
/**
 * Created by JetBrains PhpStorm.
 * User: evgeny
 * Date: 25.10.12
 * Time: 15:11
 * To change this template use File | Settings | File Templates.
 */
class Application_Model_Station
{
    //contains setters end getters for neccessary fields
    private $id;
    private $station;
    private $www;
    private $created;
    private $description;


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
            throw new Exception('Invalid station property');
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid station property');
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setStation($station)
    {
        $this->station = $station;
    }

    public function getStation()
    {
        return $this->station;
    }

    public function setWww($www)
    {
        $this->www = $www;
    }

    public function getWww()
    {
        return $this->www;
    }


}