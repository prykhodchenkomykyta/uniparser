#!/usr/bin/php
<?php


// build stage:
if (!file_exists('/var/TSSS/DataPrep/in/'))                   {    mkdir('/var/TSSS/DataPrep/in/', 0777, true);}
if (!file_exists('/var/TSSS/Files/USPS/BULK/'))               {    mkdir('/var/TSSS/Files/USPS/BULK/', 0777, true);}
if (!file_exists('/var/TSSS/Files/FEDEX/BULK/'))              {    mkdir('/var/TSSS/Files/FEDEX/BULK/', 0777, true);}
if (!file_exists('/var/TSSS/Files/MAILMERGE/'))               {    mkdir('/var/TSSS/Files/MAILMERGE/', 0777, true);}
if (!file_exists('/var/TSSS/Files/TAGPL/'))                   {    mkdir('/var/TSSS/Files/TAGPL/', 0777, true);}
if (!file_exists('/var/TSSS/Files/Reports/galileo/waiting/')) {    mkdir('/var/TSSS/Files/Reports/galileo/waiting/', 0777, true);}

?>