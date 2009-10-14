<?php

class Playlist extends Controller
{
    var $view_data = array ();
    var $song_id_link;
    var $song_title_link;
    var $per_page = 30;
    var $num_links = 4;
	var $time_format = '%d:%02.0d';

    function Playlist()
    {
        parent::Controller();
        $this->load->library( array ('table', 'template', 'sam', 'form_validation', 'xml', 'pagination'));
        $this->load->model('playlist_model', 'playlist');
        $this->load->model('stream_model', 'stream');
        $this->load->helper( array ('url', 'form', 'string'));
    }

    function index()
    {
        $this->artists();
    }

    function search($pattern = null)
    {
        if (!is_null($pattern))
        {
            $query_artist = $this->playlist->search_artist($pattern);
            $artist_result = $query_artist->result_array();

            $query_album = $this->playlist->search_album($pattern);
            $album_result = $query_album->result_array();

            $query_title = $this->playlist->search_title($pattern);
            $title_result = $query_title->result_array();



            if ($query_artist->num_rows() > 0)
            {
                $this->table->set_caption('Artists');
                $this->table->set_heading( array ('Artist', 'Songs', 'Albums'));
                print $this->table->generate($artist_result)."<br />";
                $this->table->clear();
            }
            else
            {
                print "No artists matching ".$pattern."<br />";
            }



            $this->table->set_caption('Albums');
            $this->table->set_heading( array ('Artist', 'Songs', 'Album', 'Year', 'Genre'));

            if (count($album_result) > 0)
            {
                print $this->table->generate($album_result);
            }
            else
            {
                print "No albums matching ".$pattern."<br />";
            }

            $this->table->clear();

            $this->table->set_caption('Songs');
            $this->table->set_heading( array ('Artist', 'Title', 'Album', 'Year', 'Genre'));

            if (count($title_result) > 0)
            {
                print $this->table->generate($title_result);
            }
            else
            {
                print "No song titles matching ".$pattern."<br />";
            }
        }
    }

    function requests($key = null)
    {
        if ($key == 'hi')
        {
            $query_requests = $this->playlist->requests();
            $requests = $query_requests->result_array();
            $this->table->set_heading( array ('Request ID', 'Song ID', 'Client IP', 'Code', 'Status', 'Artist', 'Title', 'Album', 'Date'));
            echo $this->table->generate($requests);
        }
    }

    function request($song_id = NULL)
    {
        $this->template->set_master_template('template_lightbox');

        if ($song_id)
        {
            $song_query = $this->playlist->get($song_id);

            foreach ($song_query->row_array() as $key=>$value)
            {
                $this->view_data[$key] = $value;
            }

            $sam_request = $this->sam->request($song_id);
            $sam_request_array = $this->xml->parse($sam_request);

            $req_code = $sam_request_array['REQUEST']['status']['code'];
            $this->view_data['req_message'] = $sam_request_array['REQUEST']['status']['message'];
            $this->view_data['req_id'] = $sam_request_array['REQUEST']['status']['requestID'];
            if ($req_code == '200')
            {
                $this->view_data['req_status'] = '<span class="green">Request Added</span> - Song will be played after next 2 songs';
            }
            else
            {
                $this->view_data['req_status'] = '<span class="red">Request Failed</span> - See message for failed reason';
            }

            $this->template->parse_view('main', 'public/song_request', $this->view_data);
        }
        else
        {
            $this->template->parse_view('main', 'public/error', $this->view_data);
        }

        $this->template->render();
    }

