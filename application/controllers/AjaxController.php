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
            $ajaxContext->addActionContext('twitter', 'html')
                        ->addActionContext('channel', 'html')
                        ->addActionContext('style', 'html')
                        ->addActionContext('player', 'html')
                        ->addActionContext('top20', 'html')
                        ->addActionContext('searchform', 'html')
                        ->addActionContext('howto', 'html')
                          ->initContext();
        }
        else  $this->_helper->redirector('index', 'index');
    }

    public function indexAction()
    {
          //do nothing just redirect
//        $this->_helper->redirector('index', 'index');
    }

    public function channelAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $channelMapper = new Application_Model_ChannelMapper();
            $channel = $channelMapper->find($id, new Application_Model_Channel());
            $this->view->channel = $channel;
        }
    }

    public function styleAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $channelMapper = new Application_Model_ChannelMapper();
            $channels = $channelMapper->fetchStyle($id);
            // due to channels might be an array if 2 or above or an object if 1
            if ( !is_array($channels) ) {
                $channels_arr[] = $channels;
                $this->view->channels = $channels_arr;
            } else {
                $this->view->channels = $channels;
            }
            $styleMapper = new Application_Model_StyleMapper();
            $this->view->style = $styleMapper->find($id, new Application_Model_Style());
        }
    }

    public function top20Action()
    {
        $channelMapper = new Application_Model_ChannelMapper();
        $channels = $channelMapper->fetchTop20();
        $this->view->style = '';
        $this->view->channels = $channels;
    }

    public function twitterAction()
    {
        session_start();
        $config = array(
            'callbackUrl' => 'http://172.30.1.174/index',
            'siteUrl' => 'http://twitter.com/oauth',
            'consumerKey' => 'bo1D2mDiUlFH9zD49Y0w',
            'consumerSecret' => 'qbgOPsx8GroHJLiDNULtw7Wo5GXJnFBeizdWc6vgr3Y'
        );
//        $consumer = new Zend_Oauth_Consumer($config);
//        if ( !isset($_SESSION['TWITTER_ACCESS_TOKEN']) ) {
//            if ( !isset($_SESSION['TWITTER_REQUEST_TOKEN']) ) {
//                $req_token = $consumer->getRequestToken();
//                $_SESSION['TWITTER_REQUEST_TOKEN'] = serialize($req_token);
//                file_put_contents('c:/req_token.txt',  $_SESSION['TWITTER_REQUEST_TOKEN']);
//                $consumer->redirect();
//            }
//            else {//request token exists, try to get access token
//                $token = $consumer->getAccessToken( $_GET, unserialize($_SESSION['TWITTER_REQUEST_TOKEN']) );
//                $_SESSION['TWITTER_ACCESS_TOKEN'] = serialize($token);
//                file_put_contents('c:/acc_token.txt',  $_SESSION['TWITTER_ACCESS_TOKEN']);
//                $_SESSION['TWITTER_REQUEST_TOKEN'] = null;
//            }
//        }
//        $token = unserialize($_SESSION['TWITTER_ACCESS_TOKEN']);
        $token = unserialize(file_get_contents('c:/acc_token.txt'));
        $client = $token->getHttpClient($config);
        $client->setUri('https://api.twitter.com/1.1/friends/ids.json');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setParameterGet('screen_name', 'radio_byfly');
        $response = $client->request();
        $friends = json_decode( $response->getBody() );
        $twitterMapper = new Application_Model_TwitterMapper();
        if ( isset($friends->ids) ) {//twitter api success
            $rand_keys = array_rand( $friends->ids, 4 );
            $rand_array = array();
            foreach ($rand_keys as $key) {
                $rand_array[] = $friends->ids[$key];
            }
            $ids = implode(',', $rand_array);
            //get info about friends
            $client->setUri('https://api.twitter.com/1.1/users/lookup.json');
            $client->setMethod(Zend_Http_Client::GET);
            $client->setParameterGet('user_id', $ids);
            $response = $client->request();
            $users_info = json_decode( $response->getBody() );
            //skip 1st el because it is radio_byfly (self)
            $users = array();
            for($i = 1; $i < count($users_info); $i++) {
                $user = array('screen' => $users_info[$i]->screen_name,
                'img' => $users_info[$i]->profile_image_url_https);
                $twitter = new Application_Model_Twitter($user);
                $twitterMapper->save($twitter);
                $users[] = $twitter;
            }
            //insert ifo into twitter db
            $this->view->boot_users = $users;
        }
        else {//exceeded request limit
            //try to get info from db
            $this->view->boot_users = $twitterMapper->fetchRandom();
        }
    }

    public function playerAction()
    {
        $id = $this->getRequest()->getParam('id'); //channel id
        if ($id) {
            if ($id == 'auto') {// autoloading, no channel selected, empty player
                $this->view->display = false;
                $this->view->channel = 'empty';
                $this->view->bitrate = 'empty';
                $this->view->format = 'mp3';
                $this->view->byfly = 'empty';
            }
            else {
                $channelMapper = new Application_Model_ChannelMapper();
                $channel = $channelMapper->find( $id, new Application_Model_Channel() );
                $this->view->display = true;
                $this->view->channel = $channel->getChannel();
                $this->view->bitrate = $channel->getStreamObj()->getBitrate();
                $format = $channel->getStreamObj()->getFormat();
                if ($format == 'ogg') {
                    $this->view->format = 'oga';
                } else {
                    $this->view->format = $format;
                }
                $this->view->byfly = $channel->getStreamObj()->getByfly();
            }
        }
        else {
            $this->view->display = false;
            $this->view->channel = 'empty';
            $this->view->bitrate = 'empty';
            $this->view->format = 'mp3';
            $this->view->byfly = 'empty';
        }

    }

    public function searchformAction()
    {
        //get all available bitrates
        $streamMapper = new Application_Model_StreamMapper();
        $streams = $streamMapper->fetchDistinct();
        $this->view->streams= $streams;
        //getAll available styles
        $styleMapper = new Application_Model_StyleMapper();
        $styles = $styleMapper->fetchAllAsc();
        $this->view->styles = $styles;
    }

    public function searchAction()
    {
        if ( $this->request->isPost() ) {
            //user data may be channel(text query) | styles | bitrate

            $query_string = $this->request->getParam('channel');
            $bitrate = $this->request->getParam('bitrate');
            $channelMapper = new Application_Model_ChannelMapper();
            $styleMapper = new Application_Model_StyleMapper();
            $keyMapper = new Application_Model_KeyMapper();
            $channel_ids = array();
            $channels = array();
            $styles = $styleMapper->fetchAll();

            foreach ( $styles as $style ) {//get channels id, match with this style
                if ( $this->request->getParam( 'style_'.$style->getId() ) ) {
                   foreach ( $keyMapper->fetchChannelsPerStyle($style->getId()) as $entry) {
                       $channel_ids[] = $entry;
                   }
                }
            }

            if ( !empty($channel_ids) ) {//there were some checkboxes in user query but there's still string query
                if ( empty($query_string) ){//string query is empty, just channels from checkboxes
                    foreach ($channel_ids as $channel_id) {
                        $channels[] = $channelMapper->find( $channel_id, new Application_Model_Channel() );
                    }
                }
                else {// nedd to process string query
                    $channels = $channelMapper->findStringInChannels($channel_ids, $query_string);
                }
            }
            else {// there were no style checkboxes in user query but there's still string query
                $channels = $channelMapper->findString($query_string);
            }

            //now channels contains neccessary info or empty
            $bitrate_channels = array();
            if ( !empty($channels) ) {
                //try to match requested bitrate
                foreach ($channels as $channel) {
                    if ( (gettype($channel) === 'object') ) {
                        if ( $channel->getStreamObj()->getBitrate() == $bitrate ) {
                            $bitrate_channels[] = $channel;
                        }
                    }
                }
            }
            if ( !empty($bitrate_channels) ) {
                $channels = $bitrate_channels;
            }

            //exclude empty non-objects from array
            $ready_channels = array();
            foreach ($channels as $channel) {
                if ( (gettype($channel) === 'object') ) {
                    $ready_channels[] = $channel;
                }
            }
            $this->view->channels = $ready_channels;
        }
    }

    public function playlistAction()
    {

    }

    public function howtoAction()
    {

    }

}

