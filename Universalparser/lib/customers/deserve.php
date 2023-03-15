<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 09/27/2022
Revision: 09/27/2022
Name: Radovan Jakus
Version: 1.0
Notes: Mapping Definition
******************************/

function mapping_deserve($aInputFile){

    $aInputFile = file_get_contents($inputDir);
    $aRecordData = json_decode($aInputFile, true);
    foreach($aRecordData['details'] as $RecNo => $aRecord)
    {
        if(strlen($SerialNumber)>$SerialNumberOfDigits)
        {
            $SerialNumber = 1;
        }
        $aRecordData['details'][$RecNo]['SerialNumber'] =  str_pad($SerialNumber++,$SerialNumberOfDigits,'0',STR_PAD_LEFT); 
    }

    $iNoOfCards = count($aRecordData['details']);
    $BATCH_LOG_ID = $iNoOfCards;
    //$iNoOfCards = $aRecordData['trailer']['num_of_detail_records'];
    echo "$sDateStamp [$sUser]: Starting Parsing Data\n";
    echo "$sDateStamp [$sUser]: Number of cards in the file: ".$iNoOfCards."\n";
    setSerialNumber($SerialNumberLocal,$SerialNumberOfDigits,$SerialNumber);

    return $aRecordData;
}

?>