<?php

class AjaxController extends Zend_Controller_Action
{

    private $db;
    private $request;

    public function init()
    {
        $this->request = $this->getRequest();
        $this->db = $this->getFrontController()->getParam('bootstrap')->getPluginResource('db')->getDbAdapter();
        if ($this->request->isPost()) {
            $ajaxContext = $this->_helper->getHelper('AjaxContext');
            $ajaxContext->addActionContext('vote', 'html')
                          ->initContext();
        }
        else  $this->_helper->redirector('index', 'index');
    }

    public function indexAction()
    {
        //do nothing just redirect
        $this->_helper->redirector('index', 'index');
    }

    public function voteAction()
    {
        $channel = $this->request->getParam('channel');
        $ip = $_SERVER['REMOTE_ADDR'];
        $now = new DateTime();
        $time = $now->format('Y-m-d');
        $check_arr = $this->db->query("SELECT id FROM voting WHERE (ip = '$ip') AND (channel = '$channel') AND (created LIKE '$time%')")->fetchAll();
        if ( !(isset($check_arr[0]['id']) && $check_arr[0]['id']) ) {
            //user doesn't vote today
            if ($channel) {
                $this->db->query("UPDATE channels SET rate = rate + 1 WHERE id = '$channel'");
                $this->db->query("INSERT INTO voting (ip, channel) VALUE ('$ip', '$channel')");
                $this->view->data = 'ok';
            }
            else {
                die('error');
            }
        }
        else {
            die('error');
        }
    }
}

