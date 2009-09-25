<?php
if (!defined('BASEPATH')) exit ('No direct script access allowed');

class Sam
{
    var $sam_host = 'fizone.com';
    var $sam_port = 1221;

    function Sam()
    {
        $this->ci = & get_instance();
    }

    function request($song_id = NULL)
    {
        $xml_data = NULL;
        $sam_request = "GET /req/?songID=".$song_id."&host=".urlencode($this->ci->input->ip_address())." HTTP\1.0\r\n\r\n";

        $fp = fsockopen($this->sam_host, $this->sam_port, $errno, $errstr, 30);
        if (!$fp)
        {
            echo "$errstr ($errno)<br />\n";
        }
        else
        {
            fwrite($fp, $sam_request);
            $line = NULL;

            while (!($line == "\r\n"))
            {
                $line = fgets($fp, 128);
            }

            while (!feof($fp))
            {
                $xml_data .= fgets($fp, 4096);
            }
			
            fclose($fp);
        }

        return $xml_data;
    }

    function get_error($error_number = NULL)
    {
        $message = NULL;

        switch($error_number)
        {
            case 800:
                $message = "SAM host must be specified";
                break;
            case 801:
                $message = "SAM host can not be 127.0.0.1 or localhost";
                break;
            case 802:
                $message = "Song ID must be valid";
                break;
            case 803:
                $message = "Unable to connect to server. Station may be offline";
                break;
            case 804:
                $message = "Invalid data returned";
                break;
            default:
                $message = "Unknown error occured while requesting song";
                break;
        }

        return $message;
    }

}
?>
