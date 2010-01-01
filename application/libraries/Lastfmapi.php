<?php
class Lastfm
{
    var $xml_rpc_url = 'http://ws.audioscrobbler.com/2.0';
    var $api_key = '055046640543c3766b09f8c25d34d8f6';
    var $user;
    var $auth;
    var $client;
    var $version;
    var $SessionId;
    var $password;
    var $url_notification;
    var $url_submit;
    var $unixtimestamp;
    var $arraysongs;

    function artist_getInfo($artist)
    {
        $ci = & get_instance();
        $ci->load->library('xmlrpc');

        $ci->xmlrpc->server($this->xml_rpc_url, 80);
        $ci->xmlrpc->method('artist.getInfo');

        $request = array (
        array (
        array (
        'api_key'=>$this->api_key,
        'artist'=>str_replace('_', ' ', $artist)
        ),
        'struct'
        )
        );

        //        $request = array ('api_key'=>$this->api_key);
        $ci->xmlrpc->request($request);

        if (!$ci->xmlrpc->send_request())
        {
            echo $ci->xmlrpc->display_error();
        }
        else
        {
            echo '<pre>';
            print_r($ci->xmlrpc->display_response());
            echo '</pre>';
        }
    }

    function login($user, $password)
    {
        $this->user = $user;
        $this->client = "tst";
        $this->version = "1.0";
        $this->password = $password;
        //        $this->unixtimestamp = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
        $this->unixtimestamp = time();
        $this->arraysongs = array ();

    }

    function handshake()
    {
        $this->token();
        $result = explode("\n", file_get_contents("http://post.audioscrobbler.com/?hs=true&p=1.2&c=".$this->client."&v=".$this->version."&u=".$this->user."&t=".$this->unixtimestamp."&a=".$this->auth));
        if ($result[0] == 'OK')
        {
            $this->SessionId = $result[1];
            $this->url_notification = $result[2];
            $this->url_submit = $result[3];
            return true;
        }
        else
        {
            return false;
        }

    }

    function token()
    {
        $this->auth = md5(md5($this->password).$this->unixtimestamp);
    }

    function nowplaying($artist, $track, $album, $sec, $track_number, $mb_trackid = '')
    {
        $artist = urlencode($artist);
        $track = urlencode($track);
        $album = urlencode($album);
        $sec = (int)$sec;
        $track_number = (int)$track_number;


        $data = "s=".$this->SessionId."&a=".$artist."&t=".$track."&b=".$album."&l=".$sec."&n=".$track_number."&m=".$mb_trackid;

        $url_parse = parse_url($this->url_notification);

        $result = explode("\n", $this->HttpPost($url_parse['host'], $url_parse['path'], $data, $url_parse['port']));

        if ($result[count($result)-2] != 'OK')
        {
            return false;
        }
        return true;
    }

    function scrobble()
    {

        $number_songs = count($this->arraysongs);


        $url_parse = parse_url($this->url_submit);

        for ($i = 0; $i < $number_songs; $i++)
        {
            $data = "s=".$this->SessionId."&a[$i]=".$this->arraysongs[$i]['a']."&t[$i]=".$this->arraysongs[$i]['t']
            ."&i[$i]=".$this->arraysongs[$i]['i']."&o[$i]=".$this->arraysongs[$i]['o']."&r[$i]=".$this->arraysongs[$i]['r']
            ."&l[$i]=".$this->arraysongs[$i]['l']."&b[$i]=".$this->arraysongs[$i]['b']."&n[$i]=".$this->arraysongs[$i]['n']
            ."&m[$i]=".$this->arraysongs[$i]['m'];
            $result = explode("\n", $this->HttpPost($url_parse['host'], $url_parse['path'], $data, $url_parse['port']));

            if ($result[count($result)-2] != 'OK')
            {
                return false;
            }
            else
            {
                return true;
            }
        }

    }

    function add($artist, $track, $time, $source, $rating, $secs, $album = '', $track_number = '', $mb_trackid = '')
    {


        $this->arraysongs[] = array (
        'a'=>$artist,
        't'=>$track,
        //        'i'=>$this->GetTime_SubmitFormat($time[0], $time[1], $time[2], $time[3], $time[4], $time[5]),
        'i'=>$time,
        'o'=>$source,
        'r'=>$rating,
        'l'=>$secs,
        'b'=>$album,
        'n'=>$track_number,
        'm'=>$mb_trackid
        );
    }

    function HttpPost($host, $path, $data, $port = 80)
    {
        $http_response = '';
        $content_length = strlen($data);
        $fp = fsockopen($host, $port);
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
        fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-Length: $content_length\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);
        while (!feof($fp))$http_response .= fgets($fp, 28);
        fclose($fp);
        return $http_response;
    }

    function GetTime_SubmitFormat($hour, $minute, $second, $month, $day, $year)
    {
        date_default_timezone_set('UTC');
        //return date('H:i:s, F jS Y e',mktime($hour,$minute,$second,$month,$day,$year));
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    function removeSong_Queque($index)
    {
        if ( isset ($this->arraysongs[$index]))
        {
            array_splice($this->arraysongs, $index, 1);
        }
    }



}

/*Test*/
//$obj =  new SendMusicLastFm("username","password");
//$obj->Handshake();

/*if($obj->SendNowPlayingNotification("Dimmu Borgir","Puritania","Puritanical Euphoric Misanthropia","305","12")){ 
 print "Listing Now OK";
 }
 $time[]=21 ;//Hours
 $time[]=15; //Minutes
 $time[]=00; //Seconds
 $time[]=10; //Month
 $time[]=4;//Day
 $time[]=2007;//Year
 $obj->addSong("Dimmu Borgir","Burn in Hell",$time,'U','L',"305","Puritanical Euphoric Misanthropia","12");
 $obj->SubmitSongs();
 */
?>