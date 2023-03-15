<?php 
/******************************
Author: Jean-Eric Pierre-Louis
Company: Pierre & Rady LLC
Date: 10/20/2020
Revision: 01/25/2022
Name: Radovan Jakus
Version: 1.14
Notes: Adding Celtic Deserve
******************************/

//Production Environment
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/deserve/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sFedexOutputDir = "/var/TSSS/Files/FEDEX/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/deserve/";
$SerialNumberLocal ="/home/erutberg/Radovan/SerialNumberCounter.csv";


// $sInputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Deserve\\In\\";
// $sOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Deserve\\out\\";
// $sMailOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Deserve\\out\\USPS\\";
// $sFedexOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Deserve\\out\\FEDEX\\";
// $sProcessedDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Deserve\\In\\"; 
// $SerialNumberLocal ="D:\\Workspace\\TagSystem\\Parser_Plugin\\Deserve\\SerialNumberCounter.csv";

 //Mailer Information
 $BarcodeID = "00";
 $ServiceTypeID = "270";
 $MailerID = "902695246";
 $maxRec = 1000;
 $SerialNumberOfDigits = (strlen($MailerID)==9)? 6 : 9;

                                                    
$BATCH_LOG_ID;
$iNoOfCards;
$sDataPrepProfile;
$sCustomerName;
$sBIN;
$SHIPPING_METHOD;
$PRODUCT_ID;
$sFileExtension =".json";
$aShippingMethods[] = array(    "post"=> "USPS",
                                "01"=> "FEDx_OR",
                                "02"=>"USPS_PM",
                            );

 
$aBINs['555684']=array("Customer"=>"DESERVE_CELTIC",
                            "01"=> array(
                                "Profile"=>"P_DESERVE_CELTIC_MC_20_94_ST_TOPAZ_12_V01_v143",
                                "Product"=>""));
                        

$aBINs['528898']=array("Customer"=>"DESERVE",
                        "1"=> array(
                            "Profile"=>"P_DESERVE_MC_20_94_ST_TOPAZ_122_V01_v143",
                            "Product"=>""));

                            
$aBINs['515856']=array("Customer"=>"PERPAY",
                        "1"=> array(
                                    "Profile"=>"P_PERPAY_MC_20_ST_TOPAZ_12_V01_v143",
                                    "Product"=>"")); 

$aBINs['523215']=array("Customer"=>"CUSTOMER_BANK", 
                        "1" => array(
                                "Profile"=>"P_CUSTOMER_BANK_MC_20_94_ST_TOPAZ_12_V01_v143",
                                "Product"=>""));    
                            
