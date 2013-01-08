<?php

class IndexController extends Zend_Controller_Action
{

    private $request;
    private $db;

    public function init()
    {
        $this->request = $this->getRequest();
        $this->db = $this->getFrontController()->getParam('bootstrap')->getPluginResource('db')->getDbAdapter();
        if ($this->request->isPost()) {
            $ajaxContext = $this->_helper->getHelper('AjaxContext');
            $ajaxContext->addActionContext('vote', 'html')
                ->initContext();
        }
        //getting list of styles for enchanted search
        $styles = $this->db->query("SELECT id, style FROM styles WHERE 1 ORDER BY style ASC")->fetchAll();
        Zend_Layout::getMvcInstance()->assign('styles', $styles);
        //select available bitrates for search
        $bitrates = $this->db->query("SELECT bitrate, id FROM streams WHERE 1 GROUP BY bitrate ORDER BY bitrate DESC")->fetchAll();
        Zend_Layout::getMvcInstance()->assign('bitrates', $bitrates);
    }


    public function indexAction()
    {
        // action body
        //just show index page
    }

    public function channelAction()
    {
        //displays channel details
        $chid = $this->request->getParam('chid');
        if ( $chid == '' ) $chid = 1;
        $channel =  $this->db->query("SELECT cha.description AS ch_descr, sta.description AS st_descr, cha.id, cha.best_stream, sta.station, cha.channel, cha.pic, cha.created, sta.www FROM channels AS cha
        JOIN stations AS sta ON (cha.id_station = sta.id) WHERE (cha.best_stream != 0 AND cha.id = '$chid') ")->fetchAll();
        $this->view->channel =  $channel[0];

    }

    public function styleAction ()
    {
        //displays all stations in selected style
        $style_id = $this->request->getParam('id');
        if ( $style_id == '' ) $style_id = 1;
        $res_arr = $this->db->query("SELECT ch.id, ch.channel, ch.pic, sta.www, str.bitrate, str.format FROM ch_style AS ref
         JOIN channels AS ch ON ref.chID = ch.id
         JOIN streams AS str ON ch.best_stream = str.id
         JOIN stations AS sta ON ch.id_station = sta.id
         WHERE ref.styleID = '$style_id'")->fetchAll();
        $this->view->styles = $res_arr;
        $style_arr = $this->db->query("SELECT style FROM styles WHERE id = '$style_id'")->fetchAll();
        $this->view->style = $style_arr[0]['style'];
    }

    public function searchAction()
    {
        if ( $this->request->isPost() ) {
            //die(print_r($_POST));

            $style = " AND ref.styleID IN ( 0";
            $style_in = "";
            $amount_arr = $this->db->query("SELECT COUNT(id) AS amount FROM styles WHERE 1")->fetchAll();
            $amount = $amount_arr[0]["amount"];
            for ($i = 0; $i < $amount; $i++) {
                if ( isset($_POST['style_'.$i]) ) {
                    $style_in .= ", ".$_POST['style_'.$i];
                }
            }
            if ($style_in) {
                $style .= $style_in." )";
            }
            else $style = "";

            $bitrate = $this->request->getParam('bitrate');
            if ($bitrate == 'all') {
                $rate = '';
            }
            else {
                $rate = "  AND (str.bitrate = '$bitrate')";
            }

            //need to parse $channel string
            $channel = $this->request->getParam('channel');
            $channel = trim($channel);
            if ($channel) {
                $item_arr = explode(' ', $channel);
                foreach ($item_arr as $key => $value ){
                    $item_arr[$key] = strtolower($value);
                }
                //item arr contain key words
                //need to search for this words in stations.station, channels.channel, streams.bitrate, streams.format
                $string = "";
                $flag = 0;
                foreach ($item_arr as $key => $value){
                    if ($value && (strlen($value) > 2) ) {
                        if (!$flag) {
                            $string .= " ( MATCH(sta.station) AGAINST('$value') ) OR ( MATCH(cha.channel) AGAINST('$value') ) OR (str.bitrate = '$value') OR (str.format = '$value')  ";
                        }
                        else $string .= " OR ( MATCH(sta.station) AGAINST('$value') ) OR ( MATCH(cha.channel) AGAINST('$value') ) OR (str.bitrate = '$value') OR (str.format = '$value')  ";
                        $flag++;
                    }
                }
                $string = " AND ( ".$string." ) ";
            }
            else $string = "";

            $query = "SELECT DISTINCT cha.id, cha.channel, cha.pic, ref.styleID, sta.www, str.bitrate, str.format FROM channels AS cha
            RIGHT JOIN streams AS str ON (str.id = cha.best_stream)
            RIGHT JOIN ch_style AS ref ON (ref.chID = cha.id)
            RIGHT JOIN stations AS sta ON (sta.id = cha.id_station)
            WHERE  1 ".$rate." ".$style." ".$string." AND (cha.best_stream <> '0') GROUP BY cha.id LIMIT 0, 200";
            //die($query);
            $pre_channel_arr = $this->db->query($query)->fetchAll();
            //now channel array contain only channels mathced by style and bitrate
            $this->view->styles = $pre_channel_arr;
        }
    }

    public function playlistAction() {
        $filename = md5(rand(0,99999)).'.m3u';
        $public_path = preg_replace('/application/', 'public', APPLICATION_PATH);

        $data = $this->request->getParam('playlist_data');
        if ($data) {
            $arr = explode(':', $data);
            $list = fopen($public_path . '/files/'.$filename, 'w+');
            fwrite($list, "#EXTM3U \n");
            foreach ($arr as $row) {
                if ( !empty($row) ) {
                    //now we need to form playlist based on channel ids from $arr
                    $string_arr = $this->db->query("SELECT cha.channel, str.byfly_stream AS stream FROM channels AS cha
                     JOIN streams AS str ON cha.best_stream = str.id
                     WHERE cha.id = '$row'")->fetchAll();
                    $string = "#EXTINF:-1, ".$string_arr[0]['channel']."\n";
                    $string .= "http://shoutcast.byfly.by:88/".$string_arr[0]['stream']."\n\n";
                    fwrite($list, $string);
                }
            }
            fclose($list);
            //unlink($public_path . '/files/'.$filename);
        }
        header("Content-Type: audio/mpeg");
        header("Content-Disposition: attachment; filename=".$filename.";");
        readfile($public_path . '/files/'.$filename);
        // disable layout and view
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }


}

