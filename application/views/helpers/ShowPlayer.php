<?php
class Zend_View_Helper_ShowPlayer extends Zend_View_Helper_Abstract
{
    private $db;

    public function __construct()
    {
        $this->db = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getPluginResource("db")->getDbAdapter();
    }

    public function showPlayer(array $params)
    {
//        $params = array(
//            'host' => 'http://shoutcast.byfly.by',
//            'port' => '88',
//            'channel' => '181chilled',
//            'format' => 'mp3',
//            'bitrate' => '96',
//            'title' => '181chilled',
//            'byfly_stream' => '181chilled'
//        );

        if ($params['format'] == 'aac+') {
            $params['format'] = 'm4a';
        } elseif ($params['format'] == 'ogg') {
            $params['format'] = 'oga';
        }

        $html = '
            
<script type="text/javascript" src="/jplayer/jquery.jplayer.min.js"></script>
<link type="text/css" rel="stylesheet" href="/jplayer/blue/jplayer.blue.monday.css">
<div class="wrapper1" style="height: 60px; float: left; display: block; margin-right:45px;">
<div id="jquery_jplayer_1" class="jp-jplayer"></div><div id="jp_container_1" class="jp-audio">
    <div class="jp-type-single">  
         <div class="jp-gui jp-interface">  
            <ul class="jp-controls">  
                 <li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>  
                 <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>  
                 <li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>  
                 <li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>  
                <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>  
                  <li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>  
                 </ul>  
             <div class="jp-volume-bar">  
                 <div class="jp-volume-bar-value"></div>  
                 </div>  
             </div>  
         <div class="jp-title">  
             <ul>  
                 <li><div id="scroll"><marquee scrolldelay="500" scrollamount="10" vspace="0">'.$params['title'].' :: '.$params['bitrate'].' kbit/s </marquee></div></li>
                  </ul>  
             </div>  
         <div class="jp-no-solution">  
                <span>Update Required</span>  
             To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.  
              </div>  
         </div>  
      </div> 
</div>    
<script type="text/javascript">
    //vars
    var player_id = "jquery_jplayer_1";
//    var path = "'.$params['host'].':'.$params['port'].(($params['channel'])?('/'):('')).$params['channel'].'";
    var path = "http://shoutcast.byfly.by:88/'.$params['byfly_stream'].'";
    var type = "'.$params['format'].'"; //or ogg
    $(document).ready(function () {
       // $(".wrapper1").draggable();

        function scrollText(e) {
            $(e).val()
        }

        $("#" + player_id).jPlayer({
            ready:function () {
                    $(this).jPlayer("setMedia", {
                    '.$params['format'].':path
                });
            },
            swfPath: "http://www.jplayer.org/latest/js/Jplayer.swf",
            supplied:"'.$params['format'].'"
        });
    });
</script>';
        return $html;
    }
}