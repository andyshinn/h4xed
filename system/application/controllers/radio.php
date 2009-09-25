<?php

class Radio extends Controller
{
    var $view_data = array ();
    var $history_data = array ();
    var $limit_news = 5;
    var $char_limit_history = 26;
    var $char_limit_current = 55;
	var $hlinks = 'include/hlinks';

    function Radio()
    {
        parent::Controller();
        $this->load->model('stream_model', 'stream');
        $this->load->model('playlist_model', 'playlist');
        $this->load->model('news_model', 'news');
        $this->load->dbutil();
        $this->load->helper('url');
        $this->load->helper('date');
        $this->load->helper('text');
        $this->load->library( array ('parser', 'template'));
    }

    function index()
    {
        redirect('radio/news');
        exit ();
        $currentSongObj = $this->playlist->current_song();
        $currentSong = $currentSongObj->row();

        $historyObj = $this->playlist->history();
        $history = $historyObj->result();

        $data['graph_url'] = 'http://radio.h4xed.us/radio/listeners';
        $data['current'] = $currentSong;
        $data['history'] = $history;

        $this->load->view('radio_home', $data);
    }

    function tunein($stream_port = null)
    {
        if ($stream_port)
        {
            switch($stream_port)
            {
                case 7080:
                    header('Location: http://sc-01.h4xed.us:7080/listen.pls');
                    break;
                case 7082:
                    header('Location: http://sc-01.h4xed.us:7082/listen.pls');
                    break;
                exit ();
            }
        }
        else
        {
            $this->template->write_view('main', 'public/tunein');
            $this->template->write_view('hlinks', $this->hlinks);
            $this->template->render();
        }
    }

    function contact()
    {
        $this->template->write_view('main', 'public/contact');
        $this->template->write_view('hlinks', $this->hlinks);
        $this->template->render();
    }

    function news()
    {
        $this->template->set_master_template('template_right');

        $query_song_current = $this->playlist->current_song();
        $song_current = $query_song_current->row();
		
		$artist_query = $this->playlist->artist($song_current->artist);
		$artist = $artist_query->row();

        $this->view_data['song_current'] = $song_current->artist." - ".$song_current->title;
        $this->view_data['song_current_limited'] = character_limiter(ucwords($this->view_data['song_current']), $this->char_limit_current);
        $this->view_data['song_count'] = $artist->titlecount;
		$this->view_data['album_count'] = $artist->albumcount;
        $this->view_data['song_title'] = $song_current->title;
        $this->view_data['song_artist'] = $artist->artist;

        foreach ($song_current as $key=>$value)
        {
            $this->view_data[$key] = $value;
        }

        $this->template->parse_view('main', 'public/currently_playing', $this->view_data);

        $this->view_data['graph_hourly_url'] = 'http://radio.h4xed.us/radio/listeners/hourly.png';

        $this->view_data['song_date_played'] = mdate('%M %j%S, %Y at %g:%i %A', human_to_unix($song_current->date_played));
        $this->view_data['song_count_played'] = $song_current->count_played;

        $query_history = $this->playlist->history('5');
        $song_history = $query_history->result();

        foreach ($song_history as $song)
        {
            $song_string = $song->artist." - ".$song->title;
            $this->history_data[] = array ('song'=>character_limiter(ucwords($song_string), $this->char_limit_history), 'time'=>$song->date_played);
        }

        $this->view_data['song_history'] = $this->history_data;
        $this->template->parse_view('right', 'public/right', $this->view_data);

        $query_news = $this->news->listing($this->limit_news);
        $news_item = $query_news->result_array();
		
		$news_item_array = $this->_array_change_key_name('title', 'news_title', $news_item);

        $this->view_data['news_item'] = $news_item_array;

        $this->template->parse_view('main', 'public/news', $this->view_data);

        $this->template->write_view('hlinks', $this->hlinks);
        $this->template->render();
    }

    function listeners()
    {

        $hours = 10;

        $last_hours = $this->stream->last_hours($hours);
        $last_hours_obj = $last_hours->result();

        foreach ($last_hours_obj as $row)
        {
            $listener[] = $row->listeners;
            $hour[] = $row->hour;
        }

        $ceiling = 5;
        $cht = "lc";
        $chd = implode(',', $listener);
        $chxl = implode('|', $hour);
        $max = max($listener);
        $xmax = $ceiling*ceil($max/$ceiling);
        $xchg = 100/($hours-1);
        $ychg = 100/$xmax;
        $chg = "$xchg,$ychg";
        $chs = "600x200";

        $url = "http://chart.apis.google.com/chart?cht=$cht&chdl=Listeners&chtt=Listener+Count&chg=$chg&chd=t:$chd&chxt=x,y,x,y&chxl=0:|$chxl|1:|0|$xmax|2:||Hour+(PST)||3:||Listeners|&chds=0,$xmax&chs=$chs";

        $imagefp = fopen($url, 'rb');

        $this->output->set_header("Content-Type: image/png");
        $this->output->set_header("Content-Length: ".$this->_remotefsize($url));
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
            if ((!array_key_exists("Content-Length", $headers)))
            {
                return false;
            }
            return $headers["Content-Length"];
        }

    }

    function _array_change_key_name($orig, $new, & $array)
    {
        foreach ($array as $k=>$v)
        $return[$k === $orig?$new:$k] = (is_array($v)?$this->_array_change_key_name($orig, $new, $v):$v);
        return $return;
    }

}

?>
