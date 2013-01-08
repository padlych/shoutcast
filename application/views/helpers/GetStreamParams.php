<?php
class Zend_View_Helper_GetStreamParams extends Zend_View_Helper_Abstract
{
    private $db;

    public function __construct()
    {
        $this->db = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getPluginResource('db')->getDbAdapter();
    }

    public function getParams($strid) {
//        $params = array(
//            'host' => 'http://shoutcast.byfly.by',
//            'port' => '88',
//            'channel' => '181chilled',
//            'format' => 'mp3',
//            'bitrate' => '96',
//             'title' => '181chilled',
//             'byfly_stream' => '181chilled'
//        );
        $params = array();
        $res_arr = $this->db->query("SELECT str.host, str.port, str.path, str.format, str.bitrate, str.byfly_stream, cha.channel FROM streams AS str
         JOIN channels AS cha ON str.id = cha.best_stream WHERE str.id = '$strid'")->fetchAll();
        $stream = $res_arr[0];
        $params['host'] = 'http://'.$stream['host'];
        $params['port'] = $stream['port'];
        if ($stream['path'] == 'root') {
            $params['channel'] = '';
        }
        else {
            $params['channel'] = $stream['path'];
        }
        $params['title'] = $stream['channel'];
        $params['format'] = $stream['format'];
        $params['bitrate'] = $stream['bitrate'];
        $params['byfly_stream'] = $stream['byfly_stream'];
        return $params;
    }
}