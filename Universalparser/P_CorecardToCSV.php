<?php 
/******************************
Author: Jean-Eric Pierre-Louis
Company: Pierre & Rady LLC
Date: 10/20/2020
Revision: 03/24/2022
Name: Radovan Jakus
Version: 1.29
Notes: Adding Power
******************************/

/*Production Environment*/
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/corecard/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sFedexOutputDir = "/var/TSSS/Files/FEDEX/";
$sMergeMailOutputDir = "/var/TSSS/Files/MAILMERGE/";
$sMailMergeBadDataOutputDir = "/var/TSSS/Files/MAILMERGE/BAD_DATA/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/corecard/";
$SerialNumberLocal ="/home/erutberg/Radovan/SerialNumberCounter.csv";


// $sInputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Credit\\In\\";
// //$sOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Credit\\out\\";
// $sOutputDir = "D:\\Production Data\\in\\";
// $sMailOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Credit\\out\\STAMPS\\";
// $sFedexOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Credit\\out\\FEDEX\\";
// $sMergeMailOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Credit\\out\\MAILMERGE\\";
// $sMailMergeBadDataOutputDir = "D:\\Workspace\\TagSystem\Parser_Plugin\\Corecard_Credit\\out\\MAILMERGE\\BAD_DATA\\";
// $sProcessedDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Credit\\In\\";   
// $SerialNumberLocal ="D:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Credit\\SerialNumberCounter.csv";


 //Mailer Information
 $BarcodeID = "00";
 $ServiceTypeID = "270";
 $MailerID = "902695246";
 $maxRec = 1000;
 $SerialNumberOfDigits = (strlen($MailerID)==9)? 6 : 9;


$BATCH_LOG_ID;
$sDataPrepProfile;
$sCustomerName;
$iNumberOfRecords;
$sBIN;
$SHIPPING_METHOD;
$PRODUCT_ID;
$sFileExtension =".txt";
$bIsExtendedBINused = false;

$aShippingMethods[] = array(    
                                "00"=> "USPS_TR", //USPS w/ Tracking
                                "01"=> "FEDx_OR", // FedEx One Rate
                                "02"=> "FEDx_SON", //FedEx Standard Overnight
                                "03"=> "USPS", //USPS w/o Tracking
                                "04"=> "USPS_PM", //USPS Priority Mail
                                "05"=> "FEDx_INT",
                            );

$aBINs['547018']=array("Customer"=>"GEMINI",
                        "54701801"=> array(
                            "NA" => array(
                                "Profile"=>"P_GEMINI_MC_20_94_NXP_P71_V01_v143",
                                "Product"=>"BLK")),
                        "54701803"=> array(
                           "NA"  => array(
                                "Profile"=>"P_GEMINI_MC_20_94_NXP_P71_V01_v143",
                                "Product"=>"SLV")),
                        "54701802"=> array(
                            "NA" => array(
                                "Profile"=>"P_GEMINI_MC_20_94_NXP_P71_V01_v143",
                                "Product"=>"RSG")),
                        "54701804"=> array(
                            "NA" => array(
                                "Profile"=>"P_GEMINI_MC_20_94_NXP_P71_V01_v143",
                                "Product"=>"LTD")));

$aBINs['519369']=array("Customer"=>"MERCANTILE",
                        "519369001"=> array(
                            "a861446728" => array(
                                "Profile"=>"P_MERCANTILE_MC_20_94_ST_TOPAZ_123_V01_v143",
                                "Product"=>"M-AOA"),
                            "b861446728" => array(
                                    "Profile"=>"P_MERCANTILE_MC_20_94_ST_TOPAZ_123_V01_v143",
                                    "Product"=>"M-CHRONO"),
                            "c861446728" => array(
                                    "Profile"=>"P_MERCANTILE_MC_20_94_ST_TOPAZ_123_V01_v143",
                                    "Product"=>"M-MERC"),
                            "d861446728" => array(
                                "Profile"=>"P_MERCANTILE_MC_20_94_ST_TOPAZ_123_V01_v143",
                                "Product"=>"M-OAA"),
                            "e861446728" => array(
                                "Profile"=>"P_MERCANTILE_MC_20_94_ST_TOPAZ_123_V01_v143",
                                "Product"=>"M-DSN"),
                            "f861446728 " => array(
                                "Profile"=>"P_MERCANTILE_MC_20_94_ST_TOPAZ_123_V01_v143",
                                "Product"=>"M-ADA"),
                           ));

$aBINs['52403218']=array("Customer"=>"POWER",
                           "524032182"=> array(
                               "NA" => array(
                                   "Profile"=>"P_POWER_MC_20_94_ST_TOPAZ_12_V01_BIN8_v143",
                                   "Product"=>""),
                              ));
   

                                
                                    

$aBINs['411172']=array("Customer"=>"GLIDE",
                        "519369001"=> array(
                            "NA" => array(
                                "Profile"=>"P_GLIDE_VISA_D1_ST_TOPAZ_123_V01_v143",
                                "Product"=>"")));

   
                                    


ob_start();
ini_set("auto_detect_line_endings", true);
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
            $aParsedData = Parser($sInputFilePath);
            $bDataProcessed = DataPrepInput($aParsedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
            $bMailProcessed = MailingInput($aParsedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
            $bMailMergeProcessed = MailingMergeInput($aParsedData, $sInputFilePath, $sMergeMailOutputDir, $sProcessedDir);
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
        $aParsedData = Parser($sInputFilePath);
        $bDataProcessed = DataPrepInput($aParsedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
        $bMailProcessed = MailingInput($aParsedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
        $bMailMergeProcessed = MailingMergeInput($aParsedData, $sInputFilePath, $sMergeMailOutputDir, $sProcessedDir);
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
                //echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n";
                 $aParsedData = Parser($sInputFilePath);
                 $bDataProcessed = DataPrepInput($aParsedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
                 $bMailProcessed = MailingInput($aParsedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
                 $bMailMergeProcessed = MailingMergeInput($aParsedData, $sInputFilePath, $sMergeMailOutputDir, $sProcessedDir);
                
                 {
                    $sProcessedFilename = basename($sInputFilePath);
                    $bFileMoved = rename($sInputFilePath , $sProcessedDir.$sProcessedFilename);
                    if($bFileMoved)
                    {
                        echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sProcessedFilename \n";
                        echo "$sDateStamp [$sUser]: Total Number of Records: $iNumberOfRecords in file: $sProcessedFilename  \n"; 
                        getDetailOverview($bMailMergeProcessed);
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
    global $sSerialNumberurl;
    global $SerialNumberLocal; 
    global $SerialNumberOfDigits;
 

    /*SERIAL NUMBER*/
    $SerialNumber = getSerialNumber($SerialNumberLocal,$SerialNumberOfDigits);
    $SerialNumber = str_pad($SerialNumber,$SerialNumberOfDigits,'0',STR_PAD_LEFT);        
    

    $sFileName = basename($inputDir);
    
    $aInputFile = file($inputDir,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

 

    echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";
   // echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n\n";

     
    echo "$sDateStamp [$sUser]: Starting Parsing Data\n";
    $aRecordData = array();

    // echo"aInputFile";
    // print_r($aInputFile);

    foreach($aInputFile as $aData)
    {
        if(strlen($SerialNumber)>$SerialNumberOfDigits)
        {
            $SerialNumber = 1;
        }
        $aInputFileData =str_getcsv($aData);
        $aInputFileData['SerialNumber'] =  str_pad($SerialNumber++,$SerialNumberOfDigits,'0',STR_PAD_LEFT);  ;
        $aRecordData[] =$aInputFileData;
    }   
  
    
    setSerialNumber($SerialNumberLocal,$SerialNumberOfDigits,$SerialNumber);
    return  $aRecordData;
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
   global $iNumberOfRecords;
   global $bIsExtendedBINused;
    /*DATAPREP*/
    $aDataPrepOutputData;
    $BATCH_LOG_ID = $input[0][1];
    
    $sFileName =  preg_replace("/(TAG-|tag-)|(CreditOutgoingEmbossing_)/","",basename($inputDir,$sFileExtension));  
    
    $recordNo=0;

    // echo"input";
    // print_r($input);

    $inputDetail = array_slice($input, 1, -1);
    // echo"inputDetail";
    // print_r($inputDetail);
    foreach($inputDetail as $aRecord)
    {   
       //echo"aRecord";
        //print_r($aRecord);
        $sBIN = substr(trim($aRecord['2']), 0,6);
        $sBINExtended = substr(trim($aRecord['2']), 0,8);
        if(isset($aBINs[$sBINExtended]))
        {
            $sBIN =  $sBINExtended;
        }
        $SHIPPING_METHOD = trim($aRecord['38']);
	    foreach($aBINs[$sBIN] as $ProductID=> $ProductIDarray){
                if(empty(trim($aRecord['12'])) || trim($aRecord['12']) != $ProductID)
                {
                    $PRODUCT_ID = trim($input[0][4]); 
                }
                else
                {
                    $PRODUCT_ID = trim($aRecord['12']);
                    break;
                }
            }

             //$MAILER_ID = "NA";
             if($sBIN != "519369")
             {
                 $MAILER_ID = "NA"; 
             }
             else
             {
                 $MAILER_ID = trim($aRecord['39']); 
                 //echo"MAILEEER ID: $MAILER_ID, ".empty(trim($aRecord['39'])).",".(preg_match('/\|/', trim($aRecord['43']))).",".((preg_match('/\|/', trim($aRecord['43'])) && (empty(trim($aRecord['39'])))))."\n";
                 if((preg_match('/\|/', trim($aRecord['43'])) && (empty(trim($aRecord['39'])))))
                 {
                
                     $aField44 = explode('|', $aRecord['43']);
                     //MERCHANG LOGO
                     $MAILER_ID = $aField44['1'];
                 }
                    
             }
          
          //print_r($aBINs);
          $ProductProp = $aBINs[$sBIN][$PRODUCT_ID][$MAILER_ID];
          //print_r($ProductProp);


        //$PRODUCT_ID = empty(trim($aRecord['12'])) ? trim($input[0][4]) : trim($aRecord['12']);
        ++$recordNo;
        $sTrack1 = trim($aRecord['37']);
        $sTrack2 = trim($aRecord['24']);
        $sTrack2Chip = trim($aRecord['41']);
        $PSN = trim($aRecord['3']);
        $CVC2 = trim($aRecord['17']);
        $sEmbName = ($sBIN == "547018") ? capitalizeName($aRecord['6']) :trim($aRecord['6']);
        $sBatchID = $BATCH_LOG_ID."/".$recordNo;
	    $sUniqueNumber= sha1(substr($sTrack2,0,16));      
 // $sUniqueNumber= "dc5413d196ee7fcb80f0eeb97e79cfd60b1e54cf";
        $sNotUsed1 = "0000";
        $sNotUsed2 = "00";
        $sNotUsed3 = "000";
        $sDataPrepProfile =  $ProductProp['Profile'];
        $sNotUsed4 = "0000000";
        $sChipData = "$sTrack1#$sTrack2#$sTrack2Chip#$PSN#$CVC2#$sEmbName";
        if($sBIN =="519369" || $sBIN =="411172")
        {
            $sQRCode = trim($aRecord['32']);
            $sLogo = trim($aRecord['39']);
            $sBusinessNameOnCard = trim($aRecord['43']);
            if(preg_match('/\|/', $sBusinessNameOnCard))
            {
                //BusinessName|DistributionPartnersLogo|ContactNumber|WeekDays hours|Weekend hours|SiteURL|CardDescription
                $aField44 = explode('|', $aRecord['43']);
                //BUSINESS NAME ON CARD
                if(!isset($aField44['0']))
                {
                    $sBusinessNameOnCard = '';

                }
                else
                {
                    $sLogo = $aField44['0'];
                }

                if(!isset($aField44['1']))
                {
                    $sLogo = trim($aRecord['39']);

                }
                else
                {
                    $sLogo = $aField44['1'];
                }
                
            }

            $sTrack3 = $sLogo."|".$sQRCode."|".$sBusinessNameOnCard; 
            $sChipData = "$sTrack1#$sTrack2#$sTrack2Chip#$PSN#$CVC2#$sEmbName#$sTrack3";
        }
        
        //DATAPREP RESULT
        $aDataPrepOutputData[$SHIPPING_METHOD][($ProductProp["Product"])][]=array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData);
    }
        $sCustomerName=$aBINs[$sBIN]["Customer"];
        echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n";
        $iNumberOfRecords = $recordNo;
        echo "$sDateStamp [$sUser]: Total Number of Records: $iNumberOfRecords \n\n"; 
   
    foreach($aDataPrepOutputData as $keyShipment => $aShippingRecord)     
    {
        $sShippingName = $aShippingMethods[0][$keyShipment];
        
        foreach($aShippingRecord as $keyProduct => $aProductRecord)
        {
            $sProductName = $keyProduct;
            $sDataPrepOutputFile = $outputDir."MS_CHIPREP_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_".$sFileName.".csv";
            echo "$sDateStamp [$sUser]: Writing DataPrep Input file to $sDataPrepOutputFile \n";
            $fp = fopen($sDataPrepOutputFile, "w");
            $bFileWriting1; 
        
                foreach ($aProductRecord as $row) 
                { 
                    $bFileWriting1 = fwrite($fp, implode(';',$row)).fwrite($fp, "\r\n");
                    $aFilesWritingStatus[] = $bFileWriting1;
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
    global $sFileExtension;
    global $sFedexOutputDir;
    global $sMailOutputDir;

    $sFileName = basename($inputDir,$sFileExtension);
        
            /*MAILING*/
            $aMailShippingOutputData;
            $iRecordNo = 0;
            $inputDetail = array_slice($input, 1, -1);
            foreach($inputDetail as $aRecord)
            {
                ++$iRecordNo;
                $sPAN = trim($aRecord['2']);
                $SHIPPING_METHOD = trim($aRecord['38']);
                $PRODUCT_ID = "";
                foreach($aBINs[$sBIN] as $ProductID=> $ProductIDarray){
                    if(empty(trim($aRecord['12'])) || trim($aRecord['12']) != $ProductID)
                    {
                            $PRODUCT_ID = trim($input[0][4]); 
                    }
                     else
                    {
                            $PRODUCT_ID = trim($aRecord['12']);
                            break;
                    }
         	    }
                //$MAILER_ID = "NA";
                if($sBIN != "519369")
                {
                    $MAILER_ID = "NA"; 
                }
                else
                {
                    $MAILER_ID = trim($aRecord['39']); 
                    if((preg_match('/\|/', trim($aRecord['43'])) && (empty(trim($aRecord['39'])))))
                    {
                        $aField44 = explode('|', $aRecord['43']);
                        //MERCHANG LOGO
                        $MAILER_ID = $aField44['1'];
                    }
                       
                }
                
                $ProductProp = $aBINs[$sBIN][$PRODUCT_ID][$MAILER_ID];


                $FullName = trim($aRecord['8']).trim($aRecord['9'])." ".trim($aRecord['10']);
                $Company = "";
                $Address1 = trim($aRecord['25']);
                $Address2 = trim($aRecord['26']);
                $City = trim($aRecord['27']);
                $State =  trim($aRecord['28']); 
                if($sBIN == "547018")
                {
                    $FullName = ucwords(strtolower($FullName));
                    $Address1 = ucwords(strtolower($Address1));
                    $Address2 = ucwords(strtolower($Address2));
                    $City = ucwords(strtolower($City));
                }
                $ZIPCode = substr(trim($aRecord['30']),0,5);
                $ZIPCodeAddOn = empty(substr(trim($aRecord['30']),5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['30']),5));
                $Country = (trim($aRecord['29']=="USA"))? "US":trim($aRecord['29']);
                $EmailAddress;
                $FromFullName = "";
                $FromAddress1 = "80 Corbett Way";
                $FromAddress2 = "";
                $FromCity ="Eatontown";
                $FromState= "NJ";
                $FromCountry = "US";
                $FromZIPCode = "07724";                
                $Amount;
                $ServiceType = "US-FC"; //With the combination of this Service & Package Type, the default will be USPS_TR (First Class with Tracking)             
                $PackageType = "Package";
                $WeightOz = "1";
                $ShipDate = "+1";
                $ImageType = "Pdf";
                $Reference1 = substr($sCustomerName,0,3)."_".trim($aRecord['SerialNumber'])."_".substr($aRecord['2'], -4);
                $Reference2 = strtoupper(hash("sha256",$sPAN, false));
                $Reference3 = trim($aRecord['11'])."|".trim($aRecord['35']);
                $Reference4 = trim($aRecord['32']);

                switch($aShippingMethods[0][$aRecord['38']])
                {
                    case "USPS_TR":
                        $ServiceType = "US-FC";
                        $PackageType =  "Package";
                        break;
                        // USPS First Class with Tracking 
                    case "FEDx_OR":
                        $ServiceType = "FEDx_OR";
                        $PackageType = "Package";
                        break;  
                        // FedEx One-Rate
                    case "FEDx_SON":
                        $ServiceType = "FEDx_SON";
                        $PackageType = "Package"; 
                        break; 
                        // FedEx Standard Overnight
                    case "USPS":
                        $ServiceType = "US-FC";
                        $PackageType = "Letter"; 
                        break; 
                        // USPS First Class
                    case "USPS-PM":
                        $ServiceType = "US-PM";
                        $PackageType = "Large Envelope or Flat"; 
                        break; 
                        // USPS Priority Mail
                    case 5: 
                        $bBulk = true; 
                        break;
                }


                //MAILING RESULT
                
                $aMailShippingOutputData[$SHIPPING_METHOD][($ProductProp['Product'])][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode, $ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1,$FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);
                $aMailShippingOutputDataHeader = implode("\t", array("Company", "FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn", "Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3", "Reference4"."\r\n"));

                //echo "SHIPPING METHODS";
                //print_r( $aMailShippingOutputData);
            }

        $fp;
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
                $sMailOutputFile = $outputDir."MAIL_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_".$sFileName.".csv";
                echo "$sDateStamp [$sUser]: Writing Mail Input file to $sMailOutputFile \n";
                $fp = fopen($sMailOutputFile, "w");
                $bFileWriting1; 
                fwrite($fp, $aMailShippingOutputDataHeader);
                    foreach ($aProductRecord as $row) 
                    { 
                        $bFileWriting1 =fwrite($fp, implode("\t",$row)).fwrite($fp, "\r\n");
                        $aFilesWritingStatus[] = $bFileWriting1;
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

function MailingMergeInput($input, $inputDir, $outputDir)
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
    global $sMergeMailOutputDir;
    global $sMailMergeBadDataOutputDir;
    global $IgnoreDynamicMailer;

    $sFileName =  preg_replace("/(TAG-|tag-)|(CreditOutgoingEmbossing_)/","",basename($inputDir,$sFileExtension));  
    $aHasError = array();
    $bHasError = false;

            /*MAILING*/
            $aMailShippingOutputData = array();
            $aMailShippingOutputBadData = array();
            $iRecordNo = 0;
            $inputDetail = array_slice($input, 1, -1);
            foreach($inputDetail as $aRecord)
            {
                ++$iRecordNo;
                $sPAN = trim($aRecord['2']);
                $SHIPPING_METHOD = trim($aRecord['38']);
                foreach($aBINs[$sBIN] as $ProductID=> $ProductIDarray)
                {
                    if(empty(trim($aRecord['12'])) || trim($aRecord['12']) != $ProductID)
                    {
                            $PRODUCT_ID = trim($input[0][4]); 
                    }
                    else
                    {
                            $PRODUCT_ID = trim($aRecord['12']);
                            break;
                    }
         	     }
              //$MAILER_ID = "NA";
              
             if($sBIN != "519369")
              {
                  $MAILER_ID = "NA"; 
              }
              else
              {
                  $MAILER_ID = trim($aRecord['39']); 
                  if((preg_match('/\|/', trim($aRecord['43'])) && (empty(trim($aRecord['39'])))))
                  {
                      $aField44 = explode('|', $aRecord['43']);
                      //MERCHANG LOGO
                      $MAILER_ID = $aField44['1'];
                  }
                     
              }
              
                $ProductProp = $aBINs[$sBIN][$PRODUCT_ID][$MAILER_ID];  

                $Company = "";
                $FullName = trim($aRecord['8']).trim($aRecord['9'])." ".trim($aRecord['10']);
                $Address1 = trim($aRecord['25']);
                $Address2 = empty(trim($aRecord['26']))? "": trim($aRecord['26']);
                $City = trim($aRecord['27']);
                $State =  trim($aRecord['28']); 
                if($sBIN == "547018")
                {
                    $FullName = ucwords(strtolower($FullName));
                    $Address1 = ucwords(strtolower($Address1));
                    $Address2 = ucwords(strtolower($Address2));
                    $City = ucwords(strtolower($City));
                }
                $ZIPCode = substr(trim($aRecord['30']),0,5);
                $ZIPCodeAddOn = empty(substr(trim($aRecord['30']),5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['30']),5));
                $Country = (trim($aRecord['29']=="USA"))? "US":trim($aRecord['29']);
                //$EmailAddress;
                $FromFullName = "";
                $FromAddress1 = "80 Corbett Way";
                $FromAddress2 = "";
                $FromCity ="Eatontown";
                $FromState= "NJ";
                $FromCountry = "US";
                $FromZIPCode = "07724";                
                //$Amount;
                $ServiceType = "US-FC"; //Standard Overnight -> FEDEX, 
                $PackageType = "Letter";
                $WeightOz = "1";
                $ShipDate = "+1";
                $ImageType = "Pdf";
                $Reference1 = substr($sCustomerName,0,3)."_".trim($aRecord['SerialNumber'])."_".substr($aRecord['2'], -4);
                $Reference2 = strtoupper(hash("sha256",$sPAN, false));
                $Reference3 = trim($aRecord['11'])."|".trim($aRecord['35']);
                $Reference4 = trim($aRecord['32']);
                $QR_or_CreditLimit = trim($aRecord['32']);
                $Logo =  trim($aRecord['39']);
                $AccountNumber1 =  trim($aRecord['36']);
                $AccountNumber2 =  trim($aRecord['31']);
                $RoutingNumber = ($sBIN=="411172") ? "026073150": "";
                $BusinessNameOnCard= trim($aRecord['43']);
                $LogoTop = "";
                $LogoBottom = "";
                $ContactNumber = "";
                $WeekDaysHrs = "";
                $WeekEndHrs = "";
                $SiteURL = "";
                $NameOnCard = "";
                $Motto = "";
                $CardActivationURL = "";
                $Disclosure = "";
                if(preg_match('/\|/', $BusinessNameOnCard))
                {
                    //BusinessName|DistributionPartnersLogo|ContactNumber|WeekDays hours|Weekend hours|SiteURL|CardDescription
                    $aField44 = explode('|', $aRecord['43']);
                    //BUSINESS NAME ON CARD
                    if(!isset($aField44['0']))
                    {
                        $BusinessNameOnCard = 'Missing data';
                        echo "$sDateStamp [$sUser]: ERROR: Missing data for Bussiness Name On Card \n";
                        $aHasError[] = true;
                    }
                    else
                    {
                        $BusinessNameOnCard = $aField44['0'];
                    }
                    //MERCHANG LOGO
                    if(!isset($aField44['1']))
                    {
                        $LogoTop = 'Missing data';
                        $LogoBottom = 'Missing data';
                        echo "$sDateStamp [$sUser]: ERROR: Missing data for Logo ID \n";

                        $aHasError[] = true;
                    }
                    else
                    {
                        $LogoTop = $aField44['1'].'_M1';
                        $LogoBottom = $aField44['1'].'_M2';
                    }
                    //CONTACT NUMBER
                    if(!isset($aField44['2']))
                    {
                        $ContactNumber = 'Missing data';
                        echo "$sDateStamp [$sUser]: ERROR: Missing data for ContactNumber \n";

                        $aHasError[] = true;
                    }
                    else
                    {
                        $ContactNumber = $aField44['2'];
                    }
                    //WEEKDAY HOURS
                    if(!isset($aField44['3']))
                    {
                        $WeekDaysHrs = 'Missing data';
                        echo "$sDateStamp [$sUser]: ERROR: Missing data for WeekDaysHrs \n";

                        $aHasError[] = true;
                    }
                    else
                    {
                        $WeekDaysHrs = $aField44['3'];
                    }
                    //WEEKEND HOURS
                    if(!isset($aField44['4']))
                    {
                        $WeekEndHrs = 'Missing data';
                        echo "$sDateStamp [$sUser]: ERROR: Missing data for WeekendHrs \n";

                        $aHasError[] = true;
                    }
                    else
                    {
                        $WeekEndHrs = $aField44['4'];
                    }
                    //SITE URL
                    if(!isset($aField44['5']))
                    {
                        $SiteURL = 'Missing data';
                        echo "$sDateStamp [$sUser]: ERROR: Missing data for SiteURL \n";

                        $aHasError[] = true;
                    }
                    else
                    {
                        $SiteURL = $aField44['5'];
                    }
                    //$Name_Of_Card = $aField44['6'];
                    //$Card_Description = $aField44['7'];

                }
                $NameOnCard = trim($aRecord['6']); //6
                 
                if(preg_match('/\|/', $aRecord['45']))
                {
                    $aField46 = explode('|', $aRecord['45']);   
                    $Motto= isset($aField46['0'])? $aField46['0']: ""; 
                    if(!isset($aField46['1']))
                    {
                        $CardActivationURL = 'Missing data';
                        $aHasError[] = true;
                    }
                    else
                    {
                        $CardActivationURL = $aField46['1'];
                    }
                    $Disclosure =  isset($aField46['2'])? $aField46['2']: ""; 
                }
               

                switch($aShippingMethods[0][$aRecord['38']])
                {
                    case "USPS":    
                        $ServiceType = 'US-FC';      
                        $PackageType = "Letter";
                    break;      
                    case "USPS_TR":
                        $ServiceType = 'US-FC';
                        $PackageType = "Package";
                    break;           
                    case "USPS_PM":
                        $ServiceType = 'US-PM';
                        $PackageType = "Large Envelope or Flat";
                    break;
                    default:
                        $ServiceType = 'US-FC';
                        $PackageType = "Letter";
                    break;
                }
             
            
                //MAILING RESULT
                foreach($aHasError as $i)
                {
                    if($i==true)
                    {
                        $bHasError = true;
                    }

                }
                if( $bHasError==true)
                {
                    $aMailShippingOutputBadData[$SHIPPING_METHOD][($ProductProp["Product"])][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode, $ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2,$FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4, $QR_or_CreditLimit, $Logo, $AccountNumber1, $AccountNumber2, $RoutingNumber, $BusinessNameOnCard, $LogoTop, $LogoBottom,$ContactNumber,$WeekDaysHrs, $WeekEndHrs, $SiteURL,$NameOnCard,$Motto,$CardActivationURL,$Disclosure);
                    $aMailShippingOutputBadDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn", "Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3", "Reference4", "QR_or_CreditLimit", "Logo","AccountNumber1","AccountNumber2","RoutingNumber","BusinessNameOnCard", "LogoTop", "LogoBottom","ContactNumber","WeekDaysHrs", "WeekEndHrs", "SiteURL","NameOnCard","Motto","CardActivationURL","Disclosure"."\r\n"));
                }
                else
                {
                    $aMailShippingOutputData[$SHIPPING_METHOD][($ProductProp["Product"])][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode, $ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2,$FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4, $QR_or_CreditLimit, $Logo, $AccountNumber1, $AccountNumber2, $RoutingNumber, $BusinessNameOnCard, $LogoTop, $LogoBottom,$ContactNumber,$WeekDaysHrs, $WeekEndHrs, $SiteURL,$NameOnCard,$Motto,$CardActivationURL,$Disclosure);
                    $aMailShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn", "Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3", "Reference4", "QR_or_CreditLimit", "Logo","AccountNumber1","AccountNumber2","RoutingNumber","BusinessNameOnCard", "LogoTop", "LogoBottom","ContactNumber","WeekDaysHrs", "WeekEndHrs", "SiteURL","NameOnCard","Motto","CardActivationURL","Disclosure"."\r\n"));

                }
            
            
        }
        
        $fp;

        if(!empty($aMailShippingOutputData))
        {
            foreach($aMailShippingOutputData as $keyShipment => $aShippingRecord)     
            {
            
                $sShippingName = $aShippingMethods[0][$keyShipment];
                
                    $outputDir = $sMergeMailOutputDir;
                    
            
            
                foreach($aShippingRecord as $keyProduct => $aProductRecord)
                {
                    $sProductName = $keyProduct;
                    $sMailOutputFile = $outputDir."MAILMERGE_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_".$sFileName.".csv";
                    echo "$sDateStamp [$sUser]: Writing Mail Input file to $sMailOutputFile \n";
                    $fp = fopen($sMailOutputFile, "w");
                    $bFileWriting1; 
                    fwrite($fp, $aMailShippingOutputDataHeader);
                        foreach ($aProductRecord as $row) 
                        { 
                            $bFileWriting1 =fwrite($fp, implode("\t",$row)).fwrite($fp, "\r\n");
                            $aFilesWritingStatus[] = $bFileWriting1;
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
        if(!empty($aMailShippingOutputBadData))
        {
            foreach($aMailShippingOutputBadData as $keyShipment => $aShippingRecord)     
            {
            
                $sShippingName = $aShippingMethods[0][$keyShipment];
               
                    $outputDir = $sMailMergeBadDataOutputDir;
            
                foreach($aShippingRecord as $keyProduct => $aProductRecord)
                {
                    $sProductName = $keyProduct;
                    $sMailOutputFile = $outputDir."MAILMERGE_BAD_DATA_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_".$sFileName.".csv";
                    echo "$sDateStamp [$sUser]: Writing Mail Input file to $sMailOutputFile \n";
                    $fp = fopen($sMailOutputFile, "w");
                    $bFileWriting1; 
                    fwrite($fp, $aMailShippingOutputBadDataHeader);
                        foreach ($aProductRecord as $row) 
                        { 
                            $bFileWriting1 =fwrite($fp, implode("\t",$row)).fwrite($fp, "\r\n");
                            $aFilesWritingStatus[] = $bFileWriting1;
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

        return $aMailShippingOutputData;
}


    //$ trim(ucwords(strtolower($name)))

    function capitalizeName($name) {
        // A list of properly cased parts
        $CASED = [
          "O'", "l'", "d'", 'St.', 'Mc', 'the', 'van', 'het', 'in', "'t", 'ten',
          'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', 'the', 'III', 'IV',
          'VI', 'VII', 'VIII', 'IX',
        ];
    
        // Trim whitespace sequences to one space, append space to properly chunk
        $name = preg_replace('/\s+/', ' ', $name) . ' ';
    
        // Break name up into parts split by name separators
        $parts = preg_split('/( |-|O\'|l\'|d\'|St\\.|Mc)/i', $name, -1, PREG_SPLIT_DELIM_CAPTURE);
    
        // Chunk parts, use $CASED or uppercase first, remove unfinished chunks
        $parts = array_chunk($parts, 2);
        $parts = array_filter($parts, function($part) {
                return sizeof($part) == 2;
            });
        $parts = array_map(function($part) use($CASED) {
                // Extract to name and separator part
                list($name, $separator) = $part;
    
                // Use specified case for separator if set
                $cased = current(array_filter($CASED, function($i) use($separator) {
                    return strcasecmp($i, $separator) == 0;
                }));
                $separator = $cased ? $cased : $separator;
    
                // Choose specified part case, or uppercase first as default
                $cased = current(array_filter($CASED, function($i) use($name) {
                    return strcasecmp($i, $name) == 0;
                }));
                return [$cased ? $cased : ucfirst(strtolower($name)), $separator];
            }, $parts);
        $parts = array_map(function($part) {
                return implode($part);
            }, $parts);
        $name = implode($parts);
    
        // Trim and return normalized name
        return trim($name);
    }


function getDetailOverview($aInputData){

    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
    global $aShippingMethods;
    $iTotalNumberOfRecords=0;
    //$aInputData = array_slice($aInputData,0,1);
    //print_r($aInputData);
    foreach($aInputData as $keyShip => $aShipRecord)
    { 
        
        foreach($aShipRecord as $keyProd => $aProdRecords)
        {
            foreach($aProdRecords as $aRecord)
            {
                $aCollectShipment[$keyShip][] = $keyProd;
            }
        }
    }
    //print_r($aCollectShipment);
    //$aShipmentServicesPerProduct = array_count_values($aCollectShipment);
    
    
    echo "\n\t Detail Summary of records in file Shipment Method and per Product for customer $sCustomerName: \n";
    printf('            %-20s|  %-20s|    %-20s ', 'Shipment Method','Product', 'Total Number of Records');
    echo"\n";

    foreach($aCollectShipment as $keyShipService => $aProducts)
    {
        //print_r($aProducts);
        $aShipmentServices = array_count_values($aProducts);
        $iSubTotalNumberOfRecords = 0;
        //print_r( $aShipmentServices);
        foreach($aShipmentServices as $keyShipPerProduct  => $iTotalNoPerService)
        { 
            
            $sShipmentMethodType = $keyShipService;
            $sShipmentMethodDescr = $aShippingMethods[0][$keyShipService];
            $sProductType = $keyShipPerProduct;
            $iTotalNumberOfRecords+=$iTotalNoPerService;
            $iSubTotalNumberOfRecords+=$iTotalNoPerService;
            printf('            %-2s-%-17s|  %-20s|    %20d ',$sShipmentMethodType, $sShipmentMethodDescr, $sProductType, $iTotalNoPerService);
            echo"\n";
        }
       
        printf('           %-87s','------------------------------------------------------------------------');
        echo"\n";
        printf('           %-47s  %20d', 'Subtotal Records per Product',$iSubTotalNumberOfRecords);
        echo"\n\n";
        
    }

        printf('           %-87s','------------------------------------------------------------------------');
        echo"\n";
        printf('           %-20s    %-20s    %20d', 'Total Records in file','',$iTotalNumberOfRecords);
        echo"\n\n";
    
    return  $aCollectShipment;

}

function getSerialNumber($inputDir)
{
    global $sDateStamp;
    global $sUser;
    global $SerialNumberOfDigits;

    if(file_exists($inputDir))
    {
        $aInputFile = file($inputDir, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $header = array_shift($aInputFile);
        $aRecordData;
        foreach($aInputFile as $aData)
        {
            $aRecordData = array_combine(str_getcsv($header,','),str_getcsv($aData,','));
        }   
        
        
        if(isset($aRecordData['SerialNumber']))
        {
            $SerialNumber = $aRecordData['SerialNumber'];
            if(!preg_match('/[0-9]{1,}/',$SerialNumber))
            {
                $SerialNumber =str_pad(1,$SerialNumberOfDigits,'0',STR_PAD_LEFT);
                echo "$sDateStamp [$sUser]: ERROR: Serial number corrupted: $SerialNumber, new serial number starting $SerialNumber, will be started\n";
                return $SerialNumber;
            }

            if(strlen($SerialNumber)>$SerialNumberOfDigits)
            {
                $SerialNumber = 1;
            }
            echo "$sDateStamp [$sUser]: Serial number continues at $SerialNumber\n";
            return str_pad($SerialNumber,$SerialNumberOfDigits,'0',STR_PAD_LEFT);
        }
        else
        {
            $SerialNumber =str_pad(1,$SerialNumberOfDigits,'0',STR_PAD_LEFT);
            echo "$sDateStamp [$sUser]: ERROR: Serial number corrupted, no value set or header is not 'SerialNumber': $SerialNumber, new serial number starting 1, will be started\n";
            return $SerialNumber;
        }
        
    }
    else
    {
        $SerialNumber = 000001;
        echo "$sDateStamp [$sUser]: ERROR: Serial counter file is missing. Creating a new one starting 000001\n";
        $SerialNumberFile = fopen($inputDir,'w');
        fputcsv($SerialNumberFile,array('SerialNumber'));
        fputcsv($SerialNumberFile,array($SerialNumber));
        fclose($SerialNumberFile);
        return $SerialNumber;
    }
  
}

function setSerialNumber($inputDir,$SerialNumberOfDigits,$SerialNumber)
{
                global $sDataStamp;
                global $sUser;
                $SerialNumber = str_pad($SerialNumber,$SerialNumberOfDigits,'0',STR_PAD_LEFT);        
                $SerialNumberFile = fopen($inputDir,'w');
                fputcsv($SerialNumberFile,array('SerialNumber'));
                fputcsv($SerialNumberFile,array($SerialNumber));
                fclose($SerialNumberFile);
                return; 
                
}


echo "$sDateStamp [$sUser]: Ending Script";

?> 
