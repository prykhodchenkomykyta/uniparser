<?php 
/******************************
Author: Jean-Eric Pierre-Louis
Company: Pierre & Rady LLC
Date: 10/20/2020
Revision: 01/25/2022
Name: Radovan Jakus
Version: 1.10
Notes: Added Company, Reference#-4, FromAddress2. FromFullName was cleared of value. 
******************************/

//Production Environment
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/marygold/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sBulkOutputDir = "/var/TSSS/Files/USPS/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/marygold/";

// $sInputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Carta_Marygold\\in\\";
// $sInputFilePath;
// $sOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Carta_Marygold\\out\\";
// $sBulkOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Carta_Marygold\\out\\";
// $sMailOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Carta_Marygold\\out\\";
// $sProcessedDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Carta_Marygold\\in\\";

$sDataPrepProfile = "P_MARYGOLD_MC_45_xx_xx_94_98_85_SECORA_S_V162307013_v142";
$sCustomerName= "MARYGOLD";

ob_start();
header('Content-Type: application/json');
date_default_timezone_set ("America/New_York");
$sDateStamp = date('Y-m-d H:i:s', time());
$sUser = get_current_user();

echo "$sDateStamp [$sUser]: Starting Script \n";

$aOptions  = getopt("p::n::");
$sInputFilePath;


$bDataProcessed = false;
$bMailProcessed = false;


if(!empty($aOptions ['p'])){
    $sInputFilePath = $aOptions ['p'];
    echo "$sDateStamp [$sUser]: Using full path option \n";
    if(file_exists($sInputFilePath))
        {
            $bDataProcessed = CartaDataPrepInput(CartaParser($sInputFilePath), $sInputFilePath, $sOutputDir, $sProcessedDir);
            $bMailProcessed = CartaMailingInput(CartaParser($sInputFilePath), $sInputFilePath, $sMailOutputDir, $sProcessedDir);
            {
                $sProcessedFilename = basename($sInputFilePath);
                $bFileMoved = rename($sInputFilePath , $sProcessedDir.$sProcessedFilename);
                if($bFileMoved)
                {
                    echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sProcessedFilename \n";
                }
                else 
                {
                    echo "$sDateStamp [$sUser]: Processed File failed to be moved to: $sProcessedDir$sProcessedFilename \n";
                }
            }
        }
        else
        {
            die("\nERROR: The file does not exist. Check the name or path of the file. FILE:".$sInputFilePath);  
        }
}else if(!empty($aOptions ['n'])){
    $sInputFilePath = $sInputDir."\\".$aOptions ['n'];
    echo "$sDateStamp [$sUser]: Using file name option \n";
    if(file_exists($sInputFilePath))
    {
        $bDataProcessed = CartaDataPrepInput(CartaParser($sInputFilePath), $sInputFilePath, $sOutputDir, $sProcessedDir);
        $bMailProcessed = cartaMailingInput(CartaParser($sInputFilePath), $sInputFilePath, $sMailOutputDir, $sProcessedDir);
        if($bDataProcessed && $bMailProcessed)
        {
            $sProcessedFilename = basename($sInputFilePath);
            $bFileMoved = rename($sInputFilePath , $sProcessedDir.$sProcessedFilename);
            if($bFileMoved)
            {
                echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sProcessedFilename \n";
            }
            else 
            {
                echo "$sDateStamp [$sUser]: Processed File failed to be moved to: $sProcessedDir$sProcessedFilename \n";
            }
        }
    }
    else
    {
        die("\nERROR: The file does not exist. Check the name or path of the file. FILE:".$sInputFilePath);  
    }
}
else{
 echo "$sDateStamp [$sUser]: Using option to process files from predefined directory automatically. List of files in directory: $sInputDir \n";
 $aInputFiles = glob($sInputDir."*", GLOB_NOSORT);
    if($aInputFiles){
            foreach($aInputFiles as $sInputFilePath){
            echo "\t".basename($sInputFilePath)." \n";
            }
            foreach($aInputFiles as $sInputFilePath){
                echo "\n$sDateStamp [$sUser]: Processing file: $sInputFilePath \n";
                echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n";
                echo "$sDateStamp [$sUser]: Batch of the file: ".CartaParser($sInputFilePath)[0]['BATCH_LOG_ID']." \n";
                //$bDataProcessed = CartaParser($sInputFilePath);

                $bDataProcessed = CartaDataPrepInput(CartaParser($sInputFilePath), $sInputFilePath, $sOutputDir, $sProcessedDir);
                $bMailProcessed = CartaMailingInput(CartaParser($sInputFilePath), $sInputFilePath, $sMailOutputDir, $sProcessedDir);
                {
                    $sProcessedFilename = basename($sInputFilePath);
                    $bFileMoved = rename($sInputFilePath , $sProcessedDir.$sProcessedFilename);
                    if($bFileMoved)
                    {
                        echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sProcessedFilename \n";
                    }
                    else 
                    {
                        echo "$sDateStamp [$sUser]: Processed File failed to be moved to: $sProcessedDir$sProcessedFilename \n";
                    }
                }
            }
    }
    else 
    {
        echo "$sDateStamp [$sUser]: There are no files to be processed in directory. The directory does not contain customer files. Directory: $sInputDir\n";
    }
}

