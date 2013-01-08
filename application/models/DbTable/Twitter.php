<?php

class Application_Model_DbTable_Twitter extends Zend_Db_Table_Abstract
{//provides a table to store events
    protected $_name = 'twitter';
    protected $_primary = 'screen';
}

