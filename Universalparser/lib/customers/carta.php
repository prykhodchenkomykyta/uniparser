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

function mapping_carta($aInputFile)
{
    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
  

    // $aInputFile1 = file($inputDir, FILE_SKIP_EMPTY_LINES);
    // $aHeader = array_slice($aInputFile1, 0, 1);
    // $aTrailer = array_slice($aInputFile1, count($aInputFile1)-1,1);
    
    // $sInputFile1 = file_get_contents($inputDir);
    // $aDetailRecordsParser = preg_split("/(#END#)/",  $sInputFile1, NULL, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    // $aDetailRecordsParser[0] = substr($aDetailRecordsParser[0],22);
    // $aDetailRecordsParser = array_slice($aDetailRecordsParser,0,-1);
    
    // $aDetailRecord;
    foreach($aDetailRecordsParser as $key => $sRecord){
        if(substr($sRecord,0,5) == "#END#")
        {
            $aDetailRecord[] = ltrim($aDetailRecordsParser[$key-1]."#END#");
        }
    }
   
    $aInputFile = array_merge($aHeader, $aDetailRecord ,$aTrailer);

    $aHEADER_RECORD = array(
        "RECORD_TYPE" => 2,
        "BATCH_LOG_ID" => 12,
        "PROCESSING_DATE" => 8,

    );
    
    $aDetailRecords = array_slice($aInputFile, 1, count($aInputFile)-2);

 
    $iChipDataLength;
    foreach($aDetailRecords as $sRecord){
        
        $startPos = strpos($sRecord, "#SMC#");
        echo "$sDateStamp [$sUser]: Chip Data Start pos: $startPos\n";

        $endPos = strpos($sRecord, "#END#");
        echo "$sDateStamp [$sUser]: Chip Data End pos: $endPos\n";

        $iChipDataLength = $endPos - $startPos +5;
        echo("$sDateStamp [$sUser]: Chip Length: $iChipDataLength\n");

    }
    
    $aDETAIL_RECORDS = array (
        "RECORD_TYPE" => 2,
        "RECORD_NUMBER" =>6,
        "FILLER_1" => 1,
        "PRODUCT_CODE" => 3,
        "FILLER_2" => 1,
        "CARD_NUMBER" => 19,
        "FILLER_3" => 1,
        "TITLE_CODE" => 2,
        "FILLER_4" => 1,
        "EMBOSSED_NAME" => 26,
        "FILLER_5" => 1,
        "EXPIRY_DATE" => 5,
        "CLIENT_CODE" => 24,
        "EMBOSSED_FLAG" => 6,
        "NETWORK_SIGN" => 1,
        "FILLER_6" => 1,
        "INDENT_CARD" => 20,
        "FILLER_7" => 1,
        "TRACK_1" => 73,
        "FILLER_8" => 1,
        "TRACK_2" => 39,
        "START_VALIDITY_DATE" => 8,
        "CVC2" => 3,
        "PERSO_DATA" => 1028,
        "EMBOSSED_1" => 30,
        "EMBOSSED_2" => 30,
        "ADD1" => 30,
        "ADD2" => 30,
        "ADD3" => 30,
        "ADD4" => 30,
        "CITY" => 30,
        "STATE"=> 50,
        "COUNTRY" => 32,
        "BRANCH" => 6,
        "ACCOUNT" => 24,
        "PRODUCT" => 32,
        "TYPE" => 1,
        "ZIP"=> 30,
        "FREE1" => 30,
        "FREE2" => 30,
        "FREE3" => 30,
        "FREE4" => 30,
        "FREE5" => 30,
        "ORGANISATION_ID" => 24,
        "CHIP DATA" => $iChipDataLength,
        "CITIZEN DATA" => 10704,
        "END_OF_RECORD" => 5,
       
           
    );

    $aTRAILER_RECORD = array(
        "RECORD_TYPE" => 2,
        "PROCESSING_DATE" => 8,
        "BANK_CODE" => 6,
        "TOTAL_RECORDS" => 9,
        "FILLER_END" => 0,
    );

    $iFileHeaderNo = 0;
    $iBatchHeaderNo = 0;
    $iDetailRecordNo = 0;
    $iBatchFooterNo = 0;
    $iFileFooterNo = 0;
    $iRecordNo=0;
    $iBatchID;

    foreach($aInputFile as $iIndex => $sRecord){
        
        $sBOM = pack('H*','EFBBBF');        
        $sRecord = preg_replace("/^$sBOM/", '', $sRecord);

        switch(substr($sRecord,0,2))
            {
                case "HR":
                    $iFileHeaderNo++;
                    $iPos = 0;
                    foreach($aHEADER_RECORD as $sKey => $iLength)
                    {
                       $aFileRecords["HEADER_RECORD"][$sKey] =  substr($sRecord, $iPos, $iLength);
                      $iPos+=$iLength;
                    } 
                    break;
                case "DT":
                    $iDetailRecordNo++;
                    $iPos = 0;
                    foreach($aDETAIL_RECORDS as $sKey => $iLength)
                    {
                        
                        $aFileRecords["HEADER_RECORD"]["DETAIL_RECORD"][$iRecordNo][$sKey] = substr($sRecord, $iPos, $iLength);
                        $iPos+=$iLength;
                    }                    
                    $iRecordNo++;
                    break;
                case "TR":
                    $iFileFooterNo++;
                    $iPos = 0;
                    foreach($aTRAILER_RECORD as $sKey => $iLength)
                    {
                     
                        $aFileRecords["TRAILER_RECORD"][$sKey] = substr($sRecord, $iPos, $iLength);
                        $iPos+=$iLength;
                    } 
                    break;    
            
                }
            }
        

    return $aFileRecords;  
   
    
}


?>