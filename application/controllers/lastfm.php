<?php

class Lastfm extends Controller
{
    var $user;
    var $password;
    var $key = 'cometothefox';
    var $seconds;
    var $timestamp;

    function Lastfm()
    {
        parent::Controller();
        $this->load->model('playlist_model', 'playlist');
        $this->load->library('audioscrobbler');
		$this->load->library('lastfmapi');

        $this->user = 'h4xedradio';
        $this->password = '3g3r235N';
    }
	
	function test($artist = null)
	{
		if ($artist){
			$this->lastfmapi->artist_getInfo($artist);
		}
	}

    function scrobble($songId = '', $key = '')
    {
        if ($key == $this->key)
        {
            if (intval($songId))
            {
                $query = $this->playlist->get($songId);
                $song = $query->row();
                $this->seconds = round($song->duration/1000);

                print "Scrobbling: $song->artist - $song->title\n";
                print "'_ Album: $song->album Seconds\n";
                print "'_ Duration: $this->seconds Seconds\n";
                print "'_ Track Number: $song->trackno\n\n";

                print "Authenticating as: $this->user\n";
                $this->audioscrobbler->login($this->user, $this->password);

                if ($this->audioscrobbler->handshake())
                {
                    print "Authentication: OK\n\n";
                    if ($this->audioscrobbler->nowplaying($song->artist, $song->title, $song->album, $this->seconds, $song->trackno))
                    {
                        print "Now Playing: Success\n";
                    }
                    else
                    {
                        print "Now Playing: Failed\n";
                    }

                    //					$this->timestamp = (time() - $this->seconds);
                    $this->timestamp = time();

                    $this->audioscrobbler->add($song->artist, $song->title, $this->timestamp, 'P', NULL, $this->seconds, $song->album, $song->trackno);

                    print "Scrobbling Track: ";
                    if ($this->audioscrobbler->scrobble())
                    {
                        print "Success";
                    }
                    else
                    {
                        print "Failed";
                    }
                    print "\n";
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
