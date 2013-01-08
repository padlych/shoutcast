<?php

class Application_Model_StationMapper
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
            $this->setDbTable('Application_Model_DbTable_Stations');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Station $station)
    {
        $data = array(
            'station'   => $station->getStation(),
            'www' => $station->getWww(),
            'created' => date('Y-m-d H:i:s'),
            'description' => $station->getDescription(),
        );

        if (null === ($id = $station->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    public function find($id, Application_Model_Station $station)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return 0;
        }
        $row = $result->current();
        $station                  ->setId($row->id);
        $station        ->setStation($row->station);
        $station                ->setWww($row->www);
        $station        ->setCreated($row->created);
        $station->setDescription($row->description);
        return $station;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Station();
            $entry->setId($row->id);
            $entry->setStation($row->station);
            $entry->setWww($row->www);
            $entry->setCreated($row->created);
            $entry->setDescription($row->description);
            $entries[] = $entry;
        }
        return $entries;
    }
}

?>