<?php

class Song extends Controller
{
    var $user;
    var $password;
    var $key = 'cometothefox';
    var $seconds;
    var $timestamp;

    function Song()
    {
        parent::Controller();
        $this->load->model('playlist_model', 'playlist');
        $this->load->library('audioscrobbler');
		$this->load->library('lastfm');

        $this->user = 'h4xedradio';
        $this->password = '3g3r235N';
    }
	
	function test($artist = null)
	{
		if ($artist){
			$this->lastfm->artist_getInfo($artist);
		}
	}
	
	function last()
	{
		$last_song_query = $this->playlist->get_last();
		$last_song = $last_song_query->result();
		
		echo $last_song->ID . ' ' . $last_song->artist;
		echo $this->db->last_query();
	}

    function scrobble($key = '')
    {
        if ($key == $this->key)
        {
        	$currentSongID = (int) $this->playlist->currentSongID();
			$lastSongID = (int) $this->playlist->lastSongID();
			
            if (is_int($currentSongID))
            {
                $query_song_cur = $this->playlist->get($currentSongID);
                $song_cur = $query_song_cur->row();
                $this->seconds = round($song_cur->duration/1000);
				
				$query_song_last = $this->playlist->get($lastSongID);
                $song_last = $query_song_last->row();
                $song_last_seonds = round($song_last->duration/1000);

				print "Now Playing Track: $song_cur->artist - $song_cur->title\n";
                print "Scrobbling Track: $song_last->artist - $song_last->title\n";

                print "Authenticating as: $this->user\n";
                $this->lastfm->login($this->user, $this->password);

                if ($this->lastfm->handshake())
                {
                    print "Authentication: OK\n\n";

			$this->timestamp = time();

                    $this->lastfm->add($song_last->artist, $song_last->title, $this->timestamp, 'P', NULL, $song_last_seonds, $song_last->album, $song_last->trackno);

                    print "Scrobbling: ";
                    if ($this->lastfm->scrobble())
                    {
                        print "Success\n\n";
                    }
                    else
                    {
                        print "Failed\n\n";
                    }
		
			print "Now Playing: ";
                    if ($this->lastfm->nowplaying($song_cur->artist, $song_cur->title, $song_cur->album, $this->seconds, $song_cur->trackno))
                    {
                        print "Success\n\n";
                    }
                    else
                    {
                        print "Failed\n\n";
                    }

                    //					$this->timestamp = (time() - $this->seconds);

                }
                else
                {
                    print "Authentication: Failed\n";
                    print time();
                }



            }
            else
            {
                print "Missing or invalid song ID!";
            }
        }
        else
        {
            print "Missing or invalid key!";
        }
    }
}
