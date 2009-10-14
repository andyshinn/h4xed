<?php
class Lastfm
{
    var $xml_rpc_url = 'http://ws.audioscrobbler.com/2.0';
    var $handshake_url = 'http://post.audioscrobbler.com:80/';
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

    function Lastfm()
    {
        $this->client = "tst";
        $this->version = "1.0";
    }

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

        $this->password = $password;
        //        $this->unixtimestamp = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
        $this->unixtimestamp = time();
        $this->arraysongs = array ();

    }

    function handshake()
    {
        // Build the Authentication Token for Standard Authentication and store in $this->auth
        $this->token();

        $request_array = array (
        // Indicates that a handshake is requested. Requests without this parameter set to true will return a human-readable informational message and no handshake will be performed.
        'hs'=>'true',

        // Is the version of the submissions protocol to which the client conforms.
        'p'=>'1.2.1',

        // Is an identifier for the client (See section 1.1).
        'c'=>$this->client,

        // Is the version of the client being used.
        'v'=>$this->version,

        // Is the name of the user.
        'u'=>$this->user,

        // Is a UNIX Timestamp representing the current time at which the request is being performed.
        't'=>$this->unixtimestamp,

        // Is the authentication token (See section 1.2 and section 1.3).
        'a'=>$this->auth,

        // The API key from your Web Services account. Required for Web Services authentication only.
        //				'api_key'=>'',

        // The Web Services session key generated via the authentication protocol. Required for Web Services authentication only.)
        //				'sk'=>''
        );

        $request_string = $this->handshake_url.'?'.http_build_query($request_array);

        $request_result = file_get_contents($request_string);

        $result = explode(chr(10), $request_result);
        //        $result = explode("\n", file_get_contents("http://post.audioscrobbler.com/?hs=true&p=1.2&c=".$this->client."&v=".$this->version."&u=".$this->user."&t=".$this->unixtimestamp."&a=".$this->auth));
        print_r($result);

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
//        $artist = urlencode($artist);
//        $track = urlencode($track);
//        $album = urlencode($album);
        $sec = (int)$sec;
        $track_number = (int)$track_number;

        $request_array = array (
        's'=>$this->SessionId,
        'a'=>$artist,
        't'=>$track,
        'b'=>$album,
        'l'=>$sec,
        'n'=>$track_number,
        'm'=>$mb_trackid);

        $request_string = $this->url_notification.'/?'.http_build_query($request_array);
		
		

        $request_result = $this->httpPost($this->url_notification, http_build_query($request_array));
		
//		print "<br /> <br />";
//		var_dump( $request_result);
//		print "<br /> <br />";

        if (trim($request_result) == 'OK')
        {
            return true;
        }
        else
        {
            return false;
        }

        //        print "Now Playing Result: \n";
        //        print_r($request_result);
    }

    function scrobble()
    {

        $number_songs = count($this->arraysongs);

        $url_parse = parse_url($this->url_submit);

        foreach ($this->arraysongs as $song_number=>$song_array)
        {
            $new_song_array = array ();
			
			$new_song_array['s'] = $this->SessionId;
			
            foreach ($song_array as $key=>$value)
            {
                if (trim($key) == 's')
                {
                    $new_song_array['s'] = $value;
                }
				else
				{
					$new_song_array[$key.'['.$song_number.']'] = $value;
				}
            }
			
//			print_r($new_song_array);
			

            $result = $this->httpPost($this->url_submit, http_build_query($new_song_array));
			

            if (trim($result) != 'OK')
            {
                return false;
            }

            return true;
        }
    }

    function add($artist, $track, $time, $source, $rating, $secs, $album = '', $track_number = '', $mb_trackid = '')
    {
		
		if(is_null($rating))
		{
			$rating = '';
		}

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

    function httpPost($url, $data_array)
    {
        $http_response = '';

        //        $parsed_url = parse_url($url);
        //
        //
        //        print_r($parsed_url);
        //        $data = $parsed_url['query'];
        //        $content_length = strlen($data);
        //        $path = $parsed_url['path'];
        //        $port = ($parsed_url['port']?$parsed_url['port']:80);
        //        $host = $parsed_url['host'];
        //
        //		$url_only = 'http://' . $host . ':' . $port . $path;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_array);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false); // DO NOT RETURN HTTP HEADERS
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // RETURN THE CONTENTS OF THE CALL
        $http_response = curl_exec($ch);


        //        $fp = fsockopen($host, $port);
        //
        //        fwrite($fp, "POST $path HTTP/1.1\r\n");
        //        fwrite($fp, "Host: $host\r\n");
        //        fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
        //        fwrite($fp, "Content-Length: $content_length\r\n");
        //        fwrite($fp, "Connection: close\r\n\r\n");
        //        fwrite($fp, $data);
        //
        //        while (!feof($fp))
        //        {
        //            $http_response .= fgets($fp, 28);
        //        }
        //
        //        fclose($fp);

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
