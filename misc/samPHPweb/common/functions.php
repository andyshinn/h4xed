<?php

function Preparesong(&$song)
{
 global $picture_na, $picture_dir, $sam, $stationid;

 Def($song["id"],0);
 Def($song["songid"],$song["id"]); 
 Def($song["artist"],"");
 Def($song["title"],"");
 Def($song["album"],"");
 Def($song["duration"],-1);
 Def($song["picture"],"");
 Def($song["buycd"],"");
 Def($song["website"],"");

 if(empty($song["picture"])) 
  {
   $song["picture"] = $picture_na;
   $song["haspicture"] = false;
  }
 else
  {
   $song["picture"] = $picture_dir.$song["picture"];
   $song["haspicture"] = true;
  }
  
 //Make Artist+Tile combination
 if(empty($song["artist"])) 
  $song["combine"] = $song["title"];
 else
  $song["combine"] = $song["artist"] . " - ". $song["title"];
  
 $ss = round($song["duration"] / 1000);
 $mm = (int)($ss / 60);
 $ss = ($ss % 60);
 if($ss<10) $ss="0$ss";
 $song["mmss"] = "$mm:$ss";
 
  
 //Make a link that will search for the best place to buy the CD 
 if(empty($song["buycd"])) 
  {
   $data = "http://www.audiorealm.com/findcd.html?artist=#artist#&title=#title#&album=#album#";
   $song["buycd"] = FillData($song,$data);
  }
  
 //Make a link to search for the artist homepage
 if(empty($song["website"])) 
  {
   $data = "http://www.audiorealm.com/findwebsite.html?artist=#artist#&title=#title#&album=#album#";
   $song["website"] = FillData($song,$data);
  }
  
  //Make a request link 
  $song["request"] = "javascript:request($song[songid])";

}

?>