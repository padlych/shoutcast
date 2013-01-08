<?php

class Application_Model_StreamMapper
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
            $this->setDbTable('Application_Model_DbTable_Streams');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Stream $stream)
    {
        $data = array(
            'channel'   => $stream->getChannel(),
            'host' => $stream->getHost(),
            'path' => $stream->getPath(),
            'port' => $stream->getPort(),
            'bitrate' => $stream->getBitrate(),
            'format' => $stream->getFormat(),
            'created' => date('Y-m-d H:i:s'),
            'byfly' => $stream->getByfly()
        );

        if (null === ($id = $stream->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    public function find($id, Application_Model_Stream $stream)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return 0;
        }
        $row = $result->current();
        $stream->setId($row->id);
        $stream->setChannel($row->channel);
        $stream ->setHost($row->host);
        $stream ->setPort($row->port);
        $stream->setBitrate($row->bitrate);
        $stream ->setFormat($row->format);
        $stream ->setCreated($row->created);
        $stream ->setByfly($row->byfly);
        return $stream;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Stream();
            $entry->setId($row->id);
            $entry->setChannel($row->channel);
            $entry->setHost($row->host);
            $entry->setPort($row->port);
            $entry->setBitrate($row->bitrate);
            $entry->setFormat($row->format);
            $entry->setCreated($row->created);
            $entry->setByfly($row->byfly);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchDistinct()
    {
        $select = $this->getDbTable()->select()->group('bitrate')->order('bitrate DESC');
        $resultSet = $this->getDbTable()->fetchAll($select);
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Stream();
            $entry->setId($row->id);
            $entry->setChannel($row->channel);
            $entry->setHost($row->host);
            $entry->setPort($row->port);
            $entry->setBitrate($row->bitrate);
            $entry->setFormat($row->format);
            $entry->setCreated($row->created);
            $entry->setByfly($row->byfly);
            $entries[] = $entry;
        }
        return $entries;
    }
}

?>