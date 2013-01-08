<?php

class Application_Model_DbTable_Streams extends Zend_Db_Table_Abstract
{//provides a table to store events
    protected $_name = 'streams';
    protected $_primary = 'id';
}

