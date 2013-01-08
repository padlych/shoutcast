<?php

class IndexController extends Zend_Controller_Action
{
    private $db;
    private $request;
    private $public_path;

    public function init()
    {
        $this->request = $this->getRequest();
        $this->public_path = preg_replace('/application/', 'public', APPLICATION_PATH);
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('channel', 'html')
            ->addActionContext('style', 'html')
            ->initContext();
//      $this->db = $this->getFrontController()->getParam('bootstrap')->getPluginResource('db')->getDbAdapter();
    }

    public function indexAction()
    {
        //just displays index.phtml
    }

    public function styleAction()
    {
        $controller = $this->request->getParam('controller');
        $action = $this->request->getParam('action');
        $module = $this->request->getParam('module');
        $id = $this->request->getParam('id');
        $params = array('id' => $id);
        return $this->_forward($action, 'ajax', null, $params);
    }

    public function channelAction()
    {
        $controller = $this->request->getParam('controller');
        $action = $this->request->getParam('action');
        $module = $this->request->getParam('module');
        $id = $this->request->getParam('id');
        $params = array('id' => $id);
        return $this->_forward($action, 'ajax', null, $params);
    }

    private function makePlaylist($ids)
    {
        $channelMapper = new Application_Model_ChannelMapper();
        $id_arr = array();
        if ( !is_array($ids) ) {//single id from channel
            $id_arr[] = $ids;
            $channel = $channelMapper->find( $ids, new Application_Model_Channel() );
            $filename = $channel->getStreamObj()->getByfly().'.m3u';
        }
        else {//array of ids from style
            $id_arr = $ids;
            $channel = $channelMapper->find( $id_arr[0], new Application_Model_Channel() );
            $channel_styles = $channel->getStyles();
            $filename = '';
            foreach ($channel_styles as $channel_style) {
                $filename .= $channel_style->getStyle().'_';
            }
            $filename = $filename.'.m3u';
        }
        $list = fopen($this->public_path . '/files/'.$filename, 'w+');
        fwrite($list, "#EXTM3U \n");
        foreach ($id_arr as $id) {
            $channel = $channelMapper->find( $id, new Application_Model_Channel() );
            $string = "#EXTINF:-1, ".$channel->getChannel()."\n";
            $string .= "http://shoutcast.byfly.by:88/".$channel->getStreamObj()->getByfly()."\n\n";
            fwrite($list, $string);
        }
        header("Content-Type: audio/mpeg");
        header("Content-Disposition: attachment; filename=".$filename.";");
        readfile($this->public_path . '/files/'.$filename);
        fclose($list);
        unlink($this->public_path . '/files/'.$filename);
    }

    public function playlistAction() {

//        $data = $this->request->getParam('playlist_data');
//        if ($data) {
//            $arr = explode(':', $data);
//            $list = fopen($public_path . '/files/'.$filename, 'w+');
//            fwrite($list, "#EXTM3U \n");
//            foreach ($arr as $row) {
//                if ( !empty($row) ) {
//                    //now we need to form playlist based on channel ids from $arr
//                    $string_arr = $this->db->query("SELECT cha.channel, str.byfly_stream AS stream FROM channels AS cha
//                     JOIN streams AS str ON cha.best_stream = str.id
//                     WHERE cha.id = '$row'")->fetchAll();
//                    $string = "#EXTINF:-1, ".$string_arr[0]['channel']."\n";
//                    $string .= "http://shoutcast.byfly.by:88/".$string_arr[0]['stream']."\n\n";
//                    fwrite($list, $string);
//                }
//            }
//            fclose($list);
//            //unlink($public_path . '/files/'.$filename);
//        }
        $id = $this->request->getParam('id');
        if ( $id ) {
            $this->makePlaylist($id);
        }
        // disable layout and view
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

}

