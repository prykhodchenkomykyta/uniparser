<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 12/06/2021
Revision: 03/24/2022
Name: Jean-Eric Pierre-Louis
Version: 1.6
Notes: Updated Shipping Methods; Contains References 1-4; Updated Shipping Method Logic
- Changed default shipping method to USPS_TR (US-FC + "Package" = Tracking)
******************************/

/*Production Environment*/
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/corecard/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sFedexOutputDir = "/var/TSSS/Files/FEDEX/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/corecard/";


// $sInputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Debit\\in\\";
// $sOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Debit\\out\\";
// $sMailOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Debit\\out\\USPS\\";
// $sFedexOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Debit\\out\\FEDEX\\";
// $sMergeMailOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Debit\\out\\USPS\\";
// $sProcessedDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Debit\\In\\";   
// $sBulkOutputDir = "W:\\Workspace\\TagSystem\\Parser_Plugin\\Corecard_Debit\\out\\BULK\\";
$BATCH_LOG_ID;
$sDataPrepProfile;
$sCustomerName;
$iNumberOfRecords;
$sBIN;
$SHIPPING_METHOD;
$PRODUCT_ID;
$sFileExtension =".txt";
 

$aShippingMethods[] = array(    
                                0=> "USPS_TR", //USPS w/ Tracking
                                1=> "FEDx_OR", // FedEx One Rate
                                2=> "FEDx_SON", //FedEx Standard Overnight
                                3=> "USPS", //USPS w/o Tracking
                                4=> "USPS_PM", //USPS Priority Mail
                            );
                            print_r($aShippingMethods);

