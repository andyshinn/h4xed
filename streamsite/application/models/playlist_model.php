<?php
class Playlist_model extends Model
{
    var $separator = '-';
    var $search = '_';

    function Playlist_model()
    {
        parent::Model();
    }

    function requests($date = NULL, $limit = 50)
    {
    	if ($date)
		{
			$this->db->where("t_stamp BETWEEN DATE_SUB(DATE_ADD(\"$date\", INTERVAL 1 DAY), INTERVAL 1 DAY) AND DATE_ADD(\"$date\", INTERVAL 1 DAY)");
		}
		
        $this->db->select('requestlist.ID, requestlist.songID, host, code, requestlist.status, artist, title, album, t_stamp');
        $this->db->from('requestlist');
        $this->db->join('songlist', 'requestlist.songID = songlist.ID', 'left');
        $this->db->order_by('requestlist.ID', 'desc');
		
        return $this->db->get();
    }

    function search_title($pattern)
    {
        $this->db->select('artist, title, album, albumyear, genre');
        $this->db->from('songlist');
        $this->db->like('title', $pattern);

        return $this->db->get();
    }

    function search_album($pattern)
    {
        $this->db->select('artist, count(title) as titlecount, album, albumyear, genre');
        $this->db->from('songlist');
        $this->db->like('album', $pattern);
        $this->db->group_by( array ('album'));

        return $this->db->get();
    }

    function search_artist($pattern)
    {
        $this->db->select('artist, count(title) as titlecount, count(DISTINCT album) as albumcount');
        $this->db->from('songlist');
        $this->db->like('artist', $pattern);
        $this->db->group_by( array ('artist'));

        return $this->db->get();
    }

    function artists($letter = null, $per_page = null, $limit_offset = 0)
    {
        if ($letter)
        {
            if ($letter == 'num')
            {
                $this->db->like('artist', "0", 'after');
                for ($i = 1; $i < 10; $i++)
                {
                    $this->db->or_like('artist', "$i", 'after');
                }
            }
            else
            {
                $this->db->like('artist', $letter, 'after');
            }
        }

        if ($per_page)
        {
            $this->db->limit($per_page, $limit_offset);
        }

        //$this->db->select('ID AS song_id, duration AS song_duration, artist AS song_artist, title AS song_title, album AS song_album, count_played, count_requested, count_performances');
        $this->db->select('artist, count(DISTINCT title) as titlecount, count(DISTINCT album) as albumcount');
        $this->db->from('songlist');
        $this->db->group_by( array ('artist'));

        $this->db->order_by('artist', 'asc');
        //        $this->db->order_by('album', 'asc');
        //        $this->db->order_by('trackno', 'asc');

        return $this->db->get();
    }

    function artist($artist)
    {

        $search_array = $this->_term_array($artist);

        $this->db->select('artist, count(DISTINCT title) as titlecount, count(DISTINCT album) as albumcount');
        $this->db->from('songlist');

        foreach ($search_array as $word)
        {
            $this->db->like('artist', $word);
        }
        $this->db->group_by( array ('artist'));
        $this->db->limit(1);

        return $this->db->get();
    }
	
	function duration()
	{
		$this->db->select('SUM(duration/1000) AS total');
		$this->db->from('songlist');
		
		$query = $this->db->get();
		
		return $query->row();
	}

    function albums($artist)
    {
        $search_array = $this->_term_array($artist);

        $this->db->select('artist AS album_artist, album AS album_name, albumyear AS album_year, count(DISTINCT title) as album_titlecount, genre AS album_genre');
        $this->db->from('songlist');

        foreach ($search_array as $word)
        {
            $this->db->like('artist', $word);
        }
        $this->db->group_by( array ('artist', 'album'));
        $this->db->order_by('albumyear', 'desc');

        return $this->db->get();
    }

    function tracks($artist_safe = null, $album_safe = null)
    {
        if (isset($artist_safe) && isset($album_safe))
        {
            $search_array_artist = $this->_term_array($artist_safe);
            $search_array_album = $this->_term_array($album_safe);

            foreach ($search_array_artist as $word)
            {
                $this->db->like('artist', $word);
            }

            foreach ($search_array_album as $word)
            {
                $this->db->like('album', $word);
            }

            $this->db->select('ID as song_id, artist AS track_artist, album AS album_name, albumyear AS track_year, title as track_title, genre AS track_genre, duration AS track_duration, trackno AS track_no');
            $this->db->from('songlist');
			$this->db->order_by('trackno', 'asc');
			
			return $this->db->get();
        }
    }

