<?php

if ( ! function_exists('when'))
{
    function when($dt,$precision=2)

    {
    $times=array(   365*24*60*60    => "year",
                    30*24*60*60     => "month",
                    7*24*60*60      => "week",
                    24*60*60        => "day",
                    60*60           => "hour",
                    60              => "minute",
                    1               => "second");

    $passed=time()-$dt;

    if($passed<5)
    {
        $output='less than 5 seconds ago';
    }
    elseif($passed > 172800)
    {
         $output=date("F jS, Y",$dt);
    }
    else
    {
        $output=array();
        $exit=0;

        foreach($times as $period=>$name)
        {
            if($exit>=$precision OR ($exit>0 && $period<60)) break;

            $result = floor($passed/$period);
            if($result>0)
            {
                $output[]=$result.' '.$name.($result==1?'':'s');
                $passed-=$result*$period;
                $exit++;
            }
            else
                if($exit>0)
                    $exit++;
        }

        $output=implode(', ',$output).' ago';
    }

    return $output;
} 
}
