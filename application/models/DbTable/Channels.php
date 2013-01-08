<?php

class Application_Model_DbTable_Channels extends Zend_Db_Table_Abstract
{//provides a table to store events
    protected $_name = 'channels';
    protected $_primary = 'id';
}

