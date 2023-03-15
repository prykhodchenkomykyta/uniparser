<?php

// build stage:
if (!file_exists('/var/tmp/TSSS/DataPrep/in/'))                   {    mkdir('/var/tmp/TSSS/DataPrep/in/', 0777, true);}
if (!file_exists('/var/tmp/TSSS/Files/USPS/BULK/'))               {    mkdir('/var/tmp/TSSS/Files/USPS/BULK/', 0777, true);}
if (!file_exists('/var/tmp/TSSS/Files/FEDEX/BULK/'))              {    mkdir('/var/tmp/TSSS/Files/FEDEX/BULK/', 0777, true);}
if (!file_exists('/var/tmp/TSSS/Files/MAILMERGE/'))               {    mkdir('/var/tmp/TSSS/Files/MAILMERGE/', 0777, true);}
if (!file_exists('/var/tmp/TSSS/Files/TAGPL/'))                   {    mkdir('/var/tmp/TSSS/Files/TAGPL/', 0777, true);}
if (!file_exists('/var/tmp/TSSS/Files/Reports/galileo/waiting/')) {    mkdir('/var/tmp/TSSS/Files/Reports/galileo/waiting/', 0777, true);}
?>