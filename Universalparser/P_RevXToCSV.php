<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 08/03/2021
Revision: 08/03/2021
Name: Radovan Jakus
Version: 1.1
******************************/

/*Production Environment*/
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/revx/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sFedexOutputDir = "/var/TSSS/Files/FEDEX/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/revx/"; 
/*
$sInputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\RevX\\in\\";
$sOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\RevX\\out\\";
$sMailOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\RevX\\out\\USPS\\";
$sFedexOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\RevX\\out\\FEDEX\\";
$sProcessedDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\RevX\\in\\";   */ 
$BATCH_LOG_ID;
$sDataPrepProfile;
$sCustomerName;
$iNumberOfRecords;
$sBIN;
$SHIPPING_METHOD;
$PRODUCT_ID;
$sFileExtension =".inp";
$aShippingMethods[] = array(    "15002"=> "USPS",
                            );

$aBINs['41801600']=array("Customer"=>"REVX",
                        "15002"=> array(
                            "Profile"=>"P_REVX_VISA_D5_SECORA_S_V01_143",
                            "Product"=>"",
                            "PackageType"=>"Package",
                            "WeightOz" => 1));



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
            $bDataProcessed = DataPrepInput(Parser($sInputFilePath), $sInputFilePath, $sOutputDir, $sProcessedDir);
            $bMailProcessed = MailingInput(Parser($sInputFilePath), $sInputFilePath, $sMailOutputDir, $sProcessedDir);
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
        $bDataProcessed = DataPrepInput(Parser($sInputFilePath), $sInputFilePath, $sOutputDir, $sProcessedDir);
        $bMailProcessed = MailingInput(Parser($sInputFilePath), $sInputFilePath, $sMailOutputDir, $sProcessedDir);
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
            

                 $ParsedData = Parser($sInputFilePath);
                 $bDataProcessed = DataPrepInput($ParsedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
                 $bMailProcessed = MailingInput($ParsedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
                 
                {
                    $sProcessedFilename = basename($sInputFilePath);
                    $bFileMoved = rename($sInputFilePath , $sProcessedDir.$sProcessedFilename);
                    if($bFileMoved)
                    {
                        echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sProcessedFilename \n";
                        echo "$sDateStamp [$sUser]: Total Number of Records: $iNumberOfRecords \n"; 
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

function Parser($inputDir)
{
    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
    global $sProcessedFilename;
    global $BATCH_LOG_ID;
    global $iNumberOfRecords;
    global $aBINs;
    global $sBIN;

    $sFileName = basename($inputDir);
    
    $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);
     
    echo "$sDateStamp [$sUser]: Starting Parsing Data\n";
    /********************************************************
     *  LENGTHS OF DATA
     ********************************************************/

    $aFILE_HEADER_RECORD = array(
        "SEQ_NUMBER" => 6,
        "ODER_NUMBER" => 10,
        "INSTITUTION_NAME" => 30,
        "INSTITUTION_SUB_NAME"=>30,
        "CONTACT_PHONE_NUMBER"=>15,
        "BRANCH_ID"=>10, //ProgramID 15002 RevX
        "FILLER"=>868,
        "LF"=>1
    );    

    $aDETAIL_RECORDS = array (
        "SEQ_NUM" => 6,
        "MPLREF_NO"=>20,
        "ACC_NO"=>20,
        "EMB_NAME" => 26,
        "EMB_NAME_2" => 26,
        "EXP_DATE"=>4,
        "TRACK_1"=>78,
        "TRACK_2" => 39,
        "IMPRINT" => 20,
        "NUMBER_OF_CARDS" =>1,
        "PIN" => 16,
        "MAIL_TO_ADDRESS_1" => 40,
        "MAIL_TO_ADDRESS_2" => 40,
        "MAIL_TO_ADDRESS_3" => 40,
        "MAIL_TO_ADDRESS_4" => 40,
        "MAIL_TO_ZIP" => 10,
        "DISTRIBUTION_CENTER_ID"=>40,
        "ITEM_BAR_CODE"=>30,
        "BUNDLER_BAR_CODE"=>30,
        "LOCAL_PHONE_NO"=>20,
        "PROXY_ACC_NO_TYPE"=>4,
        "BANK_ID"=>20,
        "PROXY_ACC_NO"=>20,
        "EXT_ACC_NO"=>50,
        "EXT_ACC_PRESENTATION"=>50,
        "MPLACC"=>30,
        "CARD_OPT_1"=>30,
        "CARD_OPT_2"=>30,
        "CARD_OPT_3"=>30,
        "CARD_OPT_4"=>30,
        "CARD_OPT_5"=>30,
        "CARD_OPT_6"=>30,
        "FILLER"=>99,
        "EXTENDED_FORMAT_FIELDS"=>2000,
        "LF"=>1,
    );

    $aFILE_FOOTER_RECORD = array(
        "SEQ_NO" => 6,
        "DETAIL_REC"=>6,
        "FILLER"=>987,
        "LF"=>1,
    );


    /********************************************************
     *  PARSING 
     ********************************************************/

    $iFileHeaderNo = 0;
    $iBatchHeaderNo = 0;
    $iDetailRecordNo = 0;
    $iBatchFooterNo = 0;
    $iFileFooterNo = 0;
    $iRecordNo=0;
    $iBatchID;
    $sOrderNumber=0;
    foreach($aInputFile as $iIndex => $sRecord){
        
        //echo"sRecord: \n";
        //print_r($sRecord);

        
        $sBOM = pack('H*','EFBBBF');        
        $sRecord = preg_replace("/^$sBOM/", '', $sRecord);

        switch(substr($sRecord,0,6))
            {
                case "000000":
                    $iBatchHeaderNo++;
                    $sOrderNumber= substr($sRecord,6,10);
                    $iPos = 0;
                    foreach($aFILE_HEADER_RECORD as $sKey => $iLength)
                    {
                       $aFileRecords["BATCH_HEADER"][$iBatchHeaderNo][$sKey] =  substr($sRecord, $iPos, $iLength);
                        $iPos+=$iLength;
                    } 
                    break;
                case "999999":
                    $iFileFooterNo++;
                    $iPos = 0;
                    foreach($aFILE_FOOTER_RECORD as $sKey => $iLength)
                    {
                        
                        $aFileRecords["BATCH_FOOTER"][$iBatchHeaderNo][$sKey] = substr($sRecord, $iPos, $iLength);
                        $iPos+=$iLength;
                    } 
                    break;
                default:
                        $iDetailRecordNo++;
                        $sRecordNumber = substr($sRecord,0,6);
                        $iPos = 0;
                        //$iBatchId = trim(substr($sRecord,2,30));
                        foreach($aDETAIL_RECORDS as $sKey => $iLength)
                        {
                            $aFileRecords["BATCH_HEADER"][$iBatchHeaderNo]['DETAIL RECORD'][$sRecordNumber][$sKey] = substr($sRecord, $iPos, $iLength);
                             $iPos+=$iLength;
    
                        } 
                        break;
                 }
            }
    $iNumberOfRecords = $iDetailRecordNo;

    // echo("PARSING:");
    // print_r($aFileRecords);
    return $aFileRecords;
}



function DataPrepInput($input, $inputDir, $outputDir)
{
   global $sDateStamp;
   global $sUser;
   global $BATCH_LOG_ID;
   global $sDataPrepProfile;
   global $sCustomerName;
   global $aBINs;
   global $sBIN;
   global $aShippingMethods;
   global $SHIPPING_METHOD;
   global $PRODUCT_ID;
   global $sFileExtension;
    /*DATAPREP*/
    $aDataPrepOutputData;
    //$BATCH_LOG_ID = explode("_", basename($inputDir, $sFileExtension))[count(explode("_", basename($inputDir, $sFileExtension)))-1];
    $BATCH_LOG_ID = explode("_", basename($inputDir, $sFileExtension))[1];

    
    $sFileName = basename($inputDir, $sFileExtension).".csv";
    
    $recordNo=0;


    foreach($input['BATCH_HEADER'] as $inputDetail)
    {   
     
        foreach($inputDetail['DETAIL RECORD'] as $aRecord){
                    
                        $sPAN=substr(preg_replace("/;/","",trim($aRecord['TRACK_2'])),0,16);
                        $sBIN = substr(str_replace(";","",trim($aRecord['TRACK_2'])), 0,8);
                        $SHIPPING_METHOD = trim($inputDetail['BRANCH_ID']);
                        $PRODUCT_ID = trim($inputDetail['BRANCH_ID']);
                        ++$recordNo;
                        $sTrack1 = trim($aRecord['TRACK_1']);
                        $sTrack2 = str_replace(";","",trim($aRecord['TRACK_2']));
                        //$sICVV = trim($aRecord['9']);
                        //$PSN = trim($aRecord['10']);
                        $CVC2 = trim($aRecord['IMPRINT']);
                        $sEmbName = strtoupper(trim($aRecord['EMB_NAME']));
                        $sExtendedField = trim($aRecord['EXTENDED_FORMAT_FIELDS']);
                        $sHTTP = preg_match("/\"(.*)\"/",$sExtendedField,$sQRCode);
                        $sBatchID = $BATCH_LOG_ID."/".$recordNo;
                        $sUniqueNumber= sha1($sPAN);
                        $sNotUsed1 = "0000";
                        $sNotUsed2 = "00";
                        $sNotUsed3 = "000";
                        $sDataPrepProfile =  $aBINs[$sBIN][trim($PRODUCT_ID)]['Profile'];
                        $sNotUsed4 = "0000000";
                        $sChipData = "$sTrack1#$sTrack2#$CVC2#$sEmbName#$sQRCode[0]";
                        
                        //DATAPREP RESULT
                        $aDataPrepOutputData[$SHIPPING_METHOD][($aBINs[$sBIN][trim($PRODUCT_ID)]["Product"])][]=array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData);
                    }
    }

    $sCustomerName = $aBINs[$sBIN]['Customer'];
    
    echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n";
    echo "$sDateStamp [$sUser]: BIN: $sBIN \n";
    echo "\n$sDateStamp [$sUser]: DATAPREP START \n";
 
    foreach($aDataPrepOutputData as $keyShipment => $aShippingRecord)     
    {
        $sShippingName = $aShippingMethods[0][$keyShipment];
        foreach($aShippingRecord as $keyProduct => $aProductRecord)
        {

            $sProductName = $keyProduct;
            echo "$sDateStamp [$sUser]: DataPrep: Records per product $sProductName and shipment method $sShippingName: ".count($aProductRecord)."\n";
            $bFileWriting1; 

            $maxRec = 500;
            $numSplits = 0;
            $recordsDone = 0;
            $fp = null;
            $neededSplits = 0;
            if(count($aProductRecord)>$maxRec)
            {
                $neededSplits = ceil(count($aProductRecord) / $maxRec);
            }
        
                foreach ($aProductRecord as $row) 
                { 
                    if($recordsDone == $maxRec)
                    $recordsDone = 0;
                    if($recordsDone == 0)
                    {
                        if($numSplits > 0)
                            fclose($fp);
                        ++$numSplits;
                        $sDataPrepOutputFile = $outputDir."MS_CHIPREP_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_";
                        if($neededSplits > 0)
                            $sDataPrepOutputFile = $sDataPrepOutputFile.$numSplits."_of_".$neededSplits."_";
                        $sDataPrepOutputFile = $sDataPrepOutputFile.$sFileName;
                        echo "$sDateStamp [$sUser]: Writing DataPrep Input file to $sDataPrepOutputFile \n";

                        $fp = fopen($sDataPrepOutputFile, "w");
                    }
                    $bFileWriting1 = fwrite($fp, implode(';',$row)).fwrite($fp, "\r\n");
                    $aFilesWritingStatus[] = $bFileWriting1;
                    $recordsDone++;
                } 
                    if($bFileWriting1)
                    {
                        echo "$sDateStamp [$sUser]: File for batch #: $BATCH_LOG_ID succesfully written.\n";
                        fclose($fp);
                    }
                    else 
                    {
                        echo "$sDateStamp [$sUser]: Writing file for batch $BATCH_LOG_ID failed\n";
                        fclose($fp);     
                    }
                    foreach($aFilesWritingStatus as $bFileStatus)
                    {
                        if(!$bFileStatus)
                        {
                            return false;                 
                        }
                    }
        }
    }
        return true;
         
   
}

function MailingInput($input, $inputDir, $outputDir)
{
    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
    global $BATCH_LOG_ID;
    global $aBINs;
    global $sBIN;
    global $aShippingMethods;
    global $sMailOutputDir;
    global $sFedexOutputDir;
    global $sFileExtension;

    $sFileName = basename($inputDir,$sFileExtension).".csv";
        
            /*MAILING*/
            $aMailShippingOutputData;
            $iRecordNo = 0;
            $inputDetail =$input;
            foreach($input['BATCH_HEADER'] as $inputDetail)
            {   
             
                foreach($inputDetail['DETAIL RECORD'] as $aRecord){
                                ++$iRecordNo;
                                $sPAN=substr(preg_replace("/;/","",trim($aRecord['TRACK_2'])),0,16);
                                $SHIPPING_METHOD = trim($inputDetail['BRANCH_ID']);
                                $PRODUCT_ID = trim($inputDetail['BRANCH_ID']);
                                $FullName = trim($aRecord['MAIL_TO_ADDRESS_1']);
                                $Company = "";
                                $Address1 = trim($aRecord['MAIL_TO_ADDRESS_2']);
                                $Address2 = trim($aRecord['MAIL_TO_ADDRESS_3']);
                                $City = trim($aRecord['MAIL_TO_ADDRESS_4']);
                                $State =  "";
                                $ZIPCode = substr(trim($aRecord['MAIL_TO_ZIP']),0,4);
                                $ZIPCodeAddOn = empty(substr($aRecord['MAIL_TO_ZIP'],5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['MAIL_TO_ZIP']),5));
                                $Country = "";
                                $EmailAddress;
                                $FromFullName;
                                $FromFullName = "TagSystems USA";
                                $FromAddress1 = "80 Corbett Way";
                                $FromAddress2;
                                $FromCity ="Eatontown";
                                $FromState= "NJ";
                                $FromCountry = "US";
                                $FromZIPCode = "07724";                
                                $Amount;
                                $ServiceType = "US-FC"; //Standard Overnight -> FEDEX, 
                                $PackageType = $aBINs[$sBIN][$PRODUCT_ID]["PackageType"];
                                $WeightOz = $aBINs[$sBIN][$PRODUCT_ID]["WeightOz"]; 
                                $ShipDate = "+1";
                                $ImageType = "Pdf";
                                $Reference1 = $sCustomerName."_".$BATCH_LOG_ID."_".$iRecordNo."_".substr($sPAN, -4);
                                $Reference2 = strtoupper(hash("sha256", $sPAN, false));

                                $aMailShippingOutputData[$SHIPPING_METHOD][($aBINs[$sBIN][trim($PRODUCT_ID)]["Product"])][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz,$ShipDate, $ImageType, $Reference1, $Reference2);                
                            }
            }

            echo "\n$sDateStamp [$sUser]: MAILING START \n";
        foreach($aMailShippingOutputData as $keyShipment => $aShippingRecord)     
        {
        
            $sShippingName = $aShippingMethods[0][$keyShipment];
            if(preg_match("/FEDx(.*)/", $sShippingName) == 1)
            {
                $outputDir = $sFedexOutputDir;
            }
            else
            {
                $outputDir = $sMailOutputDir;
            }
            foreach($aShippingRecord as $keyProduct => $aProductRecord)
            {
                $sProductName = $keyProduct;
                echo "$sDateStamp [$sUser]: Mailing: Records per product $sProductName and shipment method $sShippingName: ".count($aProductRecord)."\n";
                $bFileWriting1; 

                $maxRec = 500;
                $numSplits = 0;
                $recordsDone = 0;
                $fpm = null;
                $neededSplits = 0;

                if(count($aProductRecord)>$maxRec)
                {
                    $neededSplits = ceil(count($aProductRecord) / $maxRec);
                }
             
                    foreach ($aProductRecord as $row) 
                    { 
                    
                        if($recordsDone == $maxRec)
                        $recordsDone = 0;
                       if($recordsDone == 0)
                        {
                            if($numSplits > 0)
                                fclose($fpm);
                            ++$numSplits;
                            $sDataPrepOutputFile =  $outputDir."MAIL_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_";
                            if($neededSplits > 0)
                                $sDataPrepOutputFile = $sDataPrepOutputFile.$numSplits."_of_".$neededSplits."_";
                            $sDataPrepOutputFile = $sDataPrepOutputFile.$sFileName;
                            echo "$sDateStamp [$sUser]: Writing Mail Input file to $sDataPrepOutputFile \n";
                            $fpm = fopen($sDataPrepOutputFile, "w");
                            fwrite($fpm, implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1", "Reference2"))).fwrite($fpm, "\r\n");
                        }

                        $bFileWriting1 =fwrite($fpm, implode("\t",$row)).fwrite($fpm, "\r\n");
                        $aFilesWritingStatus[] = $bFileWriting1;
                        $recordsDone++;
                    } 
                        if($bFileWriting1)
                        {
                            echo "$sDateStamp [$sUser]: File for batch #: $BATCH_LOG_ID succesfully written.\n";
                            fclose($fpm);
                        }
                        else 
                        {
                            echo "$sDateStamp [$sUser]: Writing file for batch $BATCH_LOG_ID failed\n";
                            fclose($fpm);
                        }
                        foreach($aFilesWritingStatus as $bFileStatus)
                        {
                            if(!$bFileStatus)
                            {
                                return false;
                            }
                        }
            }
        }         
        return true;
}


echo "$sDateStamp [$sUser]: Ending Script";

?> 