    function artists($letter = 'all', $limit_offset = 0)
    {
        $this->load->helper('inflector');

        if ($letter == 'all')
        {
            $letter = null;
        }

        $artist_count_query = $this->playlist->artists($letter);
        $artist_count = $artist_count_query->num_rows();

        $artist_query = $this->playlist->artists($letter, $this->per_page, $limit_offset);
        $artist = $artist_query->result_array();

        $artist_url = array ();

        foreach ($artist as $key=>$value)
        {
            $artist_name = array ();
            $artist_name = explode(' ', $value['artist']);
            $artist_new_name = '';

            foreach ($artist_name as $artist_word)
            {
                $artist_new_name .= $artist_word.' ';
            }

            $artist_url[$key] = array_merge($value, array ('artist_url'=>reduce_multiples(url_title(strtolower(trim($artist_new_name))))), '-');
        }

        $config['base_url'] = site_url("playlist/artists/".( isset ($letter)?$letter."/":'all'));
        $config['total_rows'] = $artist_count;
        $config['per_page'] = $this->per_page;
        $config['uri_segment'] = 4;
        $config['num_links'] = $this->num_links;

        $this->pagination->initialize($config);

        $this->view_data['artist'] = $artist_url;
        $this->view_data['letter_links'] = $this->_letters_anchor('playlist/artists');
        $this->view_data['page_links'] = $this->pagination->create_links();
        $this->view_data['artist_count'] = $artist_count;

        $this->template->write_view('hlinks', 'include/hlinks');
        $this->template->write_view('right', 'include/right');
        $this->template->parse_view('main', 'public/artists', $this->view_data);
        $this->template->render();
    }

    function artist($artist_safe = null, $album_safe = null)
    {
        if ($artist_safe)
        {
            $artist_query = $this->playlist->artist($artist_safe);
            $artist = $artist_query->row();

            $albums_query = $this->playlist->albums($artist_safe);
            $albums = $albums_query->result_array();

            foreach ($artist as $key=>$value)
            {
                $this->view_data[$key] = $value;
            }

            $albums_urls = $this->_build_array($albums);
            $this->view_data['albums'] = $albums_urls;
            $this->view_data['artist_url'] = $this->_safe_search($artist->artist);

            $this->template->parse_view('main', 'public/artist', $this->view_data);

            $album_tracks = array ();

            foreach ($albums_urls as $album)
            {
                $tracks_query = $this->playlist->tracks($this->_safe_search($album['album_artist']), $this->_safe_search($album['album_name']));
                $tracks = $tracks_query->result_array();


                $tracks_time = array ();

                foreach ($tracks as $track)
                {
                	$seconds = $track['track_duration'] / 1000;
                    $minutes = intval($seconds/ 60);
                    $secondsRemaining = ($seconds % 60);
					$tracks_time[]['track_time2'] = sprintf($this->time_format, $minutes, $secondsRemaining);
                }
				
				$tracks_new = $this->_my_array_merge($tracks, $tracks_time);

                $this->template->parse_view('main', 'public/tracks', array ('artist_name' => $artist->artist, 'tracks'=>$tracks_new, 'album_name'=>$album['album_name'], 'album_url'=>$album['album_url']));
            }

            //			print "<pre>";
            //			print_r($albums_urls);
            //			print "</pre>";

            $this->template->write_view('hlinks', 'include/hlinks');
            $this->template->write_view('right', 'include/right');
            //            $this->template->parse_view('main', 'public/artist', $this->view_data);
            $this->template->render();


        }
    }

    function _build_array($data, $prefix = 'album')
    {
        $array = array ();
        foreach ($data as $key=>$value)
        {
            $array[$key] = array ($prefix.'_url'=>$this->_safe_search($value[$prefix.'_name']));
        }

        return $this->_my_array_merge($data, $array);
    }


    function _safe_search($string = null)
    {
        if ($string)
        {
            return (string)reduce_multiples(url_title(strtolower(trim($string))));
        }
    }

    function _my_array_merge($arr, $ins)
    {
        if (is_array($arr))
        {
            if (is_array($ins)) foreach ($ins as $k=>$v)
            {
                if ( isset ($arr[$k]) && is_array($v) && is_array($arr[$k]))
                {
                    $arr[$k] = $this->_my_array_merge($arr[$k], $v);
                }
                else
                {
                    // This is the new loop :)
                    while ( isset ($arr[$k]))
                    $k++;
                    $arr[$k] = $v;
                }
            }
        }
        elseif (!is_array($arr) && (strlen($arr) == 0 || $arr == 0))
        {
            $arr = $ins;
        }
        return ($arr);
    }

