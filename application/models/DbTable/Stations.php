<?php

class Application_Model_DbTable_Stations extends Zend_Db_Table_Abstract
{//provides a table to store events
    protected $_name = 'stations';
    protected $_primary = 'id';
}

