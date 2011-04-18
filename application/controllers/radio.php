<?php
class Radio extends CI_Controller
{
    private $view_data = array();
    private $history_data = array();
    private $limit_news = 5;
    private $char_limit_history = 26;
    private $char_limit_current = 55;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('stream_model', 'stream');
        $this->load->model('playlist_model', 'playlist');
        $this->load->model('news_model', 'news');
        $this->load->model('shoutbox_model', 'shoutbox');
        $this->load->helper(array('url', 'date', 'text', 'cookie', 'when'));
        $this->load->library('parser');
        $this->template->add_js('assets/js/jquery-1.5.2.min.js');
        $this->template->add_css('assets/css/style.css');
    }

    function index()
    {
        redirect('radio/news');
    }

    function contact()
    {
        $this->template->write_view('main', 'public/contact');
        $this->template->write_view('head', 'include/head');
        $this->template->render();
    }

    function news()
    {
        $this->template->add_css('assets/css/shoutbox.css');
        $this->template->add_js('assets/js/jquery.emoticon.js');
        $this->template->add_js('assets/js/emoticons.js');
        $this->template->add_js('assets/js/shoutbox.js');
        $this->template->add_js('assets/js/news.js');
        // Now playing song information
        $query_song_current = $this->playlist->current_song();
        $song_current = $query_song_current->row();
        $artist_query = $this->playlist->artist($song_current->artist);
        $artist = $artist_query->row();
        $this->view_data['song_current'] = $song_current->artist . " - " . $song_current->title;
        $this->view_data['song_current_limited'] = character_limiter(ucwords($this->view_data['song_current']), $this->char_limit_current);
        $this->view_data['song_count'] = $artist->titlecount;
        $this->view_data['album_count'] = $artist->albumcount;
        $this->view_data['song_title'] = $song_current->title;
        $this->view_data['song_artist'] = $artist->artist;
        foreach($song_current as $key => $value)
        {
            $this->view_data[$key] = $value;
        }
        $this->template->parse_view('main', 'public/currently_playing', $this->view_data);
        $this->view_data['graph_hourly_url'] = 'http://radio.h4xed.us/radio/listeners/hourly.png';
        $this->view_data['song_date_played'] = mdate('%M %j%S, %Y at %g:%i %A', human_to_unix($song_current->date_played));
        $this->view_data['song_count_played'] = $song_current->count_played;
        // Shoutbox initial display and form
        $shouts = $this->shoutbox->messages();
        $this->view_data['shouts'] = $shouts->result();
        $this->view_data['name'] = (get_cookie('h4xed_shoutname', TRUE)) ? get_cookie('h4xed_shoutname', TRUE) : 'Your name';
        // Song history
        $query_history = $this->playlist->history('5');
        $song_history = $query_history->result();
        foreach($song_history as $song)
        {
            $song_string = $song->artist . " - " . $song->title;
            $this->history_data[] = array('song' => character_limiter(ucwords($song_string), $this->char_limit_history), 'time' => $song->date_played);
        }
        $this->view_data['song_history'] = $this->history_data;
        $this->template->parse_view('right', 'public/right', $this->view_data);
        //		$this->template->parse_view('right', 'public/shoutbox/_shoutbox_list', array('name' => $name));
        // News information
        $query_news = $this->news->listing($this->limit_news);
        $news_item = $query_news->result_array();
        $news_item_array = $this->_array_change_key_name('title', 'news_title', $news_item);
        $this->view_data['news_item'] = $news_item_array;
        $this->template->parse_view('main', 'public/news', $this->view_data);
        $this->template->write_view('head', 'include/head');
        $this->template->render();
    }

    function listeners()
    {
        $hours = 10;
        $last_hours = $this->stream->last_hours($hours);
        $last_hours_obj = $last_hours->result();
        foreach($last_hours_obj as $row)
        {
            $listener[] = $row->listeners;
            $hour[] = $row->hour;
        }
        $ceiling = 5;
        $cht = "lc";
        $chd = implode(',', $listener);
        $chxl = implode('|', $hour);
        $max = max($listener);
        $xmax = $ceiling * ceil($max / $ceiling);
        $xchg = 100 / ($hours - 1);
        $ychg = 100 / $xmax;
        $chg = "$xchg,$ychg";
        $chs = "600x200";
        $url = "http://chart.apis.google.com/chart?cht=$cht&chdl=Listeners&chtt=Listener+Count&chg=$chg&chd=t:$chd&chxt=x,y,x,y&chxl=0:|$chxl|1:|0|$xmax|2:||Hour+(PST)||3:||Listeners|&chds=0,$xmax&chs=$chs";
        $imagefp = fopen($url, 'rb');
        $this->output->set_header("Content-Type: image/png");
        $this->output->set_header("Content-Length: " . $this->_remotefsize($url));
        readfile($url);
    }

    function _remotefsize($url)
    {
        $sch = parse_url($url);
        if (($sch['scheme'] != "http") && ($sch != "https"))
        {
            return false;
        }
        if (($sch == "http") || ($sch == "https"))
        {
            $headers = get_headers($url, 1);
            if ((! array_key_exists("Content-Length", $headers)))
            {
                return false;
            }
            return $headers["Content-Length"];
        }
    }

    function _array_change_key_name($orig, $new, &$array)
    {
        foreach($array as $k => $v)
            $return[$k === $orig ? $new : $k] = (is_array($v) ? $this->_array_change_key_name($orig, $new, $v) : $v);
        return $return;
    }

    function tunein($stream_id = null, $list_type = null)
    {
        $file_url = null;
        $mime_type = null;
        if ($stream_id && $list_type)
        {
            switch($list_type)
            {
                case 'asx' :
                    $file_name = $stream_id . '.' . $list_type;
                    $mime_type = 'video/x-ms-asf';
                break;
                case 'm3u' :
                    $file_name = $stream_id . '.' . $list_type;
                    $mime_type = 'audio/x-mpegurl';
                break;
                case 'pls' :
                    $file_name = $stream_id . '.' . $list_type;
                    $mime_type = 'audio/x-scpls';
                default :
                    $file_name = $stream_id . '.' . $list_type;
                    $mime_type = 'video/x-ms-asf';
            }
            $file_url = base_url() . 'assets/links/' . $file_name;
            $this->_output_file($file_url, $file_name, $mime_type);
             //			header('Location: ' . base_url() . 'assets/links/' . $stream_id . '.' . $list_type);
        //			exit();
        }
        else
        {
            $this->template->write_view('main', 'public/tunein');
            $this->template->write_view('head', 'include/head');
            $this->template->render();
        }
    }

    function _output_file($file, $name, $mime_type = '')
    {
        /*
 This function takes a path to a file to output ($file), 
 the filename that the browser will see ($name) and 
 the MIME type of the file ($mime_type, optional).
 
 If you want to do something on download abort/finish,
 register_shutdown_function('function_name');
 */
        //		if (! is_readable($file)) die('File not found or inaccessible!');
        //		$size = filesize($file);
        $name = rawurldecode($name);
        /* Figure out the MIME type (if not specified) */
        $known_mime_types = array("pdf" => "application/pdf", "txt" => "text/plain", "html" => "text/html", "htm" => "text/html", "exe" => "application/octet-stream", "zip" => "application/zip", "doc" => "application/msword", "xls" => "application/vnd.ms-excel", "ppt" => "application/vnd.ms-powerpoint", "gif" => "image/gif", "png" => "image/png", "jpeg" => "image/jpg", "jpg" => "image/jpg", "php" => "text/plain");
        if ($mime_type == '')
        {
            $file_extension = strtolower(substr(strrchr($file, "."), 1));
            if (array_key_exists($file_extension, $known_mime_types))
            {
                $mime_type = $known_mime_types[$file_extension];
            }
            else
            {
                $mime_type = "application/force-download";
            }
            ;
        }
        ;
        @ob_end_clean(); //turn off output buffering to decrease cpu usage
        // required for IE, otherwise Content-Disposition may be ignored
        if (ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header("Content-Transfer-Encoding: binary");
        header('Accept-Ranges: bytes');
        /* The three lines below basically make the 
    download non-cacheable */
        header("Cache-control: private");
        header('Pragma: private');
        //		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        // multipart-download and download resuming support
        //		if (isset($_SERVER['HTTP_RANGE'])) {
        //			list($a, $range) = explode("=", $_SERVER['HTTP_RANGE'], 2);
        //			list($range) = explode(",", $range, 2);
        //			list($range, $range_end) = explode("-", $range);
        //			$range = intval($range);
        //			if (! $range_end) {
        //				$range_end = $size - 1;
        //			}
        //			else {
        //				$range_end = intval($range_end);
        //			}
        //			
        //			$new_length = $range_end - $range + 1;
        //			header("HTTP/1.1 206 Partial Content");
        //			header("Content-Length: $new_length");
        //			header("Content-Range: bytes $range-$range_end/$size");
        //		}
        //		else {
        //			$new_length = $size;
        //			header("Content-Length: " . $size);
        //		}
        /* output the file itself */
        $chunksize = 1 * (1024 * 1024); //you may want to change this
        $bytes_send = 0;
        if ($file = fopen($file, 'r'))
        {
            if (isset($_SERVER['HTTP_RANGE'])) fseek($file, $range);
            while(! feof($file) && (! connection_aborted()))
            {
                $buffer = fread($file, $chunksize);
                print($buffer); //echo($buffer); // is also possible
                flush();
                $bytes_send += strlen($buffer);
            }
            fclose($file);
        }
        else
            die('Error - can not open file.');
        die();
    }
}
?>