function CartaParser($inputDir)
{
    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
  
    

    $sProcessedFilename = basename($inputDir);

    $sFileName = basename($inputDir)."csv";
    $aInputFile1 = file($inputDir, FILE_SKIP_EMPTY_LINES);
    $aHeader = array_slice($aInputFile1, 0, 1);
    $aTrailer = array_slice($aInputFile1, count($aInputFile1)-1,1);
    
    $sInputFile1 = file_get_contents($inputDir);
    $aDetailRecordsParser = preg_split("/(#END#)/",  $sInputFile1, NULL, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $aDetailRecordsParser[0] = substr($aDetailRecordsParser[0],22);
    $aDetailRecordsParser = array_slice($aDetailRecordsParser,0,-1);
    
    $aDetailRecord;
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
        
   // echo("aFileRecords:");
   // print_r($aFileRecords);
    return $aFileRecords;  
   
    
}

function CartaDataPrepInput($input, $inputDir, $outputDir)
{
    echo"DATAPREP";
    global $sDateStamp;
    global $sUser; 
    global $sDataPrepProfile;
    /*DATAPREP*/
      $aDataPrepOutputData;
    
    $sFileName = basename($inputDir).".csv";
  
    $BATCH_LOG_ID =trim($input['HEADER_RECORD']['BATCH_LOG_ID']);

    $iRecordNo = 0;
    $aDataPrepOutputData = array();
    foreach($input['HEADER_RECORD']['DETAIL_RECORD'] as $aRecord)
    { 
        $iRecordNo++;
        $sTrack1 = trim(preg_replace("/%|\?/","",$aRecord['TRACK_1']));
        $sTrack2 = trim(preg_replace("/;|\?/","",$aRecord['TRACK_2']));
        $sChipData = strtoupper(bin2hex(trim($aRecord['CHIP DATA'])));
        $pos57 = strpos($sChipData, "57");
        $length57 = substr($sChipData, $pos57+2,2);
        $tag57 = substr($sChipData, $pos57+4,hexdec($length57)*2);     
        
        $sTrack2Chip = $tag57;
        $posFF13 = strpos($sChipData, "FF13");
	    echo("\nsChipData: ".$sChipData);
        echo("\nposFF13: ".$posFF13);
        $tagFF13 = substr($sChipData, $posFF13+(17*2),16);
        echo("PINBlock: ".$tagFF13."\n");
        $sPINBlock =  $tagFF13;
        $CVC2 = trim($aRecord['CVC2']);
        $sEmbName = str_replace("/","",trim($aRecord['EMBOSSED_NAME']));
    
    $sBatchID = preg_replace("/\D/","",$BATCH_LOG_ID)."/".$iRecordNo;
    $sUniqueNumber= "dc5413d196ee7fcb80f0eeb97e79cfd60b1e54cf";
    $sNotUsed1 = "0000";
    $sNotUsed2 = "00";
    $sNotUsed3 = "000";
   
    $sNotUsed4 = "0000000";
    $sChipData = "$sTrack1#$sTrack2#$sTrack2Chip#$sPINBlock#$CVC2#$sEmbName";
    //DATAPREP RESULT
    $aDataPrepOutputData[] = array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData); 
    }

    $sDataPrepOutputFile = $outputDir."MS_CHIPREP_".$BATCH_LOG_ID."_".$sFileName;
    //echo "$sDateStamp [$sUser]: Writing DataPrep Input file to $sDataPrepOutputFile \n";
    $fp = fopen($sDataPrepOutputFile, "w");
    $bFileWriting1; 
    foreach ($aDataPrepOutputData as $row) 
    { 
        $bFileWriting1 = fwrite($fp, implode(';',$row)).fwrite($fp, "\r\n");
    } 
        if($bFileWriting1)
        {
            echo "$sDateStamp [$sUser]: File succesfully written.\n";
            fclose($fp);
            return true;
        }
        else 
        {
            echo "$sDateStamp [$sUser]: Writing file failed\n";
            fclose($fp);
            return false;
        }
         
   
}