    function listing($songId = null, $songCount = null, $sortField = 'ID', $sortOrder = 'desc')
    {
        $this->db->select('s1.ID, s1.duration, s1.artist, s1.title, s1.album, s1.trackno, s1.genre, s1.count_played, s1.count_requested, s1.count_performances');

        if ($songCount)
        {
            $this->db->select('COUNT(*) as artistsongs');
            $this->db->join('songlist AS s2', 's1.artist = s2.artist', 'left');
            $this->db->group_by('s1.artist');
        }

        $this->db->from('songlist AS s1');
        if ($songId)
        {
            $this->db->where('s1.ID', $songId);
        }
        else if ($sortField)
		{
			$this->db->order_by($sortField, $sortOrder);
		}
		else
        {
            $this->db->order_by('s1.artist', 'asc');
            $this->db->order_by('s1.album', 'asc');
            $this->db->order_by('s1.trackno', 'asc');
        }
		
		return $this->db->get();
    }

    function get($songId = '', $artistCount = FALSE)
    {

        $this->db->select('s1.ID, s1.duration, s1.artist, s1.title, s1.album, s1.trackno, s1.genre, s1.count_played, s1.count_requested, s1.count_performances');

        if ($artistCount)
        {
            $this->db->select('COUNT(*) as artistsongs');
            $this->db->join('songlist AS s2', 's1.artist = s2.artist', 'left');
            $this->db->group_by('s1.artist');
        }

        $this->db->from('songlist AS s1');
        if ($songId)
        {
            $this->db->where('s1.ID', $songId);
        }
        else
        {
            $this->db->order_by('s1.artist', 'asc');
            $this->db->order_by('s1.album', 'asc');
            $this->db->order_by('s1.trackno', 'asc');
        }
        //        $this->db->group_by( array ("album", "artist"));

        $query = $this->db->get();

        return $query;
    }

    function lastSongID()
    {
        $this->db->select('historylist.ID, historylist.songID');
        $this->db->from('historylist');
        $this->db->order_by('historylist.ID', 'desc');
        $this->db->limit(1, 1);

        $query = $this->db->get();
        $row = $query->row();
        return (int)$row->songID;
    }

    function currentSongID()
    {
        $this->db->select('historylist.ID, historylist.songID');
        $this->db->from('historylist');
        $this->db->order_by('historylist.ID', 'desc');
        $this->db->limit(1, 0);

        $query = $this->db->get();
        $row = $query->row();
        return (int)$row->songID;
    }

    function get_letter($letter = NULL, $per_page = NULL, $limit_offset = 0)
    {
        if ($letter)
        {
            if ($letter == 'num')
            {
                $this->db->like('artist', "0", 'after');
                for ($i = 1; $i < 10; $i++)
                {
                    $this->db->or_like('artist', $i, 'after');
                }
            }
            else
            {
                $this->db->like('artist', $letter, 'after');
            }
        }

        if ($per_page)
        {
            $this->db->limit($per_page, $limit_offset);
        }

        $this->db->select('ID AS song_id, duration AS song_duration, artist AS song_artist, title AS song_title, album AS song_album, count_played, count_requested, count_performances');
        $this->db->from('songlist');

        $this->db->order_by('artist', 'asc');
        $this->db->order_by('album', 'asc');
        $this->db->order_by('trackno', 'asc');

        return $this->db->get();
    }

    function getAlbum($albumName = '')
    {
        $this->db->select('ID, duration, artist, album, trackno, genre, count_played, count_requested, count_performances');
        $this->db->from('songlist');
        $this->db->group_by("album");
        $this->db->order_by('artist', 'asc');
        $this->db->order_by('album', 'asc');
        $query = $this->db->get();

        return $query;
    }

    function song_count($song_id = NULL)
    {
        $this->db->select('COUNT(*) as artist_song_count');
        $this->db->join('songlist AS joiner', 'songlist.artist = joiner.artist', 'left');
        $this->db->group_by('songlist.artist');

        $this->db->from('songlist');
        $this->db->where('songlist.ID', $song_id);

        return $this->db->get();
    }


    function current_song()
    {
        $this->db->select('historylist.ID, historylist.songID as song_id, historylist.date_played, historylist.duration, historylist.artist, historylist.title, historylist.album, historylist.listeners, songlist.count_played');
        $this->db->from('historylist');
        $this->db->join('songlist', 'songlist.id = historylist.songID');
        $this->db->order_by('date_played', 'desc');
        $this->db->limit(1);

        return $this->db->get();
    }

    function history($count = '')
    {
        $this->db->select('ID, songID, date_played, duration, artist, title, album, listeners');
        $this->db->from('historylist');
        $this->db->order_by('date_played', 'desc');
        if ($count)
        {
            $this->db->limit($count, 1);
        }
        else
        {
            $this->db->limit(5, 1);
        }

        $query = $this->db->get();

        return $query;
    }

    function songCount($songId = '')
    {
        $this->db->select('ID, artist, COUNT(title) as count');
        $this->db->from('songlist');
        $this->db->group_by('artist');
        $this->db->having('ID', $songId);
        $query = $this->db->get();

        return $query;
    }

    function _term_array($term)
    {
        return explode($this->separator, url_title(str_replace($this->search, $this->separator, $term)));
    }
}
?>
