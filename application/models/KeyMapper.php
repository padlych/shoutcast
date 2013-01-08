<?php

class Application_Model_KeyMapper
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
            $this->setDbTable('Application_Model_DbTable_Keys');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Key $key)
    {
        $data = array(
            'chaID'   => $key->getChaID(),
            'styID' => $key->getStyID(),
        );

        if (null === ($id = $key->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    public function find($id, Application_Model_Key $key)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return 0;
        }
        $row = $result->current();
        $key->setId($row->id);
        $key->setStyID($row->styID);
        $key->setChaID($row->chaID);
        return $key;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Key();
            $entry->setId($row->id);
            $entry->setStyID($row->styID);
            $entry->setChaID($row->chaID);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchStylesPerChannel($id)
    {//returns array of style indexes
        $entries = array();
        $select = $this->getDbTable()->select()->where('chaID = ?', $id);
        $resultSet = $this->getDbTable()->fetchAll($select);
        foreach ($resultSet as $row) {
            $entries[] = $row->styID;
        }
        return $entries;
    }

    public function fetchChannelsPerStyle($id)
    {
        {//returns array of channel indexes
            $entries = array();
            $select = $this->getDbTable()->select()->where('styID = ?', $id);
            $resultSet = $this->getDbTable()->fetchAll($select);
            foreach ($resultSet as $row) {
                $entries[] = $row->chaID;
            }
            return $entries;
        }
    }
}

?>