function CartaMailingInput($input, $inputDir, $outputDir)
{
    global $sDateStamp;
    global $sUser;
    $sFileName = basename($inputDir).".csv";
    $BATCH_LOG_ID =trim($input['HEADER_RECORD']['BATCH_LOG_ID']);
    $bBulk = false;
            /*MAILING*/
            $aMailShippingOutputData;
            $iRecordNo = 0;
            foreach($input['HEADER_RECORD']['DETAIL_RECORD'] as $aRecord)
            {
                $sPersoData = trim($aRecord['PERSO_DATA']);
                $iTLVLength = substr(trim($sPersoData), 0,4);

                if($iTLVLength != strlen($sPersoData)-4)
                {
                    die("\nERROR: The expected TLV Data Length ($iTLVLength) is not matching length of TLV Data (".(strlen($sPersoData)-5).").");  
                }
        
                $known_Tags = ["PLC","NMC","J05","J06","CID","DA1","DA2","DA3","DA4","DA5","DA6","DA7","DA8","DA9","OA1","OA2","OA3","OA4","OA5","OA6","OA7","OA8","OA9"];
        
                $aRecordTLV = array();

                foreach($known_Tags as $key1=> $known_Tag)
                {
                  
                     if(strpos($sPersoData, $known_Tag))
                     {
                        $iLength = substr($sPersoData, strpos($sPersoData, $known_Tag)+3, 3);
                        $aRecordTLV[$known_Tag] = substr($sPersoData, strpos($sPersoData, $known_Tag)+6, $iLength);
                     }
                };
            
                

                ++$iRecordNo;
                $FullName = trim($aRecordTLV['J05'])." ".trim($aRecordTLV['J06']);
                $Company = "";
                $Address1 = trim($aRecordTLV['DA2']);
                $Address2 = trim($aRecordTLV['DA3']);
                $City = trim($aRecordTLV['DA6']);
                $State =  trim($aRecordTLV['DA7']); 
                $ZIPCode = substr($aRecordTLV['DA9'], 0, 5);
                $ZIPCodeAddOn = empty(substr($aRecordTLV['DA9'],5)) ? "" : preg_replace("/-/","",substr(trim($aRecordTLV['DA9']),5));
                $Country = (trim($aRecordTLV['DA8']=="USA")) ? "US" : trim($aRecordTLV['DA8']);
                $EmailAddress;
                $FromFullName = "";
                $FromAddress1 = "80 Corbett Way";
                $FromAddress2 = "";
                $FromCity ="Eatontown";
                $FromState= "NJ";
                $FromCountry = "US";
                $FromZIPCode = "07724";                
                $Amount;
                $ServiceType = "US-FC"; //Standard Overnight -> FEDEX, 
                $PackageType = "Large Envelope or Flat";
		        $WeightOz = "1";
                $ShipDate = "+1";
                $ImageType = "Pdf";
                $Reference1 = "MARYGOLD_".$BATCH_LOG_ID."_".$iRecordNo."_".substr($aRecord['CARD_NUMBER'], -4);
                $PAN = str_replace(" ","",$aRecord['CARD_NUMBER']);
                $Reference2 = hash("sha256",$PAN);
                $Reference3 = "";
                $Reference4 = "";

                /*switch($aRecord['SHIPPING_METHOD'])
                {
                    case 1:
                        $ServiceType = "US-FC";
                       // $aMailShippingOutputData[] = array($FullName, $Address1, $Address2,$City,$State,$ZIPCode, $Country, $ServiceType, $ShipDate, $ImageType, $Reference1);
                        break;
                    case 2:
                        $ServiceType = "US-PM";
                        //$aMailShippingOutputData[] = array($FullName, $Address1, $Address2,$City,$State,$ZIPCode, $Country, $ServiceType, $ShipDate, $ImageType, $Reference1);
                        break;   
                    case 3:
                        $ServiceType = "US-XM";
                        //$aMailShippingOutputData[] = array($FullName, $Address1, $Address2,$City,$State,$ZIPCode, $Country, $ServiceType, $ShipDate, $ImageType, $Reference1);
                        break; 
                    case 4: 
                        $bBulk = true; 
                        break;
                }*/
                //MAILING RESULT
                $aMailShippingOutputData[] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn,$Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4);
                
    
            }

         


            $fp;
            if($bBulk){
                $sBulkOutputFile = $bulkOutputDir."Bulk_".$input[0]['BATCH_LOG_ID']."_".$sFileName;
                echo "$sDateStamp [$sUser]: Writing bulk mail Input file to $sBulkOutputFile \n";
                $fp = fopen($sBulkOutputFile, "w");

                $bFileWriting2 = fwrite($fp, implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4"))).fwrite($fp, "\r\n");
                foreach ($aMailShippingOutputData as $row) 
                { 
                    $bFileWriting3 = fwrite($fp, implode("\t",$row)).fwrite($fp, "\r\n");
                } 
                if($bFileWriting2 && $bFileWriting3)
                {
                    echo "$sDateStamp [$sUser]: File succesfully written. \n";
                    fclose($fp);
                    return true;
                }
                else 
                {
                    echo "$sDateStamp [$sUser]: Writing file failed\n";
                    fclose($fp);
                    return false;
                }
                
            }
            else
            {
                $sMailOutputFile = $outputDir."Mail_".$BATCH_LOG_ID."_".$sFileName;
                echo "$sDateStamp [$sUser]: Writing Mail Input file to $sMailOutputFile \n";
                $fp = fopen($sMailOutputFile, "w");

                $bFileWriting2 = fwrite($fp, implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4"))).fwrite($fp, "\r\n");
                foreach ($aMailShippingOutputData as $row) 
                { 
                    $bFileWriting3 = fwrite($fp, implode("\t",$row)).fwrite($fp, "\r\n");
                } 
                if($bFileWriting2 && $bFileWriting3)
                {
                    echo "$sDateStamp [$sUser]: File succesfully written. \n";
                    fclose($fp);
                    return true;
                }
                else 
                {
                    echo "$sDateStamp [$sUser]: Writing file failed\n";
                    fclose($fp);
                    return false;
                }
                
            }
}


echo "$sDateStamp [$sUser]: Ending Script";

?> 
