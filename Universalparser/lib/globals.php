<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 09/27/2022
Revision: 09/27/2022
Name: Radovan Jakus
Version: 1.0
Notes: Global Variables Definition
******************************/

    $sDateStamp = date('Y-m-d H:i:s', time());
    $sUser = get_current_user();
    $BarcodeID = "00";
    $ServiceTypeID = "270";
    $MailerID = "902695246";
    $SerialNumberOfDigits = (strlen($MailerID)==9)? 6 : 9;
    $aErrors = array();
    $SerialNumberLocal = __dir__ . '/lib/SerialNumberCounter.csv';
    $sSupplementalFileName = "";


?>