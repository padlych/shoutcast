<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public $view;
    private $db;
    private $channelMapper;

    public function _initVars()
    {
        date_default_timezone_set('Europe/Minsk');
        $this->bootstrap('view');
        $this->view = $this->getResource('view');
    }

    protected function _initDoctype()
    {

        $this->view->doctype('XHTML1_STRICT');
        $this->view->headTitle('Shoutcast')
            ->setSeparator(' :: ');
        $this->view->headScript()->appendFile('/js/jquery-1.4.2.min.js');
        $this->view->headScript()->appendFile('/js/jquery-ui-1.8.2.custom.min.js');
        $this->view->headScript()->appendFile('/jplayer/jquery.jplayer.min.js');
        $this->view->headLink()->appendStylesheet('/jplayer/blue/jplayer.blue.monday.css');
        $this->view->headLink()->appendStylesheet('/css/screen.css');
        $this->view->headLink()->appendStylesheet('/css/content_main.css');
        $this->view->headLink()->appendStylesheet('/css/styleie6.css', 'screen', 'IE6');

        $loader = new Zend_Loader_Autoloader_Resource (array ('basePath' => APPLICATION_PATH,
            'namespace' => 'Application'));
        $loader->addResourceType( 'model', 'models', 'Model');
        $this->channelMapper = new Application_Model_ChannelMapper();
    }

    protected function _initDatabase()
    {
        // get config from config/application.ini
        $config = $this->getOptions();
        //load db from config
        $db = Zend_Db::factory($config['resources']['db']['adapter'], $config['resources']['db']['params']);
        $this->db = $db;
        //set default adapter
        Zend_Db_Table::setDefaultAdapter($db);
        //save Db in registry for later use
        Zend_Registry::set("db", $db);
    }

    protected function _initTagcloud()
    {
        //set font size according to the number of channels in db

        $styleMapper = new Application_Model_StyleMapper();
        $styles = $styleMapper->fetchAll();
        $map = $this->db->query("SELECT sty.`id` AS style, COUNT(cha.`channel`) AS amount
                                 FROM styles AS sty
                                 JOIN cha_sty AS map ON sty.id = map.styID
                                 JOIN channels AS cha ON map.chaID = cha.id
                                 WHERE 1
                                 GROUP BY sty.`style`
                                 ORDER BY amount DESC
        ")->fetchAll();
        $tags = new Application_Model_StyleAmountFont($styles, $map);
        $this->view->boot_tags = $tags->fetchAll();
    }

    protected function _initNewstations()
    {
        $this->view->boot_new = $this->channelMapper->fetchNew();
    }

    protected function _initRandstations()
    {
        $this->view->boot_rand = $this->channelMapper->fetchRandom(10);
    }

    protected function _initTop4stations()
    {
        $this->view->boot_top4 = $this->channelMapper->fetchRandom(4);
    }

}

