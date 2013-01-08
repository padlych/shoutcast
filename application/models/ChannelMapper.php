<?php

class Application_Model_ChannelMapper
{
    protected $_dbTable;

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Channels');
        }
        return $this->_dbTable;
    }

    public static function getInstance()
    {
        return new Application_Model_ChannelMapper();
    }

    public function save(Application_Model_Channel $channel)
    {
        $data = array(
            'station'   => $channel->getStation(),
            'channel' => $channel->getChannel(),
            'created' => date('Y-m-d H:i:s'),
            'pic' => $channel->getPic(),
            'stream' => $channel->getStream(),
            'rate' => $channel->getRate(),
            'description' => $channel->getDescription(),
            'users' => $channel->getUsers()
        );

        if (null === ($id = $channel->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    public function find($id, Application_Model_Channel $channel)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return 0;
        }
        $row = $result->current();
        $channel->setId($row->id);
        $channel ->setStation($row->station);
        $channel->setChannel($row->channel);
        $channel ->setCreated($row->created);
        $channel->setPic($row->pic);
        $channel->setStream($row->stream);
        $channel->setRate($row->rate);
        $channel->setDescription($row->description);
        $channel->setUsers($row->users);
        return $this->fetchResult($result);
    }

    private function fetchResult($resultSet)
    {
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Channel();
            $entry->setId($row->id);
            $entry->setStation($row->station);
            $entry->setChannel($row->channel);
            $entry->setCreated($row->created);
            $entry->setPic($row->pic);
            $entry->setStream($row->stream);
            $entry->setRate($row->rate);
            $entry->setDescription($row->description);
            $entry->setUsers($row->users);
            $styleMapper = new Application_Model_StyleMapper();
            $entry->setStyles( $styleMapper->fetchStylesPerChannel($row->id) );
            $streamMapper = new Application_Model_StreamMapper();
            $entry->setStreamObj( $streamMapper->find( $row->stream, new Application_Model_Stream() ) );
            $stationMapper = new Application_Model_StationMapper();
            $entry->setStationObj( $stationMapper->find( $row->station, new Application_Model_Station() ) );
            if ( $entry->getStreamObj() && ($entry->getStreamObj()->getFormat() == 'mp3' || $entry->getStreamObj()->getFormat() == 'ogg') ) {
                $entries[] = $entry;
            }
        }
        if ( count($entries) == 1 ) return $entries[0];
        else return $entries;
    }



    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        return $this->fetchResult($resultSet);
    }

    public function fetchNew()
    {
        $count = 3;
        $select = $this->getDbTable()->select()->where('stream <> 0')->order('created DESC')->limit($count, 0);
        $resultSet = $this->getDbTable()->fetchAll($select);
        return $this->fetchResult($resultSet);
    }

    public function fetchRandom($count)
    {
        $out = array();
        $select = $this->getDbTable()->select()->where('stream IS NOT NULL')->order('RAND()')->limit($count + $count, 0);
        $resultSet = $this->getDbTable()->fetchAll($select);
        $items = $this->fetchResult($resultSet);
        for ($i = 0; $i < $count; $i++ ) {
            if (isset($items[$i]) && $items[$i] instanceof Application_Model_Channel) {
                $out[] = $items[$i];
            }
        }
        return $out;
    }

    public function fetchStyle($id)
    {
        $keyMapper = new Application_Model_KeyMapper();
        $channel_ids = $keyMapper->fetchChannelsPerStyle($id);
        $select = $this->getDbTable()->select()->where('stream <> 0')->where('id IN (?)', $channel_ids);
        $resultSet = $this->getDbTable()->fetchAll($select);
        return $this->fetchResult($resultSet);
    }

    public function fetchTop20()
    {
        $count = 21;
        $select = $this->getDbTable()->select()->where('stream <> 0')->order('rate DESC')->limit($count, 0);
        $resultSet = $this->getDbTable()->fetchAll($select);
        return $this->fetchResult($resultSet);
    }

    public function findString($in_query_string)
    {
        $select = $this->getDbTable()->select()->where("channel LIKE ? OR description LIKE ?", '%'.$in_query_string.'%');
        $resultSet = $this->getDbTable()->fetchAll($select);
//        die(print_r($this->fetchResult($resultSet)));
        return $this->fetchResult($resultSet);
    }

    public function findStringInChannels(array $channel_ids, $in_query_string )
    {
        $select = $this->getDbTable()->select()
            ->where("channel LIKE ? OR description LIKE ?", '%'.$in_query_string.'%')
            ->where("id IN (?)", $channel_ids);
        $resultSet = $this->getDbTable()->fetchAll($select);
//        die(print_r($this->fetchResult($resultSet)));
        return $this->fetchResult($resultSet);
    }

}

?>