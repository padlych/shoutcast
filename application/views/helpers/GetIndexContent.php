<?php
class Zend_View_Helper_GetIndexContent extends Zend_View_Helper_Abstract
{
    private $db;

    public function __construct()
    {
        $this->db = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getPluginResource('db')->getDbAdapter();
    }


    private function arrayMapper(array $a) {
        //returns array like 0=>16, 1=>0, 2=>22, etc. e.e el_index=>font_number
        $fonts = array(0, 16, 22, 28, 33, 44, 56);
        $amount = 0; $fontNum = 6;
        //find max
        $tmp_arr = $a;
        arsort($tmp_arr);
        $outMapper = array();
        foreach( $tmp_arr as $key => $value) {
            if ( ($value != $amount) && $fontNum) {// current number of channels varies from previous and channel is significant
                $outMapper[$key] = $fonts[$fontNum];
                $amount = $value;
                if ($fontNum > 0) $fontNum--;
            }
            else {
                $outMapper[$key] = $fonts[$fontNum];
            }
        }
        return $outMapper;
    }

    private function wrapStyleRate(array $styles) {
        $html = '';
        $inMapper = array();
        foreach ($styles as $style) {
            $inMapper[] = $style['amount'];
        }
        $mapper = $this->arrayMapper($inMapper);
        $i = 0;
        foreach ($styles as $style) {
            $class = '';
            if ($mapper[$i] != '0') {
                $class = 'class="size'.$mapper[$i].'"';
            }
            $html .= '<span class="fancy" title="'.$style['descr'].'"><a href="/index/style/id/'.$style['id'].'" '.$class.'>'.$style['style'].'</a></span> ';
            $i++;
        }
        return $html;
    }

    private function wrapStyleAmount(array $styles) {
        $html = '';
        $inMapper = array();
        foreach ($styles as $style) {
            $inMapper[] = $style['amount'];
        }
        $mapper = $this->arrayMapper($inMapper);
        $i = 0;
        foreach ($styles as $style) {
            $class = '';
            if ($mapper[$i] != '0') {
                $class = 'class="size'.$mapper[$i].'"';
            }
            $html .= '<span class="fancy" title="'.$style['descr'].'"><a href="/index/style/id/'.$style['id'].'" '.$class.'>'.$style['style'].'</a></span> ';
            $i++;
        }
        return $html;
    }

    private function wrapStyleAsc(array $styles) {
        $html = '';
        $inMapper = array();
        foreach ($styles as $style) {
            $inMapper[] = $style['amount'];
        }
        $mapper = $this->arrayMapper($inMapper);
        $i = 0;
        foreach ($styles as $style) {
            $class = '';
            if ($mapper[$i] != '0') {
                $class = 'class="size'.$mapper[$i].'"';
            }
            $html .= '<span class="fancy" title="'.$style['descr'].'"><a href="/index/asc/id/'.$style['id'].'" '.$class.'>'.$style['style'].'</a></span> ';
            $i++;
        }
        return $html;
    }

    public function getStyles($flag)
    {
       //flag indicates criteria for sorting amount of stations | summed station's rate

        if ($flag == 'rate') {
            $styles = $this->db->query("SELECT st.id, st.style, st.descr, SUM(channels.rate) AS amount FROM styles AS st
            JOIN ch_style AS ch ON st.id = ch.styleID JOIN channels ON ch.chID = channels.id GROUP BY st.id")->fetchAll();
            return $this->wrapStyleRate($styles);
        }
        elseif ($flag == 'asc') {
            $styles = $this->db->query("SELECT st.id, st.style, st.descr, st.id AS amount FROM styles AS st
            JOIN ch_style AS ch ON st.id = ch.styleID JOIN channels ON ch.chID = channels.id GROUP BY st.id")->fetchAll();
            return $this->wrapStyleAsc($styles);
        }
        elseif ($flag == 'amount') {
            $styles = $this->db->query("SELECT st.id, st.style, st.descr, COUNT(ch.chID) AS amount FROM styles AS st
            JOIN ch_style AS ch ON st.id = ch.styleID GROUP BY st.id")->fetchAll();
            return $this->wrapStyleAmount($styles);
        }
        else return 'error';

    }

    public function getNewChannels()
    {//return new channels (Station-channel)
        //used in left column returns 3 new channels
        $res_arr = $this->db->query("SELECT cha.id, sta.station, cha.channel, cha.pic, cha.created FROM channels AS cha
        JOIN stations AS sta ON (cha.id_station = sta.id) WHERE (cha.best_stream != 0) ORDER BY cha.created DESC LIMIT 0,3")->fetchAll();
        return $res_arr;
    }

    public function getTopChannels($amount)
    {
        if ( !is_numeric($amount) ) die('error in getTopChannels. Amount is not numeric');
        $res_arr = $this->db->query("SELECT cha.id, sta.station, cha.channel, cha.pic, cha.created FROM channels AS cha
        JOIN stations AS sta ON (cha.id_station = sta.id) WHERE (cha.best_stream != 0) ORDER BY cha.rate DESC LIMIT 0, {$amount} ")->fetchAll();
        return $res_arr;
    }

    public function getChannelStyles($chid)
    {
        $html = '';
        $res_arr = $this->db->query("SELECT st.style, st.id FROM ch_style AS ch JOIN styles as st ON (ch.styleID = st.id) WHERE ch.chID = '$chid' ")->fetchAll();
        foreach ($res_arr as $row) {
            $html .= '<a href="/index/style/id/'.$row['id'].'"> '.$row['style'].' </a>';
        }
        if ($html) return $html;
        else return 'mixed style';
    }

    public function getChannelStream($chid)
    {

       $res_arr = $this->db->query("SELECT bitrate, format FROM streams WHERE id IN (SELECT best_stream FROM channels WHERE id = '$chid')")->fetchAll();
        if ( isset($res_arr[0]['bitrate']) && isset($res_arr[0]['bitrate']) )
            return ($res_arr[0]['bitrate'].' kbits '.strtoupper($res_arr[0]['format']) );
        else return '';
    }

    public function getRandChannel($amount)
    {
        $count_arr = $this->db->query("SELECT COUNT(id) AS amount FROM channels WHERE 1")->fetchAll();
        $id_arr = array();
        $output = array();
        while (count($id_arr) < $amount) {
            $id = rand(1, $count_arr[0]['amount']);
            if ( !in_array($id, $id_arr) ) {
                $channel = $this->db->query( "SELECT cha.id, cha.pic, cha.channel, str.bitrate, str.format FROM channels AS cha
                JOIN streams AS str ON cha.best_stream = str.id
                WHERE (cha.id = '$id') AND (cha.best_stream <> 0) AND (cha.rate <> 0)" )
                ->fetchAll();
                if ( isset($channel[0]['id']) ) {
                    array_push($id_arr, $channel[0]['id']);
                    array_push($output, $channel[0]);
                }
            }
        }
        return $output;
    }

    public function getChannelPlace($id)
    {
        if ($id) {
            $rates = array();
            $result = array();
            $arr = $this->db->query("SELECT id, rate FROM channels ORDER BY rate")->fetchAll();
            foreach($arr as $row) {
                $rates[$row['id']] = $row['rate'];
            }
            arsort($rates);
            $i = 1;
            foreach ($rates as $key => $value) {
                $result[$key] = $i;
                $i++;
            }
            return $result[$id];
        }
        else return 'error';
    }
}





















