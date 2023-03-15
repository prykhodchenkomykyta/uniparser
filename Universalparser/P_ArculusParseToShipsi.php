<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 10/22/2021
Revision: 10/22/2021
Name: Radovan Jakus
Version: 1.1
******************************/

/*Production Environment*/
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/composecure/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/composecure/";

// $sInputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Arculus\\in\\";
// $sOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Arculus\\out\\";
// $sMailOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Arculus\\out\\";
// $sProcessedDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Arculus\\in\\";   

$BATCH_LOG_ID;
$iNumberOfRecords;
$sDataPrepProfile;
$sCustomerName;
$sBIN;
$SHIPPING_METHOD;
$PRODUCT_ID;
$sFileExtension =".csv";
$aShippingMethods[] = array(    
                                1=> "USPS_TR",
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
            
                 //$bDataProcessed = DataPrepInput(Parser($sInputFilePath,","), $sInputFilePath, $sOutputDir, $sProcessedDir);
                 $bMailProcessed = MailingInput(Parser($sInputFilePath,","), $sInputFilePath, $sMailOutputDir, $sProcessedDir);
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

function Parser($inputDir,$sDelimiter)
{
    global $sDateStamp;
    global $sUser;
    global $iNumberOfRecords;
    $iNumberOfRecords = 0;
    
  
    
    //echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";

    $sFileName = basename($inputDir);
    $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);
    $header = array_shift($aInputFile);
    $aRecordData;
    foreach($aInputFile as $aData)
    {
        $iNumberOfRecords++;
        $aRecordData[] = array_combine(str_getcsv($header,$sDelimiter),str_getcsv($aData,$sDelimiter));
    }   
    //echo"HERE";
    //print_r($aRecordData);

    return  $aRecordData;
}


function MailingInput($input, $inputDir, $outputDir)
{
    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
    global $BATCH_LOG_ID;
    global $aShippingMethods;
    global $sFileExtension;

    $sFileName = basename($inputDir,$sFileExtension).".csv";
        
            /*MAILING*/
            $aMailShippingOutputData;
            $iRecordNo = 0;
            $inputDetail = $input;
            foreach($inputDetail as $aRecord)
            {
                ++$iRecordNo;
                $SHIPPING_METHOD = 1;
                $FullName = trim($aRecord['Name']);
                $Company = "";
                $Address1 = trim($aRecord['Address Line 1']);
                $Address2 = trim($aRecord['Address Line 2']);
                $City = trim($aRecord['City']);
                $State =  trim($aRecord['State']); 
                $ZIPCode = substr(trim($aRecord['Zip']),0,5);
                $ZIPCodeAddOn = empty(substr($aRecord['Zip'],5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['Zip']),5));
                $Country = "US";
                $EmailAddress = trim($aRecord['Email']);
                $FromFullName = "TagSystems USA";
                $FromAddress1 = "80 Corbett Way";
                $FromAddress2;
                $FromCity ="Eatontown";
                $FromState= "NJ";
                $FromCountry = "US";
                $FromZIPCode = "07724";                
                $Amount;
                $ServiceType = "US-FC"; //Standard Overnight -> FEDEX,
                $PackageType = "Package";
                $WeightOz = "1"; 
                //$PackageType = "Large Envelope or Flat";
                $ShipDate = "+1";
                $ImageType = "Pdf";
                $Reference1 = "Arculus_".$iRecordNo;
                $Reference2 = trim($aRecord['Order ID']);
                $Reference3 = trim($aRecord['Stripe ID']);
                $Reference4 = trim($aRecord['Production #']);

               /* switch($aRecord['29'])
                {
                    case 4:
                        $ServiceType = "US-FC";
                       // $aMailShippingOutputData[] = array($FullName, $Address1, $Address2,$City,$State,$ZIPCode, $Country, $ServiceType, $ShipDate, $ImageType, $Reference1);
                        break;
                }*/
                //MAILING RESULT
                $aMailShippingOutputData[$SHIPPING_METHOD][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $EmailAddress, $FromFullName, $FromAddress1, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz,$ShipDate, $ImageType, $Reference1, $Reference2, $Reference3,$Reference4);
  
            }

        echo "$sDateStamp [$sUser]: MAILING START \n";
        
        foreach($aMailShippingOutputData as $keyShipment => $aShippingRecord)     
        {
        
            $sShippingName = $aShippingMethods[0][$keyShipment];
           
                echo "$sDateStamp [$sUser]: Mailing: Records per shipment method $sShippingName: ".count($aShippingRecord)."\n";
                $bFileWriting1; 

                $maxRec = 100000;
                $numSplits = 0;
                $recordsDone = 0;
                $fpm = null;
                $neededSplits = 0;

                if(count($aShippingRecord)>$maxRec)
                {
                    $neededSplits = ceil(count($aShippingRecord) / $maxRec);
                }
                
                foreach ($aShippingRecord as $row) 
                    { 
                        if($recordsDone == $maxRec)
                         $recordsDone = 0;
                        if($recordsDone == 0)
                         {
                             if($numSplits > 0)
                                 fclose($fpm);
                             ++$numSplits;
                             $sMailOutputFile =  $outputDir."MAIL_ARCULUS_".$sShippingName."_";
                             if($neededSplits > 0)
                                 $sMailOutputFile = $sMailOutputFile.$numSplits."_of_".$neededSplits."_";
                             $sMailOutputFile = $sMailOutputFile.$sFileName;
                             echo "$sDateStamp [$sUser]: Writing Mail Input file to $sMailOutputFile \n";
                             $fpm = fopen($sMailOutputFile, "w");
                             fwrite($fpm, implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country","EmailAddress", "FromFullName","FromAddress1","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1", "Reference2", "Reference3","Reference4"))).fwrite($fpm, "\r\n");

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
        return true;
}


echo "$sDateStamp [$sUser]: Ending Script";

?> 
