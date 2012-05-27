<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

function pr($arr)
{
    print '<pre>';
    print_r($arr);
    print '</pre>';
}