$aBINs['411172']=array("Customer"=>"GLIDE",
                        "243373170"=> array(
                            "NA" => array(
                                "Profile"=>"P_GLIDE_VISA_D1_ST_TOPAZ_123_V01_v143",
                                "Product"=>"")));


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
            $bMailMergeProcessed = MailingMergeInput(Parser($sInputFilePath), $sInputFilePath, $sMergeMailOutputDir, $sProcessedDir);
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
    global $sCustomerName;
    global $sProcessedFilename;
    global $BATCH_LOG_ID;

    

    $sFileName = basename($inputDir);
    
    $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);

    echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";
   // echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n\n";

     
    echo "$sDateStamp [$sUser]: Starting Parsing Data\n";
    $aRecordData;
    foreach($aInputFile as $aData)
    {
        $aRecordData[] = str_getcsv($aData);
    }   
    
      
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
    /*DATAPREP*/
    $aDataPrepOutputData;
    $BATCH_LOG_ID = $input[0][1];
    
    $sFileName = basename($inputDir, $sFileExtension);
    
    $recordNo=0;

    
    //$inputDetail = array_slice($input, 1, -1);
    foreach($input as $aRecord)
    {   
        if($aRecord[0]==='<Header>')
        {
            echo"som header: ".$aRecord[0];
            $PRODUCT_ID = trim($aRecord['3']); 
            continue;
        }
        else if($aRecord['0']=='<Trailer>')
        {
            continue;
        }
       //echo"aRecord";
       // print_r($aRecord);
        $sBIN = substr(trim($aRecord['0']), 0,6);
        $SHIPPING_METHOD = trim($aRecord['33']);
	   /* foreach($aBINs[$sBIN] as $ProductID=> $ProductIDarray){
                if(empty(trim($aRecord['2'])) || trim($aRecord['2']) != $ProductID)
                {
                    $PRODUCT_ID = trim($input[0][4]); 
                }
                else
                {
                    $PRODUCT_ID = trim($aRecord['2']);
                    break;
                }
            }*/

          $MAILER_ID = "";
          if(empty(trim($aRecord['2'])))
          {
              $MAILER_ID = "NA"; 
          }
          else if($sBIN == "547018" || $sBIN == "411172")
          {
              $MAILER_ID = "NA"; 
          }
          else
          {
              $MAILER_ID = trim($aRecord['2']); 
          }
          
          //print_r($aBINs);
          $ProductProp = $aBINs[$sBIN][$PRODUCT_ID][$MAILER_ID];
          //print_r($ProductProp);


        //$PRODUCT_ID = empty(trim($aRecord['12'])) ? trim($input[0][4]) : trim($aRecord['12']);
        ++$recordNo;
        $sTrack1 = trim($aRecord['32']);
        $sTrack2 = trim($aRecord['20']);
        $sICVV =  trim($aRecord['37']);
        //$sTrack2Chip = trim($aRecord['41']);
        $PSN = trim($aRecord['1']);
        $CVC2 = trim($aRecord['13']);
        $sEmbName = trim($aRecord['3']);
        $sBatchID = $BATCH_LOG_ID."/".$recordNo;
	    $sUniqueNumber= sha1(substr($sTrack2,0,16));      
 // $sUniqueNumber= "dc5413d196ee7fcb80f0eeb97e79cfd60b1e54cf";
        $sNotUsed1 = "0000";
        $sNotUsed2 = "00";
        $sNotUsed3 = "000";
        $sDataPrepProfile =  $ProductProp['Profile'];
        $sNotUsed4 = "0000000";
        $sChipData = "$sTrack1#$sTrack2#$sICVV#$PSN#$CVC2#$sEmbName";
        if($sBIN =="519369" || $sBIN =="411172")
        {
            $sQRCode = "";
            $sLogo = "";
            $sBusinessNameOnCard = trim($aRecord['35']);
            $sTrack3 = $sLogo."|".$sQRCode."|".$sBusinessNameOnCard; 
            $sChipData = "$sTrack1#$sTrack2#$sICVV#$PSN#$CVC2#$sEmbName#$sTrack3";
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
            $PRODUCT_ID = "";
            //$inputDetail = array_slice($input, 1, -1);
            foreach($input as $aRecord)
            {
                if($aRecord['0']=='<Header>')
                {
                    $PRODUCT_ID = trim($aRecord['3']); 
                    continue;
                }
                else if($aRecord['0']=='<Trailer>')
                {
                    continue;
                }
                ++$iRecordNo;
                $sPAN = trim($aRecord['0']);
                $SHIPPING_METHOD = trim($aRecord['33']);
              
               /* foreach($aBINs[$sBIN] as $ProductID=> $ProductIDarray){
                    if(empty(trim($aRecord['2'])) || trim($aRecord['2']) != $ProductID)
                    {
                            $PRODUCT_ID = trim($input[0][4]); 
                    }
                     else
                    {
                            $PRODUCT_ID = trim($aRecord['2']);
                            break;
                    }
         	    }*/
                 
                $MAILER_ID = "";
                if(empty(trim($aRecord['2'])))
                {
                    $MAILER_ID = "NA"; 
                }
                else if($sBIN == "547018" || $sBIN == "411172")
                {
                    $MAILER_ID = "NA"; 
                }
                else
                {
                    $MAILER_ID = trim($aRecord['2']); 
                }
                
                $ProductProp = $aBINs[$sBIN][$PRODUCT_ID][$MAILER_ID];
                if(!isset( $ProductProp))
                {
                    echo("sBIN: $sBIN\n");
                    echo("PRODUCT_ID: $PRODUCT_ID\n");
                    echo("MAILER_ID: $MAILER_ID\n");

                }


                $FullName = trim($aRecord['5'])." ".trim($aRecord['6'])." ".trim($aRecord['7']);
                $Company = (empty(trim($aRecord['10']))) ? "" : trim($aRecord['10']);
                $Address1 = trim($aRecord['21']);
                $Address2 = trim($aRecord['22']);
                $City = trim($aRecord['23']);
                $State =  trim($aRecord['24']); 
                $ZIPCode = substr(trim($aRecord['25']),0,5);
                $ZIPCodeAddOn = empty(substr(trim($aRecord['25']),5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['25']),5));
                $Country = "US";
                $EmailAddress;
                $FromFullName;
                $FromFullName = "";
                $FromAddress1 = "80 Corbett Way";
                $FromAddress2 = "";
                $FromCity ="Eatontown";
                $FromState= "NJ";
                $FromCountry = "US";
                $FromZIPCode = "07724";                
                $Amount;
                $ServiceType = "US-FC"; //The Default will be USPS_TR
                $PackageType = "Package";
                $WeightOz = "1";
                $ShipDate = "+1";
                $ImageType = "Pdf";
                $Reference1 = $sCustomerName."_".$BATCH_LOG_ID."_".$iRecordNo."_".substr($aRecord['0'], -4);
                $Reference2 = strtoupper(hash("sha256",$sPAN, false));
                $Reference3 = trim($aRecord['9'])."|".trim($aRecord['30']);
                $Reference4 = trim($aRecord['10']);


                switch($aShippingMethods[0][$SHIPPING_METHOD])
                {

                    // 0=> "USPS_TR", //USPS w/ Tracking
                    // 1=> "FEDx_OR", // FedEx One Rate
                    // 2=> "FEDx_SON", //FedEx Standard Overnight
                    // 3=> "USPS", //USPS w/o Tracking
                    // 4=> "USPS_PM", //USPS Priority Mail
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
                
                $aMailShippingOutputData[$SHIPPING_METHOD][($ProductProp['Product'])][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode, $ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);
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
                fwrite($fp, implode("\t",array("Company", "FullName", "Address1", "Address2", "City", "State", "ZIPCode",
                 "ZIPCodeAddOn", "Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry",
                 "FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2",
                 "Reference3", "Reference4"
                 ))).fwrite($fp, "\r\n");
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
    global $sMailOutputDir;

    $sFileName = basename($inputDir,$sFileExtension);
        
              /*MAILING*/
              $aMailShippingOutputData;
              $iRecordNo = 0;
              $PRODUCT_ID = "";
              //$inputDetail = array_slice($input, 1, -1);
              foreach($input as $aRecord)
              {
                    if($aRecord['0']=='<Header>')
                    {
                        $PRODUCT_ID = trim($aRecord['3']); 
                        continue;
                    }
                    else if($aRecord['0']=='<Trailer>')
                    {
                        continue;
                    }
                  ++$iRecordNo;
                  $sPAN = trim($aRecord['0']);
                  $SHIPPING_METHOD = trim($aRecord['33']);
                 
                 /* foreach($aBINs[$sBIN] as $ProductID=> $ProductIDarray){
                      if(empty(trim($aRecord['2'])) || trim($aRecord['2']) != $ProductID)
                      {
                              $PRODUCT_ID = trim($input[0][4]); 
                      }
                       else
                      {
                              $PRODUCT_ID = trim($aRecord['2']);
                              break;
                      }
                   }*/
                  $MAILER_ID = "";
                  if(empty(trim($aRecord['2'])))
                  {
                      $MAILER_ID = "NA"; 
                  }
                  else if($sBIN == "547018" || $sBIN == "411172")
                  {
                      $MAILER_ID = "NA"; 
                  }
                  else
                  {
                      $MAILER_ID = trim($aRecord['2']); 
                  }
                  
                  $ProductProp = $aBINs[$sBIN][$PRODUCT_ID][$MAILER_ID];

                  $FullName = trim($aRecord['5'])." ".trim($aRecord['6'])." ".trim($aRecord['7']);
                  $Company = (empty(trim($aRecord['10']))) ? "" : trim($aRecord['10']);
                  $Address1 = trim($aRecord['21']);
                  $Address2 = trim($aRecord['22']);
                  $City = trim($aRecord['23']);
                  $State =  trim($aRecord['24']); 
                  $ZIPCode = substr(trim($aRecord['25']),0,5);
                  $ZIPCodeAddOn = empty(substr(trim($aRecord['25']),5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['25']),5));
                  $Country = "US";
                  $EmailAddress;
                  $FromFullName;
                  $FromFullName = "Tag Systems USA";
                  $FromAddress1 = "80 Corbett Way";
                  $FromAddress2 ="";
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
                  $Reference1 = $sCustomerName."_".$BATCH_LOG_ID."_".$iRecordNo."_".substr($aRecord['0'], -4);
                  $Reference2 = strtoupper(hash("sha256",$sPAN, false));
                  $Reference3 = trim($aRecord['9'])."|".trim($aRecord['30']);
                  $Reference4 = trim($aRecord['10']);
                $QR_or_CreditLimit = trim($aRecord['32']);
                $Logo =  "";
                $AccountNumber1 =  trim($aRecord['31']);
                $AccountNumber2 =  trim($aRecord['31']);
                $RoutingNumber = ($sBIN=="411172") ? "026073150": "";
                $BusinessNameOnCard = trim($aRecord['10']);
    

                switch($SHIPPING_METHOD)
                {
                    case 0:
                        $ServiceType = "US-FC";
                        $PackageType = "Package";
                        break;
                        //==USPS_TR First Class with Tracking 
                    case 1:
                        $ServiceType = "US-FC";
                        $PackageType = "Letter";
                        break;  
                        //First Class without Tracking
                    case 2:
                        $ServiceType = "US-PM";
                        $PackageType = "Large Envelope or Flat"; 
                        break; 
                        //Priority Mail
                    case 3:
                        $ServiceType = "FEDx_SON";
                        $PackageType = "Package"; 
                        break; 
                        //Priority Mail  
                    case 5: 
                        $bBulk = true; 
                        break;
                }
                //MAILING RESULT
                 
                
                $aMailShippingOutputData[$SHIPPING_METHOD][($ProductProp["Product"])][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode, $ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4, $QR_or_CreditLimit, $Logo, $AccountNumber1, $AccountNumber2, $RoutingNumber, $BusinessNameOnCard);
                
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
                $sMailOutputFile = $outputDir."MAILMERGE_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_".$sFileName.".csv";
                echo "$sDateStamp [$sUser]: Writing Mail Input file to $sMailOutputFile \n";
                $fp = fopen($sMailOutputFile, "w");
                $bFileWriting1; 
                fwrite($fp, implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn", "Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3", "Reference4", "QR_or_CreditLimit", "Logo","AccountNumber1","AccountNumber2","RoutingNumber","BusinessNameOnCard"))).fwrite($fp, "\r\n");
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
        return $aMailShippingOutputData;
}

function getDetailOverview($aInputData){

    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
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
            $sProductType = $keyShipPerProduct;
            $iTotalNumberOfRecords+=$iTotalNoPerService;
            $iSubTotalNumberOfRecords+=$iTotalNoPerService;
            printf('            %-20s|  %-20s|    %20d ',$sShipmentMethodType, $sProductType, $iTotalNoPerService);
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


echo "$sDateStamp [$sUser]: Ending Script";

?> 
