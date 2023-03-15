<?php
function ParserCSV($inputDir)
{
    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
    global $sProcessedFilename;
    global $sSerialNumberurl;
    global $SerialNumberLocal; 
    global $SerialNumberOfDigits;



    $sFileName = basename($inputDir);
    $aInputFile = file($inputDir, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";
    echo "$sDateStamp [$sUser]: Starting Parsing Data\n";
    $aRecordData = array();
    foreach($aInputFile as $aData)
    {
        $aInputFileData =str_getcsv($aData);
        $aRecordData[] =$aInputFileData;
    }   

    return  $aRecordData;
}
?>