    function show($letter = 'all', $limit_offset = 0)
    {
    	$this->template->add_js('assets/js/prototype.js');
		$this->template->add_js('assets/js/lightbox/lightbox.js');
		$this->template->add_css('assets/css/lightbox.css');
		
        if ($letter == 'all')
        {
            $letter = NULL;
        }

        $per_page = '30';

        $playlist_count_query = $this->playlist->get_letter($letter);
        $playlist_count = $playlist_count_query->num_rows();
        $playlist_query = $this->playlist->get_letter($letter, $per_page, $limit_offset);

        $config['base_url'] = site_url("playlist/show/".( isset ($letter)?$letter."/":'all'));
        $config['total_rows'] = $playlist_count;
        $config['per_page'] = $per_page;
        $config['uri_segment'] = 4;
        $config['num_links'] = $this->num_links;

        $this->pagination->initialize($config);

        $playlist = $playlist_query->result();
        $this->view_data['playlist'] = $playlist;
        $this->view_data['letter_links'] = $this->_letters_anchor('playlist/show');
        $this->view_data['page_links'] = $this->pagination->create_links();
        $this->view_data['playlist_count'] = $playlist_count;
        $popup_attributes = array (
        'width'=>'500',
        'height'=>'300',
        'scrollbars'=>'no',
        'status'=>'no',
        'resizable'=>'yes',
        'screenx'=>'100',
        'screeny'=>'100'
        );

        $popup_links = array ();

        foreach ($playlist as $song)
        {
            $popup_links[] = anchor_popup('playlist/song/'.$song->song_id, $song->song_id);
        }

        $this->view_data['site_url'] = site_url();
        $this->view_data['popup_links'] = $popup_links;
        $this->view_data['popup_attributes'] = $popup_attributes;



        $this->template->write_view('hlinks', 'include/hlinks');
        $this->template->write_view('right', 'include/right');
        $this->template->parse_view('main', 'public/playlist', $this->view_data);
        $this->template->render();
    }



    function song($song_id = NULL)
    {
        $this->template->set_master_template('template_lightbox');
        $this->template->write_view('hlinks', 'include/hlinks');

        if ($song_id)
        {
            $song_query = $this->playlist->get($song_id);

            foreach ($song_query->row_array() as $key=>$value)
            {
                $this->view_data[$key] = $value;
            }

            $this->template->parse_view('main', 'public/song_info', $this->view_data);
        }
        else
        {
            $this->template->write_view('main', 'public/error');
        }

        $this->template->render();
    }

    function all($sort = 'ID', $order = 'desc')
    {
        $playlistObj = $this->playlist->listing(null, null, $sort, $order);
        $playlist = $playlistObj->result_array();

        $songlist = $this->db->list_fields('songlist');

        $this->table->set_heading('ID', 'duration', 'artist', 'title', 'album', 'trackno', 'genre', 'count_played', 'count_requested', 'count_performances');
        echo $this->table->generate($playlist);
    }

    function _letters_anchor($uri = 'playlist/letters')
    {
        $links = array ();
        $letters = "ALL NUM A B C D E F G H I J K L M N O P Q R S T U V W X Y Z";
        $letters_array = explode(' ', strtolower($letters));
        foreach ($letters_array as $char)
        {
            $links[]['link'] = anchor($uri.'/'.urlencode($char), strtoupper($char));
        }

        return $links;
    }

    function _xml2array2($xml)
    {
        $data = NULL;

        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, true);

        xml_parse_into_struct($parser, $xml, $values, $index);
        xml_parser_free($parser);

        //        $temp = ($depth = array ());
        $depth = array ();
        $temp = array ();
        $dc = array ();
        $p = 0;

        foreach ($values as $value)
        {

            //            $p = implode(',', $depth);
            $key = $value['tag'];

            print $p;
            print "Value Type: ".$value['type'];
            switch($value['type'])
            {
                case 'open':
                    array_push($depth, $key);
                    array_push($depth, (int)$dc[$p]++);
                    break;

                case 'complete':
                    array_pop($depth);
                    array_push($depth, $key);
                    $p = implode(',', $depth);
                    $temp[$p] = $value['value'];
                    array_pop($depth);
                    array_push($depth, (int)$dc[$p]);
                    break;

                case 'close':
                    array_pop($depth);
                    array_pop($depth);
                    break;
            }
        }

        foreach ($temp as $key=>$value)
        {
            $levels = explode(',', $key);
            $num_levels = count($levels);

            if ($num_levels == 1)
            {
                $data[$levels[0]] = $value;
            }
            else
            {
                $pointer = & $data;
                for ($i = 0; $i < $num_levels; $i++)
                {
                    if (! isset ($pointer[$levels[$i]]))
                    {
                        $pointer[$levels[$i]] = array ();
                    }
                    $pointer = & $pointer[$levels[$i]];
                }
                $pointer = $value;
            }
        }
        return ($data);
    }

    function _xml2array($xml_value, $type = 'string', $get_attributes = 1, $priority = 'tag')
    {
        $parser = xml_parser_create('');

        if ($type == 'string')
        {
            $contents = $xml_value;
        }
        else
        {
            if (!($fp = @fopen($xml_value, 'rb')))
            {
                return array ();
            }

            while (!feof($fp))
            {
                $contents .= fread($fp, 8192);
            }

            fclose($fp);

        }

        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);

        $xml_array = array ();
        $parents = array ();
        $opened_tags = array ();
        $arr = array ();
        $current = & $xml_array;
        $repeated_tag_index = array ();

        foreach ($xml_values as $data)
        {
            unset ($attributes, $value);
            extract($data);
            $result = array ();
            $attributes_data = array ();
            if ( isset ($value))
            {
                if ($priority == 'tag')
                {
                    $result = $value;
                }
                else
                {
                    $result['value'] = $value;
                }
            }

            print $attributes;

            if ( isset ($attributes) and $get_attributes)
            {
                foreach ($attributes as $attr=>$val)
                {
                    if ($priority == 'tag')
                    {
                        $attributes_data[$attr] = $val;
                    }
                    else
                    {
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'}
                    }
                }
                if ($type == "open")
                {
                    $parent[$level-1] = & $current;
                    if (!is_array($current) or (!in_array($tag, array_keys($current))))
                    {
                        $current[$tag] = $result;
                        if ($attributes_data)
                        {
                            $current[$tag.'_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        $current = & $current[$tag];
                    }
                    else
                    {
                        if ( isset ($current[$tag][0]))
                        {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                            $repeated_tag_index[$tag.'_'.$level]++;
                        }
                        else
                        {
                            $current[$tag] = array (
                            $current[$tag],
                            $result
                            );
                            $repeated_tag_index[$tag.'_'.$level] = 2;
                            if ( isset ($current[$tag.'_attr']))
                            {
                                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                unset ($current[$tag.'_attr']);
                            }
                        }
                        $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                        $current = & $current[$tag][$last_item_index];
                    }
                }
                elseif ($type == "complete")
                {
                    if (! isset ($current[$tag]))
                    {
                        $current[$tag] = $result;
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        if ($priority == 'tag' and $attributes_data)
                        {
                            $current[$tag.'_attr'] = $attributes_data;
                        }
                    }
                    else
                    {
                        if ( isset ($current[$tag][0]) and is_array($current[$tag]))
                        {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                            if ($priority == 'tag' and $get_attributes and $attributes_data)
                            {
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level].'_attr'] = $attributes_data;
                            }
                            $repeated_tag_index[$tag.'_'.$level]++;
                        }
                        else
                        {
                            $current[$tag] = array (
                            $current[$tag],
                            $result
                            );
                            $repeated_tag_index[$tag.'_'.$level] = 1;
                            if ($priority == 'tag' and $get_attributes)
                            {
                                if ( isset ($current[$tag.'_attr']))
                                {
                                    $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                    unset ($current[$tag.'_attr']);
                                }
                                if ($attributes_data)
                                {
                                    $current[$tag][$repeated_tag_index[$tag.'_'.$level].'_attr'] = $attributes_data;
                                }
                            }
                            $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                        }
                    }
                }
                elseif ($type == 'close')
                {
                    $current = & $parent[$level-1];
                }
            }
            return ($xml_array);
        }

    }
}