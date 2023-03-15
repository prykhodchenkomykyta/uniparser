<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 10/20/2020
Revision: 01/31/2022
Name: Radovan Jakus
Version: 1.4
Notes: Added FromAddress2 and Reference4. Shipping Methods were updated to FedEx_OR. 
******************************/

/*Production Environment*/
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/fiserv/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sBulkOutputDir = "/var/TSSS/Files/USPS/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sFedexOutputDir = "/var/TSSS/Files/FEDEX/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/fiserv/";


// $sInputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Fiserv\\in\\";
// $sOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Fiserv\\out\\";
// $sBulkOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Fiserv\\out\\MAIL\\";
// $sMailOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Fiserv\\out\\MAIL\\";
// $sFedexOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Fiserv\out\\FEDEX\\";
// $sShipsiOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Fiserv\\out\\";
// $sProcessedDir = "W:\Workspace\\TagSystem\\Parser_Plugin\\Fiserv\\in\\";


$sDataPrepProfile;
$sCustomerName;
$iNumberOfRecords;
$sBIN;
$aShippingMethods[] = array(    
                                "01"=> "FEDx_OR",
                                "02"=> "FEDx",
                            );

$aBINs['553742']=array("Customer"=>"STIFEL",
                        "0008-010-MCSILV"=> array(
                            "Profile"=>"P_STIFEL_MC_20_94_NXP_P71_V01_v143",
                            "Product"=>"SLV",
                            "PackageType"=>"Package",
                            "WeightOz" => 1),
                        "0008-010-MCROSE"=> array(
                            "Profile"=>"P_STIFEL_MC_20_94_NXP_P71_V01_v143",
                            "Product"=>"ROSE",
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
    
    //echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";

    $sFileName = basename($inputDir);
    $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);

     
    
    $aFILE_HEADER_RECORD = array(
        "RECORD_TYPE_ID" => 2,
        "BATCH_ID" => 6,
        "PRODUCT_ID" => 15,
        "SHIPPING_METHOD"=>2,
        "FILLER"=>868,
    );    

    $aDETAIL_RECORDS = array (
        "RECORD_TYPE_ID" => 2,
        "CARD_ID"=>16,
        "CARDHOLDER_NAME"=>26,
        "PAN" => 19,
        "EXP_DATE"=>5,
        "FNAME"=>20,
        "LNAME"=>20,
        "TRACK_1"=>77,
        "TRACK_2" => 38,
        "CVV2" => 3,
        "ICVV" =>3,
        "PIN" => 32,
        "COMPANY_NAME" => 50,
        "MAIL_NAME" => 50,
        "MAIL_TO_ADDRESS_1" => 100,
        "MAIL_TO_ADDRESS_2" => 100,
        "MAIL_CITY" => 85,
        "MAIL_STATE" => 85,
        "MAIL_ZIP" => 10,
        "SHIP_COUNTRY"=>60,
        "CARRIER_MSG"=>100,
        "FILLER"=>100,
    );

    $aFILE_FOOTER_RECORD = array(
        "RECORD_TYPE_ID" => 2,
        "TOTAL_RECORDS"=>7,
        "FILLER"=>191,
    );


    /********************************************************
     *  PARSING 
     ********************************************************/

    $iFileHeaderNo = 0;
    $iBatchHeaderNo = 0;
    $iBatchFooterNo = 0;
    $iFileFooterNo = 0;
    $iRecordNo=0;
    $iBatchID;
    $sOrderNumber=0;
    foreach($aInputFile as $iIndex => $sRecord){
        
        $sBOM = pack('H*','EFBBBF');        
        $sRecord = preg_replace("/^$sBOM/", '', $sRecord);

        switch(substr($sRecord,0,2))
            {
                case "BH":
                    $iBatchHeaderNo++;
                    $BATCH_LOG_ID = trim(substr($sRecord,3,6));
                    $PRODUCT_ID = trim(substr($sRecord,9,15));
                    $SHIPPING_METHOD = trim(substr($sRecord,24,2));
                    $iPos = 0;
                    
            
                    foreach($aFILE_HEADER_RECORD as $sKey => $iLength)
                    {
                       $aFileRecords["BATCH_HEADER"][$BATCH_LOG_ID][$PRODUCT_ID][$SHIPPING_METHOD][$sKey] =  substr($sRecord, $iPos, $iLength);
                        $iPos+=$iLength;
                    } 
                    break;
                case "DT":
                    $iRecordNo++;
                    $iPos = 0;
                    foreach($aDETAIL_RECORDS as $sKey => $iLength)
                    {
                        
                        $aFileRecords["BATCH_HEADER"][$BATCH_LOG_ID][$PRODUCT_ID][$SHIPPING_METHOD]["DETAIL_RECORD"][$iRecordNo][$sKey] = substr($sRecord, $iPos, $iLength);
                        $iPos+=$iLength;
                    } 
                    break;
                case "BF":
                    $iFileFooterNo++;
                    $iPos = 0;
                    foreach($aFILE_FOOTER_RECORD as $sKey => $iLength)
                    {
                        
                        $aFileRecords["BATCH_FOOTER"][$sKey] = substr($sRecord, $iPos, $iLength);
                        $iPos+=$iLength;
                    } 
                    break;
                 }
            }
		$iNumberOfRecords = $iRecordNo;

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
    $sFileName = basename($inputDir, $sFileExtension).".csv";

    
    
    
    
    $inputDetail = $input;
    foreach($input['BATCH_HEADER'] as $ShipmentSplit)
    {
        foreach($ShipmentSplit as $ProductSplit){
        
            foreach($ProductSplit as $aBatch){
                $recordNo=0;
                $BATCH_LOG_ID = $aBatch['BATCH_ID'];

                foreach($aBatch['DETAIL_RECORD'] as $aRecord)
                {   
            
                    ++$recordNo;
                    $sBIN = substr(trim(str_replace(" ","",$aRecord['PAN'])), 0,6);
                    $sPAN = trim(str_replace(" ","",$aRecord['PAN']));
                    $SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD']);
                    $PRODUCT_ID = trim($aBatch['PRODUCT_ID']);
                    $sTrack1 = (substr(trim($aRecord['TRACK_1']),0,1)=="B") ?  trim($aRecord['TRACK_1']) : "B".trim($aRecord['TRACK_1']);
		    $sTrack2 =str_replace(";","", trim($aRecord['TRACK_2']));
                    $sICVV = trim($aRecord['ICVV']);
                    $PSN = trim(substr($aRecord['TRACK_2'],25,2));
                    $CVC2 = trim($aRecord['CVV2']);
                    $sEmbName = strtoupper(trim($aRecord['CARDHOLDER_NAME']));
                    $sCompanyName = trim($aRecord['COMPANY_NAME']);
                    $sBatchID = $BATCH_LOG_ID."/".$recordNo;
                    $sUniqueNumber= sha1($sPAN);
                    $sNotUsed1 = "0000";
                    $sNotUsed2 = "00";
                    $sNotUsed3 = "000";
                    $sDataPrepProfile =  $aBINs[$sBIN][trim($PRODUCT_ID)]['Profile'];
                    $sNotUsed4 = "0000000";
                    $sChipData = "$sTrack1#$sTrack2#$sICVV#$PSN#$CVC2#$sEmbName#$sCompanyName";
                    
                    //DATAPREP RESULT
                    $aDataPrepOutputData[$BATCH_LOG_ID][$SHIPPING_METHOD][($aBINs[$sBIN][trim($PRODUCT_ID)]["Product"])][]=array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData);
                }
            }
        }
    } 
   //echo "DATAPREP:";
   //print_r($aDataPrepOutputData);
   // $sBIN = substr(trim($aRecordData[0][1]), 0,6);
    $sCustomerName = $aBINs[$sBIN]['Customer'];
    
    echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n";
    echo "$sDateStamp [$sUser]: BIN: $sBIN \n";
    echo "$sDateStamp [$sUser]: DATAPREP START \n";

    foreach($aDataPrepOutputData as $BATCH_LOG_ID => $BATCHDATA){

            foreach($aDataPrepOutputData[$BATCH_LOG_ID] as $keyShipment => $aShippingRecord)     
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
    global $sFileExtension;
    global $sFedexOutputDir;
    global $sMailOutputDir;

    $sFileName = basename($inputDir,$sFileExtension).".csv";
        
            /*MAILING*/
            $aMailShippingOutputData;
            $inputDetail = $input;

    foreach($input['BATCH_HEADER'] as $ShipmentSplit)
    {
        foreach($ShipmentSplit as $ProductSplit){
        
                foreach($ProductSplit as $aBatch){
                    $iRecordNo=0;
                    $BATCH_LOG_ID = $aBatch['BATCH_ID'];
                
                            foreach($aBatch['DETAIL_RECORD'] as $aRecord)
                            {   
                                ++$iRecordNo;
                                $sBIN = substr(trim(str_replace(" ","",$aRecord['PAN'])), 0,6);
                                $sPAN = trim(str_replace(" ","",$aRecord['PAN']));
                                $SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD']);
                                $PRODUCT_ID = trim($aBatch['PRODUCT_ID']);
                                $FullName = trim($aRecord['MAIL_NAME']);
                                $Company = empty($aRecord['COMPANY_NAME']) ? "" : trim($aRecord['COMPANY_NAME']);
                                $Address1 = trim($aRecord['MAIL_TO_ADDRESS_1']);
                                $Address2 = trim($aRecord['MAIL_TO_ADDRESS_2']);
                                $City = trim($aRecord['MAIL_CITY']);
                                $State =  trim($aRecord['MAIL_STATE']); 
                                $ZIPCode = substr(trim($aRecord['MAIL_ZIP']),0,5);
                                $ZIPCodeAddOn = empty(substr(trim($aRecord['MAIL_ZIP']),5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['MAIL_ZIP']),5));
                                $Country = (preg_match("/(USA)/",trim($aRecord['SHIP_COUNTRY'])))? "US":trim($aRecord['SHIP_COUNTRY']);
                                $EmailAddress;
                                $FromFullName;
                                $FromFullName = "Stifel Bank";
                                $FromAddress1 = "P.O. Box 771470";
                                $FromAddress2 = "";
                                $FromCity ="St. Louis";
                                $FromState= "MO";
                                $FromCountry = "US";
                                $FromZIPCode = "63177";                
                                $Amount;
                                $ServiceType = "US-FC"; //Standard Overnight -> FEDEX,
                                $PackageType = $aBINs[$sBIN][$PRODUCT_ID]["PackageType"];
                                $WeightOz = $aBINs[$sBIN][$PRODUCT_ID]["WeightOz"]; 
                                //$PackageType = "Large Envelope or Flat";
                                $ShipDate = "+1";
                                $ImageType = "Pdf";
                                $Reference1 = $sCustomerName."_".$BATCH_LOG_ID."_".$iRecordNo."_".substr($aRecord['PAN'], -4);
                                $Reference2 = strtoupper(hash("sha256",$aRecord['PAN'], false));
                                $Reference3 = trim($aRecord['CARD_ID']);
                                $Reference4 = "";

                            /* switch($aRecord['29'])
                                {
                                    case 1:
                                        $ServiceType = "US-FC";
                                    // $aMailShippingOutputData[] = array($FullName, $Address1, $Address2,$City,$State,$ZIPCode, $Country, $ServiceType, $ShipDate, $ImageType, $Reference1);
                                        break;
                                    case 2:
                                        $ServiceType = "US-PM";
                                        //$aMailShippingOutputData[] = array($FullName, $Address1, $Address2,$City,$State,$ZIPCode, $Country, $ServiceType, $ShipDate, $ImageType, $Reference1);
                                        break;   
                                    case 4: 
                                        $bBulk = true; 
                                        break;
                                }*/
                                //MAILING RESULT
                                $aMailShippingOutputData[$BATCH_LOG_ID][$SHIPPING_METHOD][($aBINs[$sBIN][trim($PRODUCT_ID)]["Product"])][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz,$ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);
                            // $aMailShippingOutputData[$SHIPPING_METHOD][($aBINs[$sBIN][trim($PRODUCT_ID)]["Product"])][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $ShipDate,$ImageType, $Reference1, $Reference2);
                
                    
                            }
                }
            }
        }

        echo "$sDateStamp [$sUser]: MAILING START \n";
     foreach($aMailShippingOutputData as $BATCH_LOG_ID => $BATCHDATA){

        foreach($aMailShippingOutputData[$BATCH_LOG_ID] as $keyShipment => $aShippingRecord)     
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
                             fwrite($fpm, implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State",
                              "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity",
                              "FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate",
                               "ImageType", "Reference1", "Reference2","Reference3","Reference4"))).fwrite($fpm, "\r\n");
                             //fwrite($fpm, implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1"))).fwrite($fpm, "\r\n");

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
    }    
        return true;
}


echo "$sDateStamp [$sUser]: Ending Script";

?> 
