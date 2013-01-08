<?php

class Application_Model_StyleMapper
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
            $this->setDbTable('Application_Model_DbTable_Styles');
        }
        return $this->_dbTable;
    }

    public function save(Application_Model_Style $style)
    {
        $data = array(
            'style'   => $style->getStyle(),
            'descr' => $style->getDescr(),
            'font' => $style->getFont(),
        );

        if (null === ($id = $style->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    public function find($id, Application_Model_Style $style)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return 0;
        }
        $row = $result->current();
        $style->setId($row->id);
        $style->setStyle($row->style);
        $style->setDescr($row->descr);
        $style->setFont($row->font);
        return $style;
    }

    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Style();
            $entry->setId($row->id);
            $entry->setStyle($row->style);
            $entry->setDescr($row->descr);
            $entry->setFont($row->font);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchAllAsc()
    {
        $select = $this->getDbTable()->select()->order('style ASC');
        $resultSet = $this->getDbTable()->fetchAll($select);
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Style();
            $entry->setId($row->id);
            $entry->setStyle($row->style);
            $entry->setDescr($row->descr);
            $entry->setFont($row->font);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchStylesPerChannel($id)
    {
        $entries = array();
        $keyMapper = new Application_Model_KeyMapper();
        $styleMapper = new Application_Model_StyleMapper();
        $keys = $keyMapper->fetchStylesPerChannel($id);
        foreach ($keys as $key) {
            $style = $styleMapper->find( $key, new Application_Model_Style() );
            $entries[] = $style;
        }
        return $entries;
    }

}

?>