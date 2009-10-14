<?php

class Stat extends Controller
{

    function Stat()
    {
        parent::Controller();
        $this->load->model('stream_model', 'stream');
        $this->load->model('playlist_model', 'playlist');
        $this->load->dbutil();
        $this->load->helper('url');
        $this->load->helper('date');
        $this->load->helper('text');
        $this->load->library('parser');
    }

    function index()
    {
        echo 'Stats controller';
    }

    function listeners($graph = '')
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

        //        $imageSize = $this->_remotefsize($url);
        $imageFp = fopen($url, 'rb');
		$fileSize = $this->_remote_filesize($url);
        $imageContent = fread($imageFp, $fileSize);
        
        if ($graph == 'debug')
        {
            print $fileSize;
        }
        else
        {
            header('Content-Type: image/png');
            header('Content-Length: '.$this->_remote_filesize($url));
            echo $imageContent;
        }
    }

    function _remotefsize($url)
    {
        $sch = parse_url($url);

        print_r($sch);
        if (($sch['scheme'] != "http") && ($sch != "https"))
        {
            print "bad sheme";
            //            return false;

        }
        elseif (($sch['scheme'] == "http") || ($sch['scheme'] == "https"))
        {
            print "yo";
            $headers = $this->_get_headers($url, 1);
            if ((!array_key_exists("Content-Length", $headers)))
            {
                print "no content length";
                //                return false;
            }
            print $headers["Content-Length"];
            //            return $headers["Content-Length"];
        }
    }

    function _remote_filesize($url, $user = "", $pw = "")
    {
        ob_start();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);

        if (! empty($user) && ! empty($pw))
        {
            $headers = array ('Authorization: Basic '.base64_encode("$user:$pw"));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $ok = curl_exec($ch);
        curl_close($ch);
        $head = ob_get_contents();
        ob_end_clean();

        $regex = '/Content-Length:\s([0-9].+?)\s/';
        $count = preg_match($regex, $head, $matches);

        return isset ($matches[1])?$matches[1]:"unknown";
    }

    function _get_headers($Url, $Format = 0, $Depth = 0)
    {
        $Key = '';
        $Value = '';
        if ($Depth > 5)
        {
            return;
        }
        $Parts = parse_url($Url);
        if (!array_key_exists('path', $Parts))
        {
            $Parts['path'] = '/';
        }
        if (!array_key_exists('port', $Parts))
        {
            $Parts['port'] = 80;
        }
        if (!array_key_exists('scheme', $Parts))
        {
            $Parts['scheme'] = 'http';
        }

        $Return = array ();
        $fp = fsockopen($Parts['host'], $Parts['port'], $errno, $errstr, 30);
        if ($fp)
        {
            $Out = 'GET '.$Parts['path'].( isset ($Parts['query'])?'?'.@$Parts['query']:'')." HTTP/1.1\r\n".
            'Host: '.$Parts['host'].($Parts['port'] != 80?':'.$Parts['port']:'')."\r\n".
            'Connection: Close'."\r\n";
            fwrite($fp, $Out."\r\n");
            $Redirect = false;
            $RedirectUrl = '';
            while (!feof($fp) && $InLine = fgets($fp, 1280))
            {
                if ($InLine == "\r\n")
                {
                    break;
                }
                $InLine = rtrim($InLine);
                $split = explode(': ', $InLine, 2);
                print "<pre>";
                print_r($split);
                print "</pre>";
                list ($Key, $Value) = $split;
                if ($Key == $InLine)
                {
                    if ($Format == 1)
                    {
                        $Return[$Depth] = $InLine;
                    }
                    else
                    {
                        $Return[] = $InLine;
                    }

                    if ((strpos($InLine, '301') > 0) || (strpos($InLine, '302') > 0) || (strpos($InLine, '303') > 0))
                    {
                        $Redirect = true;
                    }
                }
                else
                {
                    if ($Key == 'Location')
                    {
                        $RedirectUrl = $Value;
                    }
                    if ($Format == 1)
                    {
                        $Return[$Key] = $Value;
                    }
                    else
                    {
                        $Return[] = $Key.': '.$Value;
                    }
                }
            }
            fclose($fp);
            if ($Redirect && ! empty($RedirectUrl))
            {
                $NewParts = parse_url($RedirectUrl);
                if (!array_key_exists('host', $NewParts))
                {
                    $RedirectUrl = $Parts['host'].$RedirectUrl;
                }
                if (!array_key_exists('scheme', $NewParts))
                {
                    $RedirectUrl = $Parts['scheme'].'://'.$RedirectUrl;
                }
                $RedirectHeaders = get_headers($RedirectUrl, $Format, $Depth+1);
                if ($RedirectHeaders)
                {
                    $Return = array_merge_recursive($Return, $RedirectHeaders);
                }
            }
            return $Return;
        }
        return false;
    }
}

?>
