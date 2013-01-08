<?php

class Application_Model_TwitterMapper
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
            $this->setDbTable('Application_Model_DbTable_Twitter');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Twitter $twitter)
    {
        $data = array(
            'screen' =>  $twitter->getScreen(),
            'img' => $twitter->getImg(),
        );
        if ( !$this->find($twitter->getScreen(), new Application_Model_Twitter()) ) {
           $this->getDbTable()->insert($data);
        } else {
           $this->getDbTable()->update($data, array('screen = ?' => $twitter->getScreen()));
        }

    }

    public function find($screen, Application_Model_Twitter $twitter)
    {
        $result = $this->getDbTable()->find($screen);
        if (0 == count($result)) {
            return 0;
        }
        $row = $result->current();
        $twitter->setScreen($row->screen);
        $twitter->setImg($row->img);
        $twitter->setCreated($row->created);
        return $twitter;
    }
   
    private function fetchResult($resultSet)
    {
        $entries = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Twitter();
            $entry->setScreen($row->screen);
            $entry->setImg($row->img);
            $entry->setCreated($row->created);
            $entries[] = $entry;
        }
        if ( count($entries) == 1 ) return $entries[0];
        else return $entries;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        return $this->fetchResult($resultSet);
    }

    public function fetchRandom()
    {
        $count = 4;
        $select = $this->getDbTable()->select()->order('RAND()')->limit($count, 0);
        $resultSet = $this->getDbTable()->fetchAll($select);
        return $this->fetchResult($resultSet);
    }
}

?>