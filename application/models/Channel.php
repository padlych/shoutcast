<?php
/**
 * Created by JetBrains PhpStorm.
 * User: evgeny
 * Date: 25.10.12
 * Time: 15:11
 * To change this template use File | Settings | File Templates.
 */
class Application_Model_Channel
{
    //contains setters end getters for neccessary fields
    private $id;
    private $station;
    private $channel;
    private $created;
    private $pic;
    private $stream;
    private $rate;
    private $description;
    private $users;
    private $styles;
    private $streamObj;
    private $stationObj;

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

    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    public function getChannel()
    {
        return $this->channel;
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

    public function setPic($pic)
    {
        $this->pic = $pic;
    }

    public function getPic()
    {
        return $this->pic;
    }

    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    public function getRate()
    {
        return $this->rate;
    }

    public function setStation($station)
    {
        $this->station = $station;
    }

    public function getStation()
    {
        return $this->station;
    }

    public function setStream($stream)
    {
        $this->stream = $stream;
    }

    public function getStream()
    {
        return $this->stream;
    }

    public function setStyles($styles)
    {
        $this->styles = $styles;
    }

    public function getStyles()
    {
        return $this->styles;
    }

    public function setStreamObj($streamObj)
    {
        $this->streamObj = $streamObj;
    }

    public function getStreamObj()
    {
        return $this->streamObj;
    }

    public function setStationObj($stationObj)
    {
        $this->stationObj = $stationObj;
    }

    public function getStationObj()
    {
        return $this->stationObj;
    }

    public function setUsers($online)
    {
        $this->users = $online;
    }

    public function getUsers()
    {
        return $this->users;
    }


}