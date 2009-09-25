Here is a simple example Web logic PHP Script.
These scripts tell SAM Broadcaster how to handle the choosing of the next song as well as requests.

To use you need to enable the web logic module
SAM->Config->Playlist rotation rules->Playlist logic modules->Web scripting based lofic module->Configure

Now add the path to the weblogic.choose.php file as the first entry
Then weblogic.request.php as the last...
ie.
On choose song: http://localhost/samweb/weblogic/weblogic.choose.php
On Request: http://localhost/samweb/weblogic/weblogic.request.php
Also check the "Async" checkbox for the last entry...