$aBINs['558345']=array("Customer"=>"GLORIFI", 
                    "BRASS" => array(
                            "Profile"=>"P_GLORIFI_MC_20_94_NXP_P71_V01_v143",
                            "Product"=>"CR_MT_BRS"),
                    "THIN_BLUE_LINE_PLASTIC" => array(
                            "Profile"=>"P_GLORIFI_MC_20_94_SECORA_S_V01_v143",
                            "Product"=>"CR_PL_BLU"),
                    "CONSTITUTIONAL_PLASTIC" => array(
                            "Profile"=>"P_GLORIFI_MC_20_94_SECORA_S_V01_v143",
                            "Product"=>"CR_PL_CNST"),
                    "1776_PLASTIC" => array(
                            "Profile"=>"P_GLORIFI_MC_20_94_SECORA_S_V01_v143",
                            "Product"=>"CR_PL_1776"),
                    "THIN_BLUE_LINE_METAL_VENEER" => array(
                            "Profile"=>"P_GLORIFI_MC_20_94_SECORA_S_V02_v143",
                            "Product"=>"CR_MT_BLU"),
                    "CONSTITUTIONAL_METAL_VENEER" => array(
                            "Profile"=>"P_GLORIFI_MC_20_94_SECORA_S_V02_v143",
                            "Product"=>"CR_MT_CNST")       
                        );    

                           

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
            $aParsedData = Parser($sInputFilePath);
            $bDataProcessed = DataPrepInput($aParsedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
            $bMailProcessed = MailingInput($aParsedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
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
               
            
                    $aParsedData = Parser($sInputFilePath);
                    $bDataProcessed = DataPrepInput($aParsedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
                    $bMailProcessed = MailingInput($aParsedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
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

function Parser($inputDir)
{
    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
    global $sProcessedFilename;
    global $BATCH_LOG_ID;
    global $iNoOfCards;
    global $sSerialNumberurl;
    global $SerialNumberLocal; 
    global $SerialNumberOfDigits;
 

    /*SERIAL NUMBER*/
    $SerialNumber = getSerialNumber($SerialNumberLocal,$SerialNumberOfDigits);
    $SerialNumber = str_pad($SerialNumber,$SerialNumberOfDigits,'0',STR_PAD_LEFT);        


    $sFileName = basename($inputDir);
    $aInputFile = file_get_contents($inputDir);
    echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";
  
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
   global $aShippingMethods;
   global $SHIPPING_METHOD;
   global $PRODUCT_ID;
   global $sFileExtension;
    /*DATAPREP*/
    $aDataPrepOutputData;
   
    
    $sFileName = basename($inputDir, $sFileExtension);
    
    $recordNo=0;


    foreach($input['details'] as $aRecord)
    {   
        //echo"\n aRecord: ";
        //print_r($aRecord);

        $sBIN = substr(trim($aRecord['card_number']), 0,6);
        $SHIPPING_METHOD = trim($aRecord['shipping_method']);
        $PRODUCT_ID = trim($aRecord['template_id']);
        ++$recordNo;
        $sTrack1 = trim($aRecord['track1_data']);
        $sTrack2 = trim($aRecord['track2_data']);
        $sTrack2Chip = trim($aRecord['track2_equivalent_data']);
        $sICVV = trim($aRecord['icvv']);
        $CVC2 = trim($aRecord['cvv2']);
        $PSN = trim($aRecord['card_sequence_number']);
        $sEmbName = strtoupper(trim($aRecord['name_on_card']));
        $sBatchID = $BATCH_LOG_ID."/".$recordNo;
	    $sUniqueNumber= sha1(substr($sTrack2,0,16));       
// $sUniqueNumber= "dc5413d196ee7fcb80f0eeb97e79cfd60b1e54cf";
        $sNotUsed1 = "0000";
        $sNotUsed2 = "00";
        $sNotUsed3 = "000";
        $sDataPrepProfile =  $aBINs[$sBIN][trim($PRODUCT_ID)]['Profile'];
        $sNotUsed4 = "0000000";
        $sChipData = "$sTrack1#$sTrack2#$sTrack2Chip#$sICVV#$CVC2#$PSN#$sEmbName";
        
        //DATAPREP RESULT
        $aDataPrepOutputData[$SHIPPING_METHOD][($aBINs[$sBIN][trim($PRODUCT_ID)]["Product"])][]=array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData);
    }

    $sCustomerName = $aBINs[$sBIN]['Customer'];
    
    echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n";
    echo "$sDateStamp [$sUser]: BIN: $sBIN \n";
    echo "$sDateStamp [$sUser]: DATAPREP START \n";

   
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
        //     echo"Before slice";
        //     print_r($input);
        //    $inputDetail = array_slice($input, 1, -1);
        //    echo"After slice"; 
        //    print_r($input);
            
            foreach($input['details'] as $aRecord)
            {
                // array("Company", "FullName", "Address1", "Address2", "City", "State", "ZIPCode",
                // "ZIPCodeAddOn", "Country", "FromFullName","FromAddress1", "FromAddress2","FromCity","FromState","FromCountry","FromZIPCode",
                // "ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4")
                ++$iRecordNo;
                $sPAN = trim(str_replace(" ","",$aRecord['card_number']));
                $SHIPPING_METHOD = trim($aRecord['shipping_method']);
                $PRODUCT_ID = trim($aRecord['template_id']);
                $FullName = trim($aRecord['middle_name'])!=null? trim($aRecord['first_name'])." ".trim($aRecord['middle_name'])." ".trim($aRecord['last_name']) : trim($aRecord['first_name'])." ".trim($aRecord['last_name']);
                $Company = " ";
                $Address1 = trim($aRecord['address_line_1']);
                $Address2 = (trim($aRecord['address_line_2'])==null) ? "" : trim($aRecord['address_line_2']);
                $City = trim($aRecord['city']);
                $State =  trim($aRecord['state']); 
                $ZIPCode = substr(trim($aRecord['postal_code']),0,5);
                $ZIPCodeAddOn =  empty(substr(trim($aRecord['postal_code']),5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['postal_code']),5));
                $Country = (trim($aRecord['country']=="USA"))? "US":trim($aRecord['country']);
                $FromFullName = "";
                $FromAddress1 = "80 Corbett Way";
                $FromAddress2 = "";
                $FromCity ="Eatontown";
                $FromState= "NJ";
                $FromCountry = "US";
                $FromZIPCode = "07724";                
                $Amount;
                $EmailAddress;
                $ServiceType = "US-FC"; //Standard Overnight -> FEDEX, 
                $PackageType = "Letter";
                $ShipDate = "+1";
                $ImageType = "Pdf";
                $Reference1 = substr($sCustomerName,0,3)."_".$aRecord['SerialNumber']."_".substr($aRecord['card_number'], -4);
                $Reference2 = strtoupper(hash("sha256", $sPAN, false));
                $Reference3 =  trim($aRecord['account_number']);
                $Reference4 = " ";
                switch($aShippingMethods[0][$aRecord['shipping_method']])
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
                            if($sBIN == "558345" && $PRODUCT_ID == "BRASS")
                                $PackageType = "Package";
                            break;
                        // USPS Priority Mail
                    
                }
                //MAILING RESULT
                $aMailShippingOutputData[$SHIPPING_METHOD][($aBINs[$sBIN][trim($PRODUCT_ID)]["Product"])][]=array($Company,
                    $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, 
                    $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $ShipDate, $ImageType, 
                    $Reference1, $Reference2, $Reference3, $Reference4
                );
                $aMailShippingOutputDataHeader = implode("\t",array(
                    "Compnay","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn",
                     "Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode",
                     "ServiceType","PackageType","ShipDate", "ImageType", "Reference1", "Reference2", "Reference3", "Reference4"."\r\n"));
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

function getSerialNumber($inputDir)
{
    global $sDateStamp;
    global $sUser;
    global $SerialNumberOfDigits;

    if(file_exists($inputDir))
    {
        $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);
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
