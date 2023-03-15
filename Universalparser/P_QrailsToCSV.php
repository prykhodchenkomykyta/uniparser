<?php 
/******************************
Author: Jean-Eric Pierre-Louis
Company: Pierre & Rady LLC
Date: 01/19/2022
Revision: 09/08/2022
Name: Radovan Jakus
Version: 2.6
Notes: Notes: Adding key words and locking the file
******************************/


//Production Environment
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/qrails/";
//$sOutputDir = "/home/erutberg/Radovan/DataPrep/IN/qrails/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sBulkOutputDir = "/var/TSSS/Files/USPS/BULK/";
$sBulkFedexOutputDir =  "/var/TSSS/Files/FEDEX/BULK/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sMailMergeOutputDir = "/var/TSSS/Files/MAILMERGE/";
$sFedexOutputDir = "/var/TSSS/Files/FEDEX/";
//$sProcessedDir = "/home/erutberg/Radovan/DataPrep/IN/qrails/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/qrails/";
$sProductConfigFile = "/home/erutberg/Radovan/Products_Configuration_Qrails.csv";
$sConfirmationReportDir = "/var/TSSS/Files/Reports/";
$sShipmentReportDir = "/var/TSSS/Files/Reports/galileo/waiting/";
$SerialNumberLocal ="/home/erutberg/Radovan/SerialNumberCounter.csv";
$sSerialNumberurl = "https://atlas.tagsystems.net/barcode/serial/";
$sCompositeFieldReference1Dir = "/home/erutberg/Radovan/Reference1.php";

//Test Environment
// $sInputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\in\\";
// $sOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\out\\";
// $sBulkOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\out\\USPS\\BULK\\";
// $sBulkFedexOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\out\\FEDEX\\BULK\\";
// $sMailOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\out\\USPS\\";
// $sMailMergeOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\out\\MAILMERGE\\";
// $sFedexOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\out\\FEDEX\\";
// $sProcessedDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\in\\";
// $sProductConfigFile = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\Products_Configuration_Qrails.csv";
// $sConfirmationReportDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\out\\REPORTS\\";
// $sShipmentReportDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\out\\REPORTS\\";
// $SerialNumberLocal ="D:\\Workspace\\TagSystem\\Parser_Plugin\\Qrails\\SerialNumberCounter.csv";
// $sSerialNumberurl = "https://atlas.tagsystems.net/barcode/serial/";
// $sCompositeFieldReference1Dir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Reference1.php";

ob_start();
header('Content-Type: application/json');
date_default_timezone_set ("America/New_York");
$sDateStamp = date('Y-m-d H:i:s', time());
$sUser = get_current_user();

$sScriptFullName=(__FILE__);

$key = ftok($sScriptFullName, "1");
$lock = sem_get($key, 1);
if (sem_acquire($lock, 1) !== false) {
     
        $sScriptName = basename($sScriptFullName);
            echo "$sDateStamp [$sUser]: Starting Script:  $sScriptName \n";


        //Mailer Information
        $BarcodeID = "00";
        $ServiceTypeID = "270";
        $MailerID = "902695246";
        $maxRec = 1000;
        $SerialNumberOfDigits = (strlen($MailerID)==9)? 6 : 9;

        $sDataPrepProfile;
        $iNumberOfRecords;
        $sBIN;
        $aBINs = getProductsList($sProductConfigFile);
        $aErrors = array();
        $aProducts;
        $iNoErrorRecs;
        $bIsExtendedBINused = false;
        $aDataPrepOutputData = array();
        $aBulkDataPrepOutputData = array();
        define('MAX_CSV_FIELDS_QRAILS',55);

        


        //   echo"aBINs:\n";
        //   print_r($aBINs);

        $aOptions = getopt("", array("keyword::","directory::"));
        $sInputFilePath;

        $bDataProcessed = false;
        $bMailProcessed = false;

//Update optiosn
        if(!empty($aOptions['keyword']) && !empty($aOptions['directory'])){
            $sKeyWords = $aOptions['keyword'];
            $sInputDir = $aOptions['directory'];
            $sListOfKeyWords = explode(",",$sKeyWords);
            $aInputFiles = array();
            foreach($sListOfKeyWords as $sPattern)
            {
                $aInputFiles = array_merge($aInputFiles, glob("$sInputDir*$sPattern*.csv"));
                
            }
            echo "$sDateStamp [$sUser]: Using filename pattern in selected directory $sInputDir\n";
                if($aInputFiles){
                foreach($aInputFiles as $sInputFilePath){
                    echo "\t".basename($sInputFilePath)." \n";
                }
                    $file = 0;

                foreach($aInputFiles as $sInputFilePath){
                        echo "\n\n$sDateStamp [$sUser]: START PROCESSING FILE: $sInputFilePath \n\n";
                        progressBar(++$file,count($aInputFiles));
                        $startTime = hrtime(true);

                        $ParsedData = Parser($sInputFilePath);
                        $CleansedData = ConfirmationReportErrorCheck($ParsedData,$sInputFilePath);

                        if($CleansedData)
                        {
                            $bDataProcessed = DataPrepInput($CleansedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
                            $bMailProcessed = MailingInput($CleansedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
                        }  
                            $sProcessedFilename = basename($sInputFilePath);
                            $bFileMoved = rename($sInputFilePath , $sProcessedDir.$sProcessedFilename);
                            if($bFileMoved)
                            {
                                echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sProcessedFilename \n";
                                echo "$sDateStamp [$sUser]: Total Number of records: $iNumberOfRecords in file: $sProcessedFilename  \n"; 
                                echo "$sDateStamp [$sUser]: Total Number of good records: ".($iNumberOfRecords-$iNoErrorRecs)." in file: $sProcessedFilename  \n"; 
                                echo "$sDateStamp [$sUser]: Total Number records that errored out: $iNoErrorRecs in file: $sProcessedFilename  \n\n"; 
                                if(isset($bDataProcessed))
                                {   
                                    getDetailOverview($bDataProcessed);
                        
                                    unset($aDataPrepOutputData);
                                    unset($aBulkDataPrepOutputData);
                                    unset($bDataProcessed);
                                    unset($bMailProcessed);
                                }
                                echo "$sDateStamp [$sUser]: END PROCESSING FILE: $sInputFilePath";

                                $endTime = hrtime(true);            
                                $executionTime = (($endTime-$startTime)/1e+6)/1000;        
                                echo "\n$sDateStamp [$sUser]: Execution time per file: $executionTime sec \n";


                            }
                            else 
                            {
                                echo "$sDateStamp [$sUser]: Processed File failed to be moved to: $sProcessedDir$sProcessedFilename \n";
                            }
                    }
            }
            else 
            {
                echo "$sDateStamp [$sUser]: There are no files to be processed in directory. The directory does not contain customer files. Directory: $sInputDir\n";
            }
        }
        else if(!empty($aOptions['keyword'])){
            $sPattern = $aOptions['keyword'];
            $sKeyWords = $aOptions['keyword'];
            $sListOfKeyWords = explode(",",$sKeyWords);
            $aInputFiles = array();
            foreach($sListOfKeyWords as $sPattern)
            {
                $aInputFiles = array_merge($aInputFiles, glob("$sInputDir*$sPattern*.csv"));  
            }
            echo "$sDateStamp [$sUser]: Using filename pattern option in predefined folder $sInputDir \n";
            if($aInputFiles){
                foreach($aInputFiles as $sInputFilePath){
                echo "\t".basename($sInputFilePath)." \n";
                }
                $file = 0;

                foreach($aInputFiles as $sInputFilePath){
                    echo "\n\n$sDateStamp [$sUser]: START PROCESSING FILE: $sInputFilePath \n\n";
                    progressBar(++$file,count($aInputFiles));
                    $startTime = hrtime(true);

                    $ParsedData = Parser($sInputFilePath);
                    $CleansedData = ConfirmationReportErrorCheck($ParsedData,$sInputFilePath);

                    if($CleansedData)
                    {
                        $bDataProcessed = DataPrepInput($CleansedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
                        $bMailProcessed = MailingInput($CleansedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
                    }  
                        $sProcessedFilename = basename($sInputFilePath);
                        $bFileMoved = rename($sInputFilePath , $sProcessedDir.$sProcessedFilename);
                        if($bFileMoved)
                        {
                            echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sProcessedFilename \n";
                            echo "$sDateStamp [$sUser]: Total Number of records: $iNumberOfRecords in file: $sProcessedFilename  \n"; 
                            echo "$sDateStamp [$sUser]: Total Number of good records: ".($iNumberOfRecords-$iNoErrorRecs)." in file: $sProcessedFilename  \n"; 
                            echo "$sDateStamp [$sUser]: Total Number records that errored out: $iNoErrorRecs in file: $sProcessedFilename  \n\n"; 
                            if(isset($bDataProcessed))
                            {   
                                getDetailOverview($bDataProcessed);
                    
                                unset($aDataPrepOutputData);
                                unset($aBulkDataPrepOutputData);
                                unset($bDataProcessed);
                                unset($bMailProcessed);
                            }
                            echo "$sDateStamp [$sUser]: END PROCESSING FILE: $sInputFilePath";

                            $endTime = hrtime(true);            
                            $executionTime = (($endTime-$startTime)/1e+6)/1000;        
                            echo "\n$sDateStamp [$sUser]: Execution time per file: $executionTime sec \n";


                        }
                        else 
                        {
                            echo "$sDateStamp [$sUser]: Processed File failed to be moved to: $sProcessedDir$sProcessedFilename \n";
                        }
                }
        }
        else 
        {
            echo "$sDateStamp [$sUser]: There are no files to be processed in directory. The directory does not contain customer files. Directory: $sInputDir\n";
        }
        }    
        else{
        echo "$sDateStamp [$sUser]: Using option to process files from predefined directory automatically. List of files in directory: $sInputDir \n";
        $aInputFiles = glob($sInputDir."*", GLOB_NOSORT);
            if($aInputFiles){
                    foreach($aInputFiles as $sInputFilePath){
                    echo "\t".basename($sInputFilePath)." \n";
                    }
                    $file = 0;

                    foreach($aInputFiles as $sInputFilePath){
                        echo "\n\n$sDateStamp [$sUser]: START PROCESSING FILE: $sInputFilePath \n\n";
                        progressBar(++$file,count($aInputFiles));
                        $startTime = hrtime(true);

                        $ParsedData = Parser($sInputFilePath);
                        $CleansedData = ConfirmationReportErrorCheck($ParsedData,$sInputFilePath);

                        if($CleansedData)
                        {
                            $bDataProcessed = DataPrepInput($CleansedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
                            $bMailProcessed = MailingInput($CleansedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
                        }  
                            $sProcessedFilename = basename($sInputFilePath);
                            $bFileMoved = rename($sInputFilePath , $sProcessedDir.$sProcessedFilename);
                            if($bFileMoved)
                            {
                                echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sProcessedFilename \n";
                                echo "$sDateStamp [$sUser]: Total Number of records: $iNumberOfRecords in file: $sProcessedFilename  \n"; 
                                echo "$sDateStamp [$sUser]: Total Number of good records: ".($iNumberOfRecords-$iNoErrorRecs)." in file: $sProcessedFilename  \n"; 
                                echo "$sDateStamp [$sUser]: Total Number records that errored out: $iNoErrorRecs in file: $sProcessedFilename  \n\n"; 
                                if(isset($bDataProcessed))
                                {   
                                    getDetailOverview($bDataProcessed);
                        
                                    unset($aDataPrepOutputData);
                                    unset($aBulkDataPrepOutputData);
                                    unset($bDataProcessed);
                                    unset($bMailProcessed);
                                }
                                echo "$sDateStamp [$sUser]: END PROCESSING FILE: $sInputFilePath";

                                $endTime = hrtime(true);            
                                $executionTime = (($endTime-$startTime)/1e+6)/1000;        
                                echo "\n$sDateStamp [$sUser]: Execution time per file: $executionTime sec \n";


                            }
                            else 
                            {
                                echo "$sDateStamp [$sUser]: Processed File failed to be moved to: $sProcessedDir$sProcessedFilename \n";
                            }
                    }
            }
            else 
            {
                echo "$sDateStamp [$sUser]: There are no files to be processed in directory. The directory does not contain customer files. Directory: $sInputDir\n";
            }
        }

        if($aErrors!=null)
        {
        echo "\n$sDateStamp [$sUser]: ERRORS during processing, to take action is needed: \n";
        foreach($aErrors as $sErrorMessage)
        {
            echo  $sErrorMessage;
        }
        }

        echo "$sDateStamp [$sUser]: Ending Script";

}
else 
{
    $sScriptName = basename($sScriptFullName);
    echo "\n$sDateStamp [$sUser]: The another instance of the script $sScriptName is running...  \n";
}

function Parser($inputDir)
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
    echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n\n";
    echo "$sDateStamp [$sUser]: Starting Parsing Data\n";
    $aRecordData = array();
    foreach($aInputFile as $aData)
    {
      
        $aInputFileData =str_getcsv($aData);
        $aRecordData[] =$aInputFileData;
    }   

    return  $aRecordData;
}

function ConfirmationReportErrorCheck($input, $inputDir)
{
    
    $sDateStamp = date('Y-m-d H:i:s', time());
    $sUser = get_current_user(); 
    $aConfirmationReportOutputData = array();    
    global $bIsExtendedBINused;
    global $aBINs;
    global $sBIN;
    global $iNumberOfRecords;
    global $sConfirmationReportDir;
    global $aErrors;
    global $sSerialNumberurl;
    global $SerialNumberLocal;
    global $SerialNumberOfDigits;
    global $iNoErrorRecs;

    /*SERIAL NUMBER*/
    $SerialNumber = getSerialNumber($SerialNumberLocal,$SerialNumberOfDigits);
    $SerialNumber = str_pad($SerialNumber,$SerialNumberOfDigits,'0',STR_PAD_LEFT);        
    
    $aFilesWritingStatus = [];

    $Status = "";
    $ErrorCode = "";
    $ErrorDescription = "";
    $bHasError = false;

    echo "\n$sDateStamp [$sUser]: Error Checking Starts: $inputDir \n";

    //SUPPORT VARIABLES
    $aFilesWritingStatus = [];
    $iNoErrorRecs = 0;
    $iNumberOfRecords = 0;

    $iPanPosition ="";
    $sMaskedPAN = "";
    $sMaskedTrack1 ="";
   
    $iNumberOfRecords = count($input);
    $sFilename =  basename($inputDir);
    if($iNumberOfRecords==0)
    {
        echo "\n$sDateStamp [$sUser]: ERROR: The $sFilename does not contain any data, the file is empty.  \n";
        return false;
    }

    

    foreach($input as $iRecID => $aRecord)
    {

        $iRecordNo = 0;
        //VALIDATION DATA
        $ProductID = trim($aRecord['49']);
        $CARD_STOCK_ID = "NA";
        $SHIPPING_METHOD = (empty($aRecord['20'])? "DTC": "BULK");
        $SHIPPING_SERVICE = trim($aRecord['29']);
        $SHIPPING_METHOD = strtoupper($SHIPPING_METHOD);
        $SHIPPING_SERVICE = strtoupper($SHIPPING_SERVICE);

       
            $iRecordNo++;
            //VALIDATION DATA
            $sPAN = trim($aRecord['1']);
            $sBIN = substr($sPAN,0,6);
            $sBINExtended = substr($sPAN,0,8);
            $PAN4 =  substr($sPAN,-4);

            //File Format Validation
            if(count($aRecord)!=MAX_CSV_FIELDS_QRAILS)
            {
                $iNoErrorRecs++;
                $sError = "";
                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, the CSV file has more than expected fields. File contains possible unescaped comma. Max expected CSV fields 54, the fields in the record is ".count($aRecord)."\n"; 
                $aErrors[] = $sError;
                echo $sError;
                $Status = "NOK";
                $ErrorCode = "305";
                $ErrorDescription = "Data Format Error";
                $bHasError= true;
                $ProductProp['Product'] = "NOK";
                $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";

                $Token = trim($aRecord['0']);
                $FileDate = date('Ymd',filemtime($inputDir));
                $FileName = basename($inputDir);
                $CurrentDate = date('Ymd');
                if($Status=="NOK")
                {
                    $Status="ERROR";
                };
    
                //$sBIN;
                //$Status;
                //$ErrorCode;
                //$ErrorDescription;
                $DateReceived = "N/A";
                //$DateReceived = filemtime($sOriginalFile.str_replace("emb","pgp",$sProcessedFilename));
                $CardType = trim($ProductID);
                $Address1 = trim($aRecord['13']);
                $Address2 = trim($aRecord['14']);
                $City = trim($aRecord['15']);
                $State =  trim($aRecord['16']); 
                $SHIP_ZIP = trim($aRecord['17']);
                $ZIPCode = substr(trim($SHIP_ZIP), 0,5);
                $ZIPCodeAddOn = empty(substr($SHIP_ZIP,5)) ? "" : preg_replace("/-/","",substr(trim($SHIP_ZIP),5));
                $Country = trim($aRecord['18']);
                $Country = convertCountry('alpha3',$Country,'alpha2');
                $sEmbName = trim($aRecord['5']);



                $aConfirmationReportOutputData[]=array($FileDate,$FileName,$Token,$PAN4,$sEmbName,$CurrentDate,$Status,$ErrorCode,$ErrorDescription,$sBIN,$CardType,$Address1,$Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn,$Country);

                if($iNumberOfRecords==$iNoErrorRecs)
                {
                    echo "$sDateStamp [$sUser]: ERROR: All the records in  $inputDir contains error, therefor this file cannot be processed.\n";
                    echo "$sDateStamp [$sUser]: \n\n CONFIRMATION REPORT START \n\n";
                    $sConfirmationReportOutputFile = $sConfirmationReportDir.(preg_replace("/(\.).*/","",$FileName)).".conf_rep.csv";
                    $fp = fopen($sConfirmationReportOutputFile,"w");
    
                    foreach($aConfirmationReportOutputData as $row)
                    {
                    
                        //$bFileWriting1 =fwrite($fp, implode(",",$row)).fwrite($fp, "\r\n");
                        $bFileWriting1 =fputcsv($fp, $row);
                        $aFilesWritingStatus[] = $bFileWriting1;
                    }
                        if($bFileWriting1)
                        {
                            echo "$sDateStamp [$sUser]: Report File for file $FileName succesfully written as: $sConfirmationReportOutputFile\n";
                            fclose($fp);
    
                        }
                        else 
                        {
                            echo "$sDateStamp [$sUser]: Writing Report file for file $FileName failed\n";
                            fclose($fp);
                        }
                    return false;
                    }


                if($bHasError)
                {
                        //DO NOT WRITE RECORD TO REST OF THE FILE
                        continue;
                }
            }
            
            if(isset($aBINs[$sBINExtended]))
            {
                $sBIN = $sBINExtended;
                $bIsExtendedBINused = true;
            }
            $sTrack1 = trim($aRecord['50']);
            $sTrack2 = trim(substr($aRecord['51'],1,strlen($aRecord['51'])-2));
            $sTrack2Chip = trim($aRecord['53']);
            $iCVV = trim($aRecord['7']);
            $CVV2 = trim($aRecord['8']);
            $sEmbName = trim($aRecord['5']);
            $sCompanyName = "\"".trim($aRecord['6'])."\"";
            $FullName = trim($aRecord['5']);
            $Company = "";
            $Address1 = trim($aRecord['13']);
            $Address2 = trim($aRecord['14']);
            $City = trim($aRecord['15']);
            $State =  trim($aRecord['16']); 
            $SHIP_ZIP = trim($aRecord['17']);
            $ZIPCode = substr(trim($SHIP_ZIP), 0,5);
            $ZIPCodeAddOn = empty(substr($SHIP_ZIP,5)) ? "" : preg_replace("/-/","",substr(trim($SHIP_ZIP),5));
            $Country = trim($aRecord['18']);
            $Country = convertCountry('alpha3',$Country,'alpha2');

            $Reference3 = $aRecord['0'];


            $iPanPosition = strpos($sPAN,$sBIN);
            $iBINln = 6;
            $iPANln = strlen($sPAN);
            $iMaskedCharsln = abs($iPANln-4-$iBINln);
            if($iPanPosition!==false)
            {
                $sMaskedPAN = substr_replace($sPAN,"XXXXXX",$iPanPosition+$iBINln,$iMaskedCharsln);
            }
            else
            {
                $sMaskedPAN = substr($sPAN, -4);
            }
            


            //ERROR CHECK TO CONFIRM BIN
            if(isset($aBINs[$sBIN]))
            {   
                //ERROR CHECK TO CONFIRM PRODUCT ID
                if(isset($aBINs[$sBIN][trim($ProductID)]))
                {
                    //ERROR CHECK TO CONFIRM CARD STOCK
                    if(isset($aBINs[$sBIN][trim($ProductID)][trim($CARD_STOCK_ID)]))
                    {
                        $ProductProp = $aBINs[$sBIN][trim($ProductID)][trim($CARD_STOCK_ID)];
                        $ProductID = trim($ProductID);
                        $Status = "SUCCESS";
                        $ErrorCode = "0";
                        $ErrorDescription = "";
                        $bHasError= false;

                        $iErrorsPerRecord = 0;
                        if($SHIPPING_METHOD=="DTC")
                        {
                            if(!isset($ProductProp['ShippingMethods'][$SHIPPING_SERVICE]))
                            {
                                $iErrorsPerRecord++;
                                $sError = "";
                                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir , in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , the shipping method $SHIPPING_METHOD and it's shipping service $SHIPPING_SERVICE is invalid. The valid shipping service for product ".$ProductProp['Product']." and shipment method 00001 Standard Shipment are 00-".$ProductProp['ShippingMethods']['00'].", 11-".$ProductProp['ShippingMethods']['11'].", 12-".$ProductProp['ShippingMethods']['12'].", 13-".$ProductProp['ShippingMethods']['13'].", and for 00002 Bulk Shipment are 10-".$ProductProp['ShippingMethodsBulk']['10'].", 11-".$ProductProp['ShippingMethodsBulk']['11'].", 12-".$ProductProp['ShippingMethodsBulk']['12'].", 13-".$ProductProp['ShippingMethodsBulk']['10'].", each is configured in products_configuration_Marqeta.csv  \n";
                                $aErrors[] = $sError;
                                echo $sError;
                                $Status = "NOK";
                                $ErrorCode = "307";
                                $ErrorDescription = "Wrong Shipping Method";
                                $bHasError= true;
                                $ProductProp['Product'] = "NOK";
                                $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                $ProductID = trim($ProductID);
                            }
                        }
                        else if(preg_match('/BULK/',$SHIPPING_METHOD))
                        {
                            if(!isset($ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE]))
                            {
                                $iErrorsPerRecord++;
                                $sError = "";
                                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir , in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , the shipping method $SHIPPING_METHOD and it's shipping service $SHIPPING_SERVICE is invalid. The valid shipping service for product ".$ProductProp['Product']." and shipment method 00001 Standard Shipment are 00-".$ProductProp['ShippingMethods']['00'].", 11-".$ProductProp['ShippingMethods']['11'].", 12-".$ProductProp['ShippingMethods']['12'].", 13-".$ProductProp['ShippingMethods']['13'].", and for 00002 Bulk Shipment are 10-".$ProductProp['ShippingMethodsBulk']['10'].", 11-".$ProductProp['ShippingMethodsBulk']['11'].", 12-".$ProductProp['ShippingMethodsBulk']['12'].", 13-".$ProductProp['ShippingMethodsBulk']['10'].", each is configured in products_configuration_Marqeta.csv  \n";
                                $aErrors[] = $sError;
                                echo $sError;
                                $Status = "NOK";
                                $ErrorCode = "307";
                                $ErrorDescription = "Wrong Shipping Service";
                                $bHasError= true;
                                $ProductProp['Product'] = "NOK";
                                $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                $ProductID = trim($ProductID);
                            }
                        }
                        else
                        {
                            $iErrorsPerRecord++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , the shipping method $SHIPPING_METHOD is invalid. Valid options are 00001 for Standard Shipment and 00002 for Bulk Shipment. \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK";
                            $ErrorCode = "307";
                            $ErrorDescription = "Wrong Shipping Method";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);

                        }
            
                        //DATA VALIDATION
                        if(!preg_match('/%?B\d{1,19}\^[\\\[\w\s.()\-$\/\]]{2,26}\^([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\??/',$aRecord['50']))
                        {
                            
                            $iPanPosition = strpos(($sTrack1),$sBIN);
                            if($iPanPosition!==false)
                            {
                                $sMaskedTrack1 = substr_replace($sTrack1,"XXXXXX",$iPanPosition+strlen($sBIN),$iMaskedCharsln);
                            }
                            else
                            {
                                $sMaskedTrack1 = "unable to mask the track data - view not allowed";
                            }
                            $iErrorsPerRecord++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , Track1 data have incorrect magnetic stripe format, received value: ".$sMaskedTrack1." \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - Magnetic Stripe Track1 format";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                        }                          
                    }
                    else
                    {
                        $iNoErrorRecs++;
                        $sError = "";
                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN $sMaskedPAN ,the card stock ID: ".trim($CARD_STOCK_ID)." is not defined in Products_configuration.csv. Please, review products configuration. \n";
                        $aErrors[] = $sError;
                        echo $sError;
                        $Status = "NOK";
                        $ErrorCode = "303";
                        $ErrorDescription = "The card stock ID from the file: ".$sEmbName.", is unknown";
                        $bHasError= true;
                        $ProductProp['Product'] = "NOK";
                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                        $ProductID = trim($ProductID);                       
                    }

                    if($iErrorsPerRecord>0)
                    {
                        $iNoErrorRecs++;
                        $iErrorsPerRecord=0;
                    }
                }
                else
                {
                    $iNoErrorRecs++;
                    $sError = "";
                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN $sMaskedPAN ,the product ID: ".trim($ProductID)." is not defined in Products_configuration_Marqeta.csv. Please, review products configuration. \n";
                    $aErrors[] = $sError;
                    echo $sError;
                    $Status = "NOK";
                    $ErrorCode = "302";
                    $ErrorDescription = "The product ID from the file: ".trim($ProductID).", is unknown";
                    $bHasError= true;
                    $ProductProp['Product'] = "NOK";
                    $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                    $ProductID = trim($ProductID);
                }      
            }
            else
            {
                $iNoErrorRecs++;
                $sError = "";
                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." , with PAN $sMaskedPAN the BIN: ".$sBIN." is not defined in Products_configuration.csv. Please, review products configuration.\n";
                $aErrors[] = $sError;
                echo $sError;
                $Status = "NOK";
                $ErrorCode = "301";
                $ErrorDescription = "The BIN from the file: ".$sBIN.", is unknown";
                $bHasError= true;
                $ProductProp['Product'] = "NOK";
                $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                $ProductID = trim($ProductID);
            }

            /*CONFIRMATION REPORT*/
            $FileDate = date('Ymd',filemtime($inputDir));
            $FileName = basename($inputDir);
            $Token = $Reference3;
            $PAN4;
            $sEmbName;
            $CurrentDate = date('Ymd');
            if($Status=="NOK")
            {
                $Status="ERROR";
            };
            $ErrorCode;
            $ErrorDescription;
            $sBIN; 
            $CardType = $ProductProp['Product'];
            $Address1;
            $Address2;
            $City;
            $State;
            $ZIPCode;
            $ZIPCodeAddOn;
            $Country;         
            
            $aConfirmationReportOutputData[]=array($FileDate,$FileName,$Token,$PAN4,$sEmbName,$CurrentDate,$Status,$ErrorCode,$ErrorDescription,$sBIN,$CardType,$Address1,$Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn,$Country);
             

            if($iNumberOfRecords==$iNoErrorRecs)
            {
                echo "$sDateStamp [$sUser]: ERROR: All the records in  $inputDir contains error, therefor this file cannot be processed.\n";
                echo "$sDateStamp [$sUser]: \n\n CONFIRMATION REPORT START \n\n";
                $sConfirmationReportOutputFile = $sConfirmationReportDir.(preg_replace("/(\.).*/","",$FileName)).".conf_rep.csv";
                $fp = fopen($sConfirmationReportOutputFile,"w");

                foreach($aConfirmationReportOutputData as $row)
                {
                
                    //$bFileWriting1 =fwrite($fp, implode(",",$row)).fwrite($fp, "\r\n");
                    $bFileWriting1 =fputcsv($fp, $row);
                    $aFilesWritingStatus[] = $bFileWriting1;
                }
                    if($bFileWriting1)
                    {
                        echo "$sDateStamp [$sUser]: Report File for file $FileName succesfully written as: $sConfirmationReportOutputFile\n";
                        fclose($fp);

                    }
                    else 
                    {
                        echo "$sDateStamp [$sUser]: Writing Report file for file $FileName failed\n";
                        fclose($fp);
                    }
                return false;

            }

        
            if($bHasError)
            {
                //DO NOT WRITE RECORD TO REST OF THE FILE
                unset($input[$iRecID]);
                continue;
            }
            if(strlen($SerialNumber)>$SerialNumberOfDigits)
            {
                $SerialNumber = 1;
            }
    
            $input[$iRecID]['SerialNumber'] = str_pad($SerialNumber++,$SerialNumberOfDigits,'0',STR_PAD_LEFT);

            setSerialNumber($SerialNumberLocal,$SerialNumberOfDigits,$SerialNumber);

        
     
    }
    
    if(isset($aBINs[$sBIN]['Customer']))
        $sCustomerName = $aBINs[$sBIN]['Customer'];
    else 
    {
        $sCustomerName = "Customer name could not be identified";
    }
    echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n";
    echo "$sDateStamp [$sUser]: BIN: $sBIN \n";

    echo "$sDateStamp [$sUser]: \n\n CONFIRMATION REPORT START \n\n";
    $sConfirmationReportOutputFile = $sConfirmationReportDir.(preg_replace("/(\.).*/","",$FileName)).".conf_rep.csv";
    $fp = fopen($sConfirmationReportOutputFile,"w");

    foreach($aConfirmationReportOutputData as $row)
    {
    
        //$bFileWriting1 =fwrite($fp, implode(",",$row)).fwrite($fp, "\r\n");
        $bFileWriting1 =fputcsv($fp, $row);
        $aFilesWritingStatus[] = $bFileWriting1;
    }
        
    if($bFileWriting1)
    {
        echo "$sDateStamp [$sUser]: Report File for $FileName succesfully written as: $sConfirmationReportOutputFile\n";
        fclose($fp);

    }
    else 
    {
        echo "$sDateStamp [$sUser]: Writing Report for file $FileName failed\n";
        fclose($fp);
    }

    // echo"PRINTINPUT\n";
    //  print_r($input);
 
        return $input;


}

function DataPrepInput($input, $inputDir, $outputDir)
{
    global $sBINs;
    global $aBINs;
    global $maxRec;
    global $sDateStamp;
    global $sUser;
    global $sDataPrepProfile;
    global $aDataPrepOutputData;
    global $aBulkDataPrepOutputData;
  
    /*DATAPREP*/
    $aDataPrepOutputData = array();
    $aBulkDataPrepOutputData = array();
    
    $sFileName = basename($inputDir);
    $aFilesWritingStatus = [];
    $ProductID = "";

    $iRecordNo=0;
    $iNumberOfRecords =  count($input);
    
    foreach($input as $aRecord)
    {   

        $ProductID = trim($aRecord['49']);
        $CARD_STOCK_ID = "NA";
        $SHIPPING_METHOD = (empty($aRecord['20'])? "DTC": "BULK");
        $SHIPPING_SERVICE = trim($aRecord['29']);
        $SHIPPING_METHOD = strtoupper($SHIPPING_METHOD);
        $SHIPPING_SERVICE = strtoupper($SHIPPING_SERVICE);

        $sPAN = trim($aRecord['1']);
        $sBIN = substr($sPAN,0,6);
        $sBINExtended = substr($sPAN,0,8);
        if(isset($aBINs[$sBINExtended]))
        {
            $sBIN = $sBINExtended;
        }
        // print_r($aBINs);
        // echo"BIN: $sBIN \n PRODUCT ID $ProductID \n CARDSTOCK $CARD_STOCK_ID";
        $ProductProp = $aBINs[$sBIN][trim($ProductID)][trim($CARD_STOCK_ID)];
        $sCustomerName = $aBINs[$sBIN]['Customer'];
        if($SHIPPING_METHOD=="DTC")
        {
            $SHIPPING_METHOD = $ProductProp['ShippingMethods'][$SHIPPING_SERVICE];
            $SHIPPING_METHOD_PROD = $ProductProp['ShippingMethods'][$SHIPPING_SERVICE];
        }
        else if(preg_match('/BULK/',$SHIPPING_METHOD))
        {
            $SHIPPING_METHOD =  trim($aRecord['29'])."-".$ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
            $SHIPPING_METHOD_PROD = $ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
        }

       
        ++$iRecordNo;
        $sTrack1 = trim($aRecord['50']);
        $sTrack2 = substr(trim($aRecord['51']),1);
        $sTrack2Chip = trim($aRecord['53']);
       
        $PSN = trim($aRecord['10']);
        $sPINBlock =  trim($aRecord['39']);
        $CVC2 = trim($aRecord['8']);
        $sEmbName = trim($aRecord['5']);
    
    
    
        $sBatchID = $iNumberOfRecords."/".$iRecordNo;
        $sUniqueNumber= "dc5413d196ee7fcb80f0eeb97e79cfd60b1e54cf";
        $sNotUsed1 = "0000";
        $sNotUsed2 = "00";
        $sNotUsed3 = "000";
        $sDataPrepProfile = $ProductProp['Profile'];
        $sNotUsed4 = "0000000";
        $sChipData = "$sTrack1#$sTrack2#$sTrack2Chip#$PSN#$CVC2#$sEmbName";
        
        //DATAPREP RESULT
        $BatchID = "";
        if(preg_match('/BULK/',$SHIPPING_METHOD))   
        {
            $GroupID = trim($aRecord['20']);
            $BulkFullName = trim($aRecord['22']);
            $BulkCompany = trim($aRecord['21']);
            $BulkAddress1 = trim($aRecord['24']);
            $BulkAddress2 = trim($aRecord['25']);
            $BulkCity = trim($aRecord['26']);
            $BulkState =  trim($aRecord['27']);  
            $BulkZIPCode = substr(trim($aRecord['28']), 0,5);
            $BulkZIPCodeAddOn = empty(substr($aRecord['28'],5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['28']),5));
            $BulkCountry =  (strlen($aRecord['31'])==2) ? trim($aRecord['31']) : convertCountry('alpha3',$aRecord['31'],'alpha2');

            $aDataPrepOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID."_".$GroupID."_". $BulkFullName."_".$BulkCompany."_".$BulkAddress1."_".$BulkAddress2."_".$BulkCity."_".$BulkZIPCode."_".$BulkZIPCodeAddOn."_".$BulkCountry][] = array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData); 

        }
        else
        {
            $aDataPrepOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][] = array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData); 
        }

    }
   
    if(isset($aDataPrepOutputData) && count($aDataPrepOutputData)!=0)
    {
            echo "$sDateStamp [$sUser]: \n\n DATAPREP START \n\n";
            // echo "DataPrepArray";
            // print_r($aDataPrepOutputData);
            foreach($aDataPrepOutputData as $keyShipment => $aShippingRecord)     
            {

                // echo "aShippingRecord\n";
                // print_r($aShippingRecord);

                // $sShippingName = $ProductProp['ShippingMethods'][$keyShipment];
                //echo"\nSHIPPING NAME $sShippingName\n";
                foreach($aShippingRecord as $keyProduct => $aProductRecord)
                {
                    foreach($aProductRecord as $keyCardStock => $aCardStockBatchRecord)
                    {
                        $iGroup = 0;

                        foreach($aCardStockBatchRecord as $BatchID => $aCardStockRecord)
                        {
                                if(!empty(explode("_",$BatchID)[1]))
                                     $BatchID = explode("_",$BatchID)[1]."_".++$iGroup;
                                


                            
                                $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                                $sShippingName = $keyShipment;
                                $sProductName =  $sProductProp['Product'];
                                echo "$sDateStamp [$sUser]: DataPrep: Records Shipment $sShippingName and per product $sProductName: ".count($aCardStockRecord)."\n";
                            
                                
                                $bFileWriting1; 
                                $aExistingFile = null;
                                $bExistingFile = false;
                                $sDataToWrite = null;
                                $numSplits = 0;
                                $recordsDone = 0;
                                $fp = null;
                                $neededSplits = 0;
                                if(count($aCardStockRecord)>$maxRec)
                                {
                                    $neededSplits = ceil(count($aCardStockRecord) / $maxRec);
                                }
                                foreach($aCardStockRecord as $row) 
                                { 

                                    if($recordsDone == $maxRec)
                                        $recordsDone = 0;
                                    if($recordsDone == 0)
                                    {
                                        if($numSplits > 0)
                                            fclose($fp);
                                        ++$numSplits;

                                        $sDataPrepOutputFile = $outputDir."MS_CHIPREP_".$sProductName."_".$sShippingName."_".$BatchID."_";
                                        if($neededSplits > 0)
                                            $sDataPrepOutputFile = $sDataPrepOutputFile.$numSplits."_of_".$neededSplits."_";
                                        $sDataPrepOutputFile = $sDataPrepOutputFile.$sFileName;

                                        //CHECK IF FILE EXISTS:
                                        $bExistingFile =file_exists($sDataPrepOutputFile);
                                        if($bExistingFile)
                                        {
                                            //$fp = fopen($sDataPrepOutputFile, "a");
                                            $fp = fopen($sDataPrepOutputFile, "a+");
                                            while(!feof($fp))
                                            {
                                                $aExistingFile[] = fgets($fp);
                                            }
                                        // $aExistingFile = file($sDataPrepOutputFile, FILE_SKIP_EMPTY_LINES);

                                        }
                                        else
                                        {
                                            $fp = fopen($sDataPrepOutputFile, "w");
                                        }
                                            
                                    }
                                    
                                    $sDataToWrite =   implode(';',$row)."\r\n";
                                    if($bExistingFile)
                                    {
                                        foreach($aExistingFile as $index => $aExistingFileRow)
                                                {
                    
                                                
                                                    if($aExistingFileRow === $sDataToWrite){
                                                        $recordsDone++;
                                                        continue 2;
                                                    }
                                                    else
                                                    {
                                                        if(explode(";",$aExistingFileRow)[0]===explode(";",$sDataToWrite)[0])
                                                        {
                                                            $aExistingFile[$index] =  $sDataToWrite;
                                                            fclose($fp);
                                                            $fp = fopen($sDataPrepOutputFile, "w");
                                                            foreach($aExistingFile as $rewrite)
                                                            {
                                                                $bFileWriting1 = fwrite($fp, $rewrite);
                                                            }
                                                            $recordsDone++;
                                                            continue 2;
                                                            
                                                        }
                                                    }
                    
                                                }
                                    }
                                    //echo("\nbFileWriting: $bFileWriting1");
                                    $bFileWriting1 = fwrite($fp, $sDataToWrite);
                                    //echo("\nbFileWriting: $bFileWriting1");
                                    $aFilesWritingStatus[] = $bFileWriting1;
                                    $recordsDone++;
                                } 
                                unset($bExistingFile);
                                if(!isset($bFileWriting1))
                                {
                                    
                                    echo "$sDateStamp [$sUser]: File already exists\n";
                                    fclose($fp);
                                }
                                else if($bFileWriting1)
                                {
                                    echo "$sDateStamp [$sUser]: File succesfully written as: $sDataPrepOutputFile.\n";
                                    fclose($fp);
                                }
                                else
                                {
                                    echo "$sDateStamp [$sUser]: Writing file failed\n";
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
            }
    }

  

    return $aDataPrepOutputData;


         
   
}

function MailingInput($input, $inputDir, $outputDir)
{
    global $sDateStamp;
    global $sUser;
    global $aBINs;
    global $sBINs;
    global $maxRec;
    global $sBulkFedexOutputDir;
    global $sFedexOutputDir;
    global $sMailOutputDir;
    global $sBulkOutputDir;
    global $sMailMergeOutputDir;
    global $sCompositeFieldReference1Dir;

    $sFileName = basename($inputDir);
            
    $bBulk = false;
            /*MAILING*/
            $aMailShippingOutputData;
            $iRecordNo = 0;
            $aFilesWritingStatus = [];

            foreach($input as $aRecord)
            {
                $ProductID = trim($aRecord['49']);
                $CARD_STOCK_ID = "NA";
                $SHIPPING_METHOD = (empty(trim($aRecord['20']))? "DTC": "BULK");
                $SHIPPING_SERVICE = trim($aRecord['29']);
                $SHIPPING_METHOD = strtoupper($SHIPPING_METHOD);
                $SHIPPING_SERVICE = strtoupper($SHIPPING_SERVICE);
       
                $sPAN = trim($aRecord['1']);
                $sBIN = substr($sPAN,0,6);
                $sBINExtended = substr($sPAN,0,8);
                if(isset($aBINs[$sBINExtended]))
                {
                    $sBIN = $sBINExtended;
                }
                $ProductProp = $aBINs[$sBIN][trim($ProductID)][trim($CARD_STOCK_ID)];
                $sCustomerName = $aBINs[$sBIN]['Customer'];

                if($SHIPPING_METHOD=="DTC")
                {
                    $SHIPPING_METHOD = $ProductProp['ShippingMethods'][$SHIPPING_SERVICE];
                    $SHIPPING_METHOD_PROD = $ProductProp['ShippingMethods'][$SHIPPING_SERVICE];
                }
                else if(preg_match('/BULK/',$SHIPPING_METHOD))
                {
                    $SHIPPING_METHOD =  trim($aRecord['29'])."-".$ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
                    $SHIPPING_METHOD_PROD = $ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
                }

                //Used by Reference1.php
                $sCustomerName3 = substr($sCustomerName,0,3);
                $sProductName = $ProductProp['Product'];
                $sProductName3 = substr($sProductName,0,3);
                $sSerialNumber = $aRecord['SerialNumber'];
                $sPAN4 = substr($sPAN, -4);
                

                ++$iRecordNo;
                $FullName = trim($aRecord['5']);
                $Company = "";
                $Address1 = trim($aRecord['13']);
                $Address2 = trim($aRecord['14']);
                $City = trim($aRecord['15']);
                $State =  trim($aRecord['16']); 
                $ZIPCode = trim(substr($aRecord['17'],0,5));
                $ZIPCodeAddOn = empty(substr($aRecord['17'],5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['17']),5)); //ADDED ZIPAdd-On
                $Country = (trim($aRecord['18']="USA"))? "US":trim($aRecord['18']);
                $EmailAddress;
                $FromFullName =  $ProductProp["FromFullName"];
                $FromAddress1 =  $ProductProp["FromAddress1"]; 
                $FromAddress2 =  $ProductProp["FromAddress2"]; 
                $FromCity = $ProductProp["FromCity"];
                $FromState=  $ProductProp["FromState"];
                $FromCountry =  $ProductProp["FromCountry"];
                $FromZIPCode =  $ProductProp["FromZIPCode"];
                $Amount;
                $ServiceType = $ProductProp["ServiceType"];
                $PackageType = $ProductProp["PackageType"];
                $WeightOz = $ProductProp["WeightOz"];
                $ShipDate = $ProductProp["ShipDate"];
                    $ImageType = "Pdf";
                $Reference1 = include($sCompositeFieldReference1Dir);
               // $Reference1 = substr($sCustomerName,0,3)."_".trim($aRecord['SerialNumber'])."_".substr($aRecord['1'], -4);
                $Reference2 = strtoupper(hash("sha256",trim($aRecord['1']), false));
                $Reference3 = trim($aRecord['0']);
                $Reference4 = "";

                //MAILMERGE
                $ValidFrom =  trim($aRecord['2']);
                $MemberSince =  trim($aRecord['3']);
                $DDAAccount =  trim($aRecord['11']);
                $Currency = trim($aRecord['19']);
                $ImageIDFront =  trim($aRecord['33']);
                $ImageIDBack =  trim($aRecord['34']);
                $ExternalCardID =  trim($aRecord['37']);
                $ExteralCHID = trim($aRecord['38']);
                $AdditionalField1 = trim($aRecord['40']);
                $AdditionalField2 = trim($aRecord['41']);
                $AdditionalField3 = trim($aRecord['42']);
                $AdditionalField4 = trim($aRecord['43']);
                $AdditionalField5 = trim($aRecord['44']);
                $AdditionalField6 = trim($aRecord['45']);
                $AdditionalField7 = trim($aRecord['46']);
                $AdditionalField8 = trim($aRecord['47']);

             

                if(empty($ServiceType)){
                    switch($SHIPPING_METHOD_PROD)
                    {
                        case "USPS":
                            $ServiceType = "US-FC";
                            break;      
                        case "USPS_TR":
                            $ServiceType = "US-FC";
                            break;                          
                        case "USPS_PM":
                            $ServiceType = "US-PM";
                            break;
                        default:
                            $ServiceType = "US-FC";
                            break;
                    }
                }

                if(empty($PackageType)){
                    switch($SHIPPING_METHOD_PROD)
                    {
                        case "USPS":              
                                $PackageType = "Letter";
                            break;      
                        case "USPS_TR":
                                $PackageType = "Package";
                            break;           
                        case "USPS_PM":
                                $PackageType = "Large Envelope or Flat";
                            break;
                        default:
                                $PackageType = "Letter";
                            break;
                    }
                }
                $BatchID = "";
          



                if(preg_match('/BULK/',$SHIPPING_METHOD))   
                {
                    
                    $bBulk = true;
                    $GroupID = trim($aRecord['20']);
                    $BulkFullName = trim($aRecord['22']);
                    $BulkCompany = trim($aRecord['21']);
                    $BulkAddress1 = trim($aRecord['24']);
                    $BulkAddress2 = trim($aRecord['25']);
                    $BulkCity = trim($aRecord['26']);
                    $BulkState =  trim($aRecord['27']);  
                    $BulkZIPCode = substr(trim($aRecord['28']), 0,5);
                    $BulkZIPCodeAddOn = empty(substr($aRecord['28'],5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['28']),5));
                    $BulkCountry =  (strlen($aRecord['31'])==2) ? trim($aRecord['31']) : convertCountry('alpha3',$aRecord['31'],'alpha2');
                    //TODO Grouping
                    $Reference1Bulk = "GROUP_ID_".$GroupID;
                    $Reference2Bulk = "";
                    $Reference3Bulk = "";
                    $Reference4Bulk = "";
                    $aBulkMultiShippingOutputData[$SHIPPING_METHOD_PROD][$BatchID."_".$GroupID."_". $BulkFullName."_".$BulkCompany."_".$BulkAddress1."_".$BulkAddress2."_".$BulkCity."_".$BulkZIPCode."_".$BulkZIPCodeAddOn."_".$BulkCountry][] = array($Company, $BulkFullName, $BulkAddress1, $BulkAddress2,$BulkCity,$BulkState,$BulkZIPCode,$BulkZIPCodeAddOn, $BulkCountry, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1Bulk, $Reference2Bulk, $Reference3Bulk, $Reference4Bulk);
                    //MAILING RESULT
                    $aMailMergeShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID."_".$GroupID."_". $BulkFullName."_".$BulkCompany."_".$BulkAddress1."_".$BulkAddress2."_".$BulkCity."_".$BulkZIPCode."_".$BulkZIPCodeAddOn."_".$BulkCountry][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4,$ValidFrom,$MemberSince,$DDAAccount,$Currency,$ImageIDFront,$ImageIDBack,$ExternalCardID,$ExteralCHID,$AdditionalField1,$AdditionalField2,$AdditionalField3,$AdditionalField4,$AdditionalField5,$AdditionalField6,$AdditionalField7,$AdditionalField8);
                    $aMailShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID."_".$GroupID."_". $BulkFullName."_".$BulkCompany."_".$BulkAddress1."_".$BulkAddress2."_".$BulkCity."_".$BulkZIPCode."_".$BulkZIPCodeAddOn."_".$BulkCountry][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4);

                }
                else
                {
                    $aMailMergeShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4,$ValidFrom,$MemberSince,$DDAAccount,$Currency,$ImageIDFront,$ImageIDBack,$ExternalCardID,$ExteralCHID,$AdditionalField1,$AdditionalField2,$AdditionalField3,$AdditionalField4,$AdditionalField5,$AdditionalField6,$AdditionalField7,$AdditionalField8);
                    $aMailShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4);
                }

                $aMailMergeShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4","ValidFrom","MemberSince","DDAAccount","Currency","ImageIDFront","ImageIDBack","ExternalCardID","ExteralCHID","AdditionalField1","AdditionalField2","AdditionalField3","AdditionalField4","AdditionalField5","AdditionalField6","AdditionalField7","AdditionalField8"."\r\n"));
                $aMailShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4"."\r\n"));

    
    
    
            }


            if(isset($aMailShippingOutputData) && count($aMailShippingOutputData)!=0)
            {
                echo "$sDateStamp [$sUser]: \n\n MAILING START \n\n";
                // echo "aMailShippingOutputData";
                // print_r($aMailShippingOutputData);
                foreach($aMailShippingOutputData as $keyShipment => $aShippingRecord) 
                {                            
                    foreach($aShippingRecord as $keyProduct => $aProductRecord)
                    {
                        foreach($aProductRecord as $keyCardStock => $aCardStockBatchRecord)
                        {
                            $iGroup = 0;

                            foreach($aCardStockBatchRecord as $BatchID => $aCardStockRecord)
                            {
                                    if(!empty(explode("_",$BatchID)[1]))
                                         $BatchID = explode("_",$BatchID)[1]."_".++$iGroup;
    
                                    $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                                    $sShippingName = $keyShipment;
                                    $sProductName =  $sProductProp['Product'];
                                    if(preg_match('/FEDx/',$sShippingName))
                                    {
                                        $mailOutputDir = $sFedexOutputDir;
                                        if(preg_match('/BULK/',$sShippingName))
                                            $mailOutputDir = $sBulkFedexOutputDir;
                                    }
                                    else
                                    {
                                        $mailOutputDir = $sMailOutputDir;
                                        if(preg_match('/BULK/',$sShippingName))
                                            $mailOutputDir = $sBulkOutputDir;
                                    }
                
                
                                    echo "$sDateStamp [$sUser]: Mailing: Records per Shipment $sShippingName and per product $sProductName: ".count($aCardStockRecord)."\n";
                                    $bFileWriting1; 
                                    $aExistingFile = null;
                                    $bExistingFile = false;
                                    $sDataToWrite = null;
                                
                                    $numSplits = 0;
                                    $recordsDone = 0;
                                    $fp = null;
                                    $neededSplits = 0;

                                    if(count($aCardStockRecord)>$maxRec)
                                    {
                                        $neededSplits = ceil(count($aCardStockRecord) / $maxRec);
                                    }
                            
                                    foreach ($aCardStockRecord as $row) 
                                    { 
                                        
                                        if($recordsDone == $maxRec)
                                            $recordsDone = 0;
                                        if($recordsDone == 0)
                                        {
                                            if($numSplits > 0)
                                                fclose($fp);
                                            ++$numSplits;
                                            $sDataPrepOutputFile =  $mailOutputDir."MAIL_".$sProductName."_".$sShippingName."_".$BatchID."_";
                                            if($neededSplits > 0)
                                                $sDataPrepOutputFile = $sDataPrepOutputFile.$numSplits."_of_".$neededSplits."_";
                                            $sDataPrepOutputFile = $sDataPrepOutputFile.$sFileName;
                                        
                                            //CHECK IF FILE EXISTS:
                                            $bExistingFile=file_exists($sDataPrepOutputFile);
                                            //echo("MAIN_FILE_EXISTS".$bExistingFile);
                                            if($bExistingFile) 
                                            {
                                                $fp = fopen($sDataPrepOutputFile, "a+");
                                                fgets($fp);
                                                while(!feof($fp))
                                                {
                                                    $aExistingFile[] = fgets($fp);
                                                }
                                                //echo"aExistingFile";
                                                //print_r($aExistingFile);
                                            }
                                            else
                                            {
                                                $fp = fopen($sDataPrepOutputFile, "w");
                                                fwrite($fp, $aMailShippingOutputDataHeader);
                                            }
                                        
                                        }
                        

                                        $sDataToWrite =  implode("\t",$row)."\r\n"; 
                                        if($bExistingFile){
                                            foreach($aExistingFile as $index => $aExistingFileRow)
                                            {
                                                if($aExistingFileRow === $sDataToWrite){
                                                    $recordsDone++; 
                                                    continue 2;
                                                }
                                                else
                                                {
                                                    if(isset(explode("\t",$aExistingFileRow)[22])===isset(explode("\t",$sDataToWrite)[22])){
                                                        if(explode("\t",$aExistingFileRow)[22]===explode("\t",$sDataToWrite)[22])
                                                        {
                                                            $aExistingFile[$index] =  $sDataToWrite;
                                                            fclose($fp);
                                                            $fp = fopen($sDataPrepOutputFile, "w");
                                                            fwrite($fp, $aMailShippingOutputDataHeader);
                                                            foreach($aExistingFile as $rewrite)
                                                            {
                                                                $bFileWriting1 = fwrite($fp, $rewrite);
                                                            }
                                                            $recordsDone++;
                                                            continue 2;
                                                            
                                                        }
                                                    }
                                                    else
                                                        continue;
                                                }
                                            }

                                        }
                                    
                                
                                        $bFileWriting1 =fwrite($fp,  $sDataToWrite);
                                        $aFilesWritingStatus[] = $bFileWriting1;
                                        $recordsDone++;
                                    } 
                                            unset($bExistingFile);
                                            if(!isset($bFileWriting1))
                                            {
                                                
                                                echo "$sDateStamp [$sUser]: File already exists\n";
                                                fclose($fp);
                                            }
                                            else if($bFileWriting1)
                                            {
                                                echo "$sDateStamp [$sUser]: File succesfully written as: $sDataPrepOutputFile.\n";
                                                fclose($fp);
                                            }
                                            else
                                            {
                                                echo "$sDateStamp [$sUser]: Writing file failed\n";
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
                }
            }


            if(isset($aMailMergeShippingOutputData) && count($aMailMergeShippingOutputData)!=0)
            {
                echo "$sDateStamp [$sUser]: \n\n MAILMERGE START \n\n";

                // echo "aMailMergeShippingOutputData";
                // print_r($aMailMergeShippingOutputData);
        

                foreach($aMailMergeShippingOutputData as $keyShipment => $aShippingRecord) 
                {                            
                    foreach($aShippingRecord as $keyProduct => $aProductRecord)
                    {
                        foreach($aProductRecord as $keyCardStock => $aCardStockBatchRecord)
                        {
                            $iGroup = 0;

                            foreach($aCardStockBatchRecord as $BatchID => $aCardStockRecord)
                            {
                                    if(!empty(explode("_",$BatchID)[1]))
                                         $BatchID = explode("_",$BatchID)[1]."_".++$iGroup;


                                    $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                                    $sShippingName = $keyShipment;
                                    $sProductName =  $sProductProp['Product'];
                                  
                                    $mailOutputDir = $sMailMergeOutputDir;
        
                                    echo "$sDateStamp [$sUser]: Mailmerge: Records per Shipment $sShippingName and per product $sProductName: ".count($aCardStockRecord)."\n";
                                    $bFileWriting1; 
                                    $aExistingFile = null;
                                    $bExistingFile = false;
                                    $sDataToWrite = null;
                                
                                    $numSplits = 0;
                                    $recordsDone = 0;
                                    $fp = null;
                                    $neededSplits = 0;

                                    if(count($aCardStockRecord)>$maxRec)
                                    {
                                        $neededSplits = ceil(count($aCardStockRecord) / $maxRec);
                                    }
                            
                                    foreach ($aCardStockRecord as $row) 
                                    { 
                                        
                                        if($recordsDone == $maxRec)
                                            $recordsDone = 0;
                                        if($recordsDone == 0)
                                        {
                                            if($numSplits > 0)
                                                fclose($fp);
                                            ++$numSplits;
                                            $sDataPrepOutputFile =  $mailOutputDir."MAILMERGE_".$sProductName."_".$sShippingName."_".$BatchID."_";
                                            if($neededSplits > 0)
                                                $sDataPrepOutputFile = $sDataPrepOutputFile.$numSplits."_of_".$neededSplits."_";
                                            $sDataPrepOutputFile = $sDataPrepOutputFile.$sFileName;
                                        
                                            //CHECK IF FILE EXISTS:
                                            $bExistingFile=file_exists($sDataPrepOutputFile);
                                            //echo("MAIN_FILE_EXISTS".$bExistingFile);
                                            if($bExistingFile) 
                                            {
                                                $fp = fopen($sDataPrepOutputFile, "a+");
                                                fgets($fp);
                                                while(!feof($fp))
                                                {
                                                    $aExistingFile[] = fgets($fp);
                                                }
                                                //echo"aExistingFile";
                                                //print_r($aExistingFile);
                                            }
                                            else
                                            {
                                                $fp = fopen($sDataPrepOutputFile, "w");
                                                fwrite($fp, $aMailMergeShippingOutputDataHeader);
                                            }
                                        
                                        }
                        

                                        $sDataToWrite =  implode("\t",$row)."\r\n"; 
                                        if($bExistingFile){
                                            foreach($aExistingFile as $index => $aExistingFileRow)
                                            {
                                                if($aExistingFileRow === $sDataToWrite){
                                                    $recordsDone++; 
                                                    continue 2;
                                                }
                                                else
                                                {
                                                    if(isset(explode("\t",$aExistingFileRow)[22])===isset(explode("\t",$sDataToWrite)[22])){
                                                        if(explode("\t",$aExistingFileRow)[22]===explode("\t",$sDataToWrite)[22])
                                                        {
                                                            $aExistingFile[$index] =  $sDataToWrite;
                                                            fclose($fp);
                                                            $fp = fopen($sDataPrepOutputFile, "w");
                                                            fwrite($fp, $aMailShippingOutputDataHeader);
                                                            foreach($aExistingFile as $rewrite)
                                                            {
                                                                $bFileWriting1 = fwrite($fp, $rewrite);
                                                            }
                                                            $recordsDone++;
                                                            continue 2;
                                                            
                                                        }
                                                    }
                                                    else
                                                        continue;
                                                }
                                            }

                                        }
                                    
                                
                                        $bFileWriting1 =fwrite($fp,  $sDataToWrite);
                                        $aFilesWritingStatus[] = $bFileWriting1;
                                        $recordsDone++;
                                    } 
                                            unset($bExistingFile);
                                            if(!isset($bFileWriting1))
                                            {
                                                
                                                echo "$sDateStamp [$sUser]: File already exists\n";
                                                fclose($fp);
                                            }
                                            else if($bFileWriting1)
                                            {
                                                echo "$sDateStamp [$sUser]: File succesfully written as: $sDataPrepOutputFile.\n";
                                                fclose($fp);
                                            }
                                            else
                                            {
                                                echo "$sDateStamp [$sUser]: Writing file failed\n";
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
                }
            }


            if(isset($aBulkMultiShippingOutputData) && count($aBulkMultiShippingOutputData)!=0)
            {
                // print_r($aBulkMultiShippingOutputData);

                echo "$sDateStamp [$sUser]: \n\n BULK PKG MAILING START \n\n";

                
                foreach($aBulkMultiShippingOutputData as $keyBulkShippingMethod => $aBulkShippingData)     
                {
                    $iGroup = 0;
                    foreach($aBulkShippingData as $keyGroupID => $aGroupID)  
                    {   
                    
                            $iGroup++;
                            // echo("GROUP $iGroup\n");
                            // print_r($aGroupID);
                            
                            $iNoOfCardsPerGroupID = count($aGroupID);
                            $Group_ID =  explode('_',$keyGroupID)[1]."_".$iGroup;
                            $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                            $sBulkShippingName = $keyBulkShippingMethod;
        
                            if(preg_match('/FEDx/', $sBulkShippingName ))
                            {
                                $sBulkOutputDir = $sBulkFedexOutputDir;
                            }
                            else
                            {
                                $sBulkOutputDir = $sBulkOutputDir;
                            }
                            $sBulkMultiOutputFile = $sBulkOutputDir."BULK_PKGS_".$sBulkShippingName."_GROUP_$Group_ID"."_CARDS_$iNoOfCardsPerGroupID"."_".(preg_replace("/(\.).*/","",$sFileName)).".csv";

                            $fp = fopen($sBulkMultiOutputFile, "w");
                            
                            fwrite($fp, $aMailShippingOutputDataHeader);    
                            
                                
                                $sDataToWrite =  implode("\t",$aGroupID[0])."\r\n"; 
                            
                                $bFileWriting1 = fwrite($fp, $sDataToWrite);
                                $aFilesWritingStatus[] = $bFileWriting1;
                        
                            
                            if($bFileWriting1)
                            {
                                echo "$sDateStamp [$sUser]: Bulk Multi Package File for batch #: $Group_ID succesfully written as: $sBulkMultiOutputFile\n";
                                fclose($fp);
                            
                            }
                            else 
                            {
                                echo "$sDateStamp [$sUser]: Writing Bulk Multi Package file for batch $Group_ID failed\n";
                                fclose($fp);
                            }
                            
                            
                    }
                }   
            }
    

            
            return $aMailShippingOutputData;
         


      
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

function progressBar($done, $total) {
    
 
    $perc = floor(($done / $total) * 100);
    $left = 100 - $perc;
    $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total ", "", "");
    //$write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
    fwrite(STDERR, $write); 
}

function convertCountry(string $currentType,string $value,string $newType)
{
    $countries = json_decode('[{"id":4,"alpha2":"af","alpha3":"afg","name":"Afghanistan"},
    {"id":248,"alpha2":"ax","alpha3":"ala","name":"Åland Islands"},
    {"id":8,"alpha2":"al","alpha3":"alb","name":"Albania"},
    {"id":12,"alpha2":"dz","alpha3":"dza","name":"Algeria"},
    {"id":16,"alpha2":"as","alpha3":"asm","name":"American Samoa"},
    {"id":20,"alpha2":"ad","alpha3":"and","name":"Andorra"},
    {"id":24,"alpha2":"ao","alpha3":"ago","name":"Angola"},
    {"id":660,"alpha2":"ai","alpha3":"aia","name":"Anguilla"},
    {"id":10,"alpha2":"aq","alpha3":"ata","name":"Antarctica"},
    {"id":28,"alpha2":"ag","alpha3":"atg","name":"Antigua and Barbuda"},
    {"id":32,"alpha2":"ar","alpha3":"arg","name":"Argentina"},
    {"id":51,"alpha2":"am","alpha3":"arm","name":"Armenia"},
    {"id":533,"alpha2":"aw","alpha3":"abw","name":"Aruba"},
    {"id":36,"alpha2":"au","alpha3":"aus","name":"Australia"},
    {"id":40,"alpha2":"at","alpha3":"aut","name":"Austria"},
    {"id":31,"alpha2":"az","alpha3":"aze","name":"Azerbaijan"},
    {"id":44,"alpha2":"bs","alpha3":"bhs","name":"Bahamas"},
    {"id":48,"alpha2":"bh","alpha3":"bhr","name":"Bahrain"},
    {"id":50,"alpha2":"bd","alpha3":"bgd","name":"Bangladesh"},
    {"id":52,"alpha2":"bb","alpha3":"brb","name":"Barbados"},
    {"id":112,"alpha2":"by","alpha3":"blr","name":"Belarus"},
    {"id":56,"alpha2":"be","alpha3":"bel","name":"Belgium"},
    {"id":84,"alpha2":"bz","alpha3":"blz","name":"Belize"},
    {"id":204,"alpha2":"bj","alpha3":"ben","name":"Benin"},
    {"id":60,"alpha2":"bm","alpha3":"bmu","name":"Bermuda"},
    {"id":64,"alpha2":"bt","alpha3":"btn","name":"Bhutan"},
    {"id":68,"alpha2":"bo","alpha3":"bol","name":"Bolivia (Plurinational State of)"},
    {"id":535,"alpha2":"bq","alpha3":"bes","name":"Bonaire, Sint Eustatius and Saba"},
    {"id":70,"alpha2":"ba","alpha3":"bih","name":"Bosnia and Herzegovina"},
    {"id":72,"alpha2":"bw","alpha3":"bwa","name":"Botswana"},
    {"id":74,"alpha2":"bv","alpha3":"bvt","name":"Bouvet Island"},
    {"id":76,"alpha2":"br","alpha3":"bra","name":"Brazil"},
    {"id":86,"alpha2":"io","alpha3":"iot","name":"British Indian Ocean Territory"},
    {"id":96,"alpha2":"bn","alpha3":"brn","name":"Brunei Darussalam"},
    {"id":100,"alpha2":"bg","alpha3":"bgr","name":"Bulgaria"},
    {"id":854,"alpha2":"bf","alpha3":"bfa","name":"Burkina Faso"},
    {"id":108,"alpha2":"bi","alpha3":"bdi","name":"Burundi"},
    {"id":132,"alpha2":"cv","alpha3":"cpv","name":"Cabo Verde"},
    {"id":116,"alpha2":"kh","alpha3":"khm","name":"Cambodia"},
    {"id":120,"alpha2":"cm","alpha3":"cmr","name":"Cameroon"},
    {"id":124,"alpha2":"ca","alpha3":"can","name":"Canada"},
    {"id":136,"alpha2":"ky","alpha3":"cym","name":"Cayman Islands"},
    {"id":140,"alpha2":"cf","alpha3":"caf","name":"Central African Republic"},
    {"id":148,"alpha2":"td","alpha3":"tcd","name":"Chad"},
    {"id":152,"alpha2":"cl","alpha3":"chl","name":"Chile"},
    {"id":156,"alpha2":"cn","alpha3":"chn","name":"China"},
    {"id":162,"alpha2":"cx","alpha3":"cxr","name":"Christmas Island"},
    {"id":166,"alpha2":"cc","alpha3":"cck","name":"Cocos (Keeling) Islands"},
    {"id":170,"alpha2":"co","alpha3":"col","name":"Colombia"},
    {"id":174,"alpha2":"km","alpha3":"com","name":"Comoros"},
    {"id":178,"alpha2":"cg","alpha3":"cog","name":"Congo"},
    {"id":180,"alpha2":"cd","alpha3":"cod","name":"Congo, Democratic Republic of the"},
    {"id":184,"alpha2":"ck","alpha3":"cok","name":"Cook Islands"},
    {"id":188,"alpha2":"cr","alpha3":"cri","name":"Costa Rica"},
    {"id":384,"alpha2":"ci","alpha3":"civ","name":"Côte d\'Ivoire"},
    {"id":191,"alpha2":"hr","alpha3":"hrv","name":"Croatia"},
    {"id":192,"alpha2":"cu","alpha3":"cub","name":"Cuba"},
    {"id":531,"alpha2":"cw","alpha3":"cuw","name":"Curaçao"},
    {"id":196,"alpha2":"cy","alpha3":"cyp","name":"Cyprus"},
    {"id":203,"alpha2":"cz","alpha3":"cze","name":"Czechia"},
    {"id":208,"alpha2":"dk","alpha3":"dnk","name":"Denmark"},
    {"id":262,"alpha2":"dj","alpha3":"dji","name":"Djibouti"},
    {"id":212,"alpha2":"dm","alpha3":"dma","name":"Dominica"},
    {"id":214,"alpha2":"do","alpha3":"dom","name":"Dominican Republic"},
    {"id":218,"alpha2":"ec","alpha3":"ecu","name":"Ecuador"},
    {"id":818,"alpha2":"eg","alpha3":"egy","name":"Egypt"},
    {"id":222,"alpha2":"sv","alpha3":"slv","name":"El Salvador"},
    {"id":226,"alpha2":"gq","alpha3":"gnq","name":"Equatorial Guinea"},
    {"id":232,"alpha2":"er","alpha3":"eri","name":"Eritrea"},
    {"id":233,"alpha2":"ee","alpha3":"est","name":"Estonia"},
    {"id":748,"alpha2":"sz","alpha3":"swz","name":"Eswatini"},
    {"id":231,"alpha2":"et","alpha3":"eth","name":"Ethiopia"},
    {"id":238,"alpha2":"fk","alpha3":"flk","name":"Falkland Islands (Malvinas)"},
    {"id":234,"alpha2":"fo","alpha3":"fro","name":"Faroe Islands"},
    {"id":242,"alpha2":"fj","alpha3":"fji","name":"Fiji"},
    {"id":246,"alpha2":"fi","alpha3":"fin","name":"Finland"},
    {"id":250,"alpha2":"fr","alpha3":"fra","name":"France"},
    {"id":254,"alpha2":"gf","alpha3":"guf","name":"French Guiana"},
    {"id":258,"alpha2":"pf","alpha3":"pyf","name":"French Polynesia"},
    {"id":260,"alpha2":"tf","alpha3":"atf","name":"French Southern Territories"},
    {"id":266,"alpha2":"ga","alpha3":"gab","name":"Gabon"},
    {"id":270,"alpha2":"gm","alpha3":"gmb","name":"Gambia"},
    {"id":268,"alpha2":"ge","alpha3":"geo","name":"Georgia"},
    {"id":276,"alpha2":"de","alpha3":"deu","name":"Germany"},
    {"id":288,"alpha2":"gh","alpha3":"gha","name":"Ghana"},
    {"id":292,"alpha2":"gi","alpha3":"gib","name":"Gibraltar"},
    {"id":300,"alpha2":"gr","alpha3":"grc","name":"Greece"},
    {"id":304,"alpha2":"gl","alpha3":"grl","name":"Greenland"},
    {"id":308,"alpha2":"gd","alpha3":"grd","name":"Grenada"},
    {"id":312,"alpha2":"gp","alpha3":"glp","name":"Guadeloupe"},
    {"id":316,"alpha2":"gu","alpha3":"gum","name":"Guam"},
    {"id":320,"alpha2":"gt","alpha3":"gtm","name":"Guatemala"},
    {"id":831,"alpha2":"gg","alpha3":"ggy","name":"Guernsey"},
    {"id":324,"alpha2":"gn","alpha3":"gin","name":"Guinea"},
    {"id":624,"alpha2":"gw","alpha3":"gnb","name":"Guinea-Bissau"},
    {"id":328,"alpha2":"gy","alpha3":"guy","name":"Guyana"},
    {"id":332,"alpha2":"ht","alpha3":"hti","name":"Haiti"},
    {"id":334,"alpha2":"hm","alpha3":"hmd","name":"Heard Island and McDonald Islands"},
    {"id":336,"alpha2":"va","alpha3":"vat","name":"Holy See"},
    {"id":340,"alpha2":"hn","alpha3":"hnd","name":"Honduras"},
    {"id":344,"alpha2":"hk","alpha3":"hkg","name":"Hong Kong"},
    {"id":348,"alpha2":"hu","alpha3":"hun","name":"Hungary"},
    {"id":352,"alpha2":"is","alpha3":"isl","name":"Iceland"},
    {"id":356,"alpha2":"in","alpha3":"ind","name":"India"},
    {"id":360,"alpha2":"id","alpha3":"idn","name":"Indonesia"},
    {"id":364,"alpha2":"ir","alpha3":"irn","name":"Iran (Islamic Republic of)"},
    {"id":368,"alpha2":"iq","alpha3":"irq","name":"Iraq"},
    {"id":372,"alpha2":"ie","alpha3":"irl","name":"Ireland"},
    {"id":833,"alpha2":"im","alpha3":"imn","name":"Isle of Man"},
    {"id":376,"alpha2":"il","alpha3":"isr","name":"Israel"},
    {"id":380,"alpha2":"it","alpha3":"ita","name":"Italy"},
    {"id":388,"alpha2":"jm","alpha3":"jam","name":"Jamaica"},
    {"id":392,"alpha2":"jp","alpha3":"jpn","name":"Japan"},
    {"id":832,"alpha2":"je","alpha3":"jey","name":"Jersey"},
    {"id":400,"alpha2":"jo","alpha3":"jor","name":"Jordan"},
    {"id":398,"alpha2":"kz","alpha3":"kaz","name":"Kazakhstan"},
    {"id":404,"alpha2":"ke","alpha3":"ken","name":"Kenya"},
    {"id":296,"alpha2":"ki","alpha3":"kir","name":"Kiribati"},
    {"id":408,"alpha2":"kp","alpha3":"prk","name":"Korea (Democratic People\'s Republic of)"},
    {"id":410,"alpha2":"kr","alpha3":"kor","name":"Korea, Republic of"},
    {"id":414,"alpha2":"kw","alpha3":"kwt","name":"Kuwait"},
    {"id":417,"alpha2":"kg","alpha3":"kgz","name":"Kyrgyzstan"},
    {"id":418,"alpha2":"la","alpha3":"lao","name":"Lao People\'s Democratic Republic"},
    {"id":428,"alpha2":"lv","alpha3":"lva","name":"Latvia"},
    {"id":422,"alpha2":"lb","alpha3":"lbn","name":"Lebanon"},
    {"id":426,"alpha2":"ls","alpha3":"lso","name":"Lesotho"},
    {"id":430,"alpha2":"lr","alpha3":"lbr","name":"Liberia"},
    {"id":434,"alpha2":"ly","alpha3":"lby","name":"Libya"},
    {"id":438,"alpha2":"li","alpha3":"lie","name":"Liechtenstein"},
    {"id":440,"alpha2":"lt","alpha3":"ltu","name":"Lithuania"},
    {"id":442,"alpha2":"lu","alpha3":"lux","name":"Luxembourg"},
    {"id":446,"alpha2":"mo","alpha3":"mac","name":"Macao"},
    {"id":450,"alpha2":"mg","alpha3":"mdg","name":"Madagascar"},
    {"id":454,"alpha2":"mw","alpha3":"mwi","name":"Malawi"},
    {"id":458,"alpha2":"my","alpha3":"mys","name":"Malaysia"},
    {"id":462,"alpha2":"mv","alpha3":"mdv","name":"Maldives"},
    {"id":466,"alpha2":"ml","alpha3":"mli","name":"Mali"},
    {"id":470,"alpha2":"mt","alpha3":"mlt","name":"Malta"},
    {"id":584,"alpha2":"mh","alpha3":"mhl","name":"Marshall Islands"},
    {"id":474,"alpha2":"mq","alpha3":"mtq","name":"Martinique"},
    {"id":478,"alpha2":"mr","alpha3":"mrt","name":"Mauritania"},
    {"id":480,"alpha2":"mu","alpha3":"mus","name":"Mauritius"},
    {"id":175,"alpha2":"yt","alpha3":"myt","name":"Mayotte"},
    {"id":484,"alpha2":"mx","alpha3":"mex","name":"Mexico"},
    {"id":583,"alpha2":"fm","alpha3":"fsm","name":"Micronesia (Federated States of)"},
    {"id":498,"alpha2":"md","alpha3":"mda","name":"Moldova, Republic of"},
    {"id":492,"alpha2":"mc","alpha3":"mco","name":"Monaco"},
    {"id":496,"alpha2":"mn","alpha3":"mng","name":"Mongolia"},
    {"id":499,"alpha2":"me","alpha3":"mne","name":"Montenegro"},
    {"id":500,"alpha2":"ms","alpha3":"msr","name":"Montserrat"},
    {"id":504,"alpha2":"ma","alpha3":"mar","name":"Morocco"},
    {"id":508,"alpha2":"mz","alpha3":"moz","name":"Mozambique"},
    {"id":104,"alpha2":"mm","alpha3":"mmr","name":"Myanmar"},
    {"id":516,"alpha2":"na","alpha3":"nam","name":"Namibia"},
    {"id":520,"alpha2":"nr","alpha3":"nru","name":"Nauru"},
    {"id":524,"alpha2":"np","alpha3":"npl","name":"Nepal"},
    {"id":528,"alpha2":"nl","alpha3":"nld","name":"Netherlands"},
    {"id":540,"alpha2":"nc","alpha3":"ncl","name":"New Caledonia"},
    {"id":554,"alpha2":"nz","alpha3":"nzl","name":"New Zealand"},
    {"id":558,"alpha2":"ni","alpha3":"nic","name":"Nicaragua"},
    {"id":562,"alpha2":"ne","alpha3":"ner","name":"Niger"},
    {"id":566,"alpha2":"ng","alpha3":"nga","name":"Nigeria"},
    {"id":570,"alpha2":"nu","alpha3":"niu","name":"Niue"},
    {"id":574,"alpha2":"nf","alpha3":"nfk","name":"Norfolk Island"},
    {"id":807,"alpha2":"mk","alpha3":"mkd","name":"North Macedonia"},
    {"id":580,"alpha2":"mp","alpha3":"mnp","name":"Northern Mariana Islands"},
    {"id":578,"alpha2":"no","alpha3":"nor","name":"Norway"},
    {"id":512,"alpha2":"om","alpha3":"omn","name":"Oman"},
    {"id":586,"alpha2":"pk","alpha3":"pak","name":"Pakistan"},
    {"id":585,"alpha2":"pw","alpha3":"plw","name":"Palau"},
    {"id":275,"alpha2":"ps","alpha3":"pse","name":"Palestine, State of"},
    {"id":591,"alpha2":"pa","alpha3":"pan","name":"Panama"},
    {"id":598,"alpha2":"pg","alpha3":"png","name":"Papua New Guinea"},
    {"id":600,"alpha2":"py","alpha3":"pry","name":"Paraguay"},
    {"id":604,"alpha2":"pe","alpha3":"per","name":"Peru"},
    {"id":608,"alpha2":"ph","alpha3":"phl","name":"Philippines"},
    {"id":612,"alpha2":"pn","alpha3":"pcn","name":"Pitcairn"},
    {"id":616,"alpha2":"pl","alpha3":"pol","name":"Poland"},
    {"id":620,"alpha2":"pt","alpha3":"prt","name":"Portugal"},
    {"id":630,"alpha2":"pr","alpha3":"pri","name":"Puerto Rico"},
    {"id":634,"alpha2":"qa","alpha3":"qat","name":"Qatar"},
    {"id":638,"alpha2":"re","alpha3":"reu","name":"Réunion"},
    {"id":642,"alpha2":"ro","alpha3":"rou","name":"Romania"},
    {"id":643,"alpha2":"ru","alpha3":"rus","name":"Russian Federation"},
    {"id":646,"alpha2":"rw","alpha3":"rwa","name":"Rwanda"},
    {"id":652,"alpha2":"bl","alpha3":"blm","name":"Saint Barthélemy"},
    {"id":654,"alpha2":"sh","alpha3":"shn","name":"Saint Helena, Ascension and Tristan da Cunha"},
    {"id":659,"alpha2":"kn","alpha3":"kna","name":"Saint Kitts and Nevis"},
    {"id":662,"alpha2":"lc","alpha3":"lca","name":"Saint Lucia"},
    {"id":663,"alpha2":"mf","alpha3":"maf","name":"Saint Martin (French part)"},
    {"id":666,"alpha2":"pm","alpha3":"spm","name":"Saint Pierre and Miquelon"},
    {"id":670,"alpha2":"vc","alpha3":"vct","name":"Saint Vincent and the Grenadines"},
    {"id":882,"alpha2":"ws","alpha3":"wsm","name":"Samoa"},
    {"id":674,"alpha2":"sm","alpha3":"smr","name":"San Marino"},
    {"id":678,"alpha2":"st","alpha3":"stp","name":"Sao Tome and Principe"},
    {"id":682,"alpha2":"sa","alpha3":"sau","name":"Saudi Arabia"},
    {"id":686,"alpha2":"sn","alpha3":"sen","name":"Senegal"},
    {"id":688,"alpha2":"rs","alpha3":"srb","name":"Serbia"},
    {"id":690,"alpha2":"sc","alpha3":"syc","name":"Seychelles"},
    {"id":694,"alpha2":"sl","alpha3":"sle","name":"Sierra Leone"},
    {"id":702,"alpha2":"sg","alpha3":"sgp","name":"Singapore"},
    {"id":534,"alpha2":"sx","alpha3":"sxm","name":"Sint Maarten (Dutch part)"},
    {"id":703,"alpha2":"sk","alpha3":"svk","name":"Slovakia"},
    {"id":705,"alpha2":"si","alpha3":"svn","name":"Slovenia"},
    {"id":90,"alpha2":"sb","alpha3":"slb","name":"Solomon Islands"},
    {"id":706,"alpha2":"so","alpha3":"som","name":"Somalia"},
    {"id":710,"alpha2":"za","alpha3":"zaf","name":"South Africa"},
    {"id":239,"alpha2":"gs","alpha3":"sgs","name":"South Georgia and the South Sandwich Islands"},
    {"id":728,"alpha2":"ss","alpha3":"ssd","name":"South Sudan"},
    {"id":724,"alpha2":"es","alpha3":"esp","name":"Spain"},
    {"id":144,"alpha2":"lk","alpha3":"lka","name":"Sri Lanka"},
    {"id":729,"alpha2":"sd","alpha3":"sdn","name":"Sudan"},
    {"id":740,"alpha2":"sr","alpha3":"sur","name":"Suriname"},
    {"id":744,"alpha2":"sj","alpha3":"sjm","name":"Svalbard and Jan Mayen"},
    {"id":752,"alpha2":"se","alpha3":"swe","name":"Sweden"},
    {"id":756,"alpha2":"ch","alpha3":"che","name":"Switzerland"},
    {"id":760,"alpha2":"sy","alpha3":"syr","name":"Syrian Arab Republic"},
    {"id":158,"alpha2":"tw","alpha3":"twn","name":"Taiwan, Province of China"},
    {"id":762,"alpha2":"tj","alpha3":"tjk","name":"Tajikistan"},
    {"id":834,"alpha2":"tz","alpha3":"tza","name":"Tanzania, United Republic of"},
    {"id":764,"alpha2":"th","alpha3":"tha","name":"Thailand"},
    {"id":626,"alpha2":"tl","alpha3":"tls","name":"Timor-Leste"},
    {"id":768,"alpha2":"tg","alpha3":"tgo","name":"Togo"},
    {"id":772,"alpha2":"tk","alpha3":"tkl","name":"Tokelau"},
    {"id":776,"alpha2":"to","alpha3":"ton","name":"Tonga"},
    {"id":780,"alpha2":"tt","alpha3":"tto","name":"Trinidad and Tobago"},
    {"id":788,"alpha2":"tn","alpha3":"tun","name":"Tunisia"},
    {"id":792,"alpha2":"tr","alpha3":"tur","name":"Turkey"},
    {"id":795,"alpha2":"tm","alpha3":"tkm","name":"Turkmenistan"},
    {"id":796,"alpha2":"tc","alpha3":"tca","name":"Turks and Caicos Islands"},
    {"id":798,"alpha2":"tv","alpha3":"tuv","name":"Tuvalu"},
    {"id":800,"alpha2":"ug","alpha3":"uga","name":"Uganda"},
    {"id":804,"alpha2":"ua","alpha3":"ukr","name":"Ukraine"},
    {"id":784,"alpha2":"ae","alpha3":"are","name":"United Arab Emirates"},
    {"id":826,"alpha2":"gb","alpha3":"gbr","name":"United Kingdom of Great Britain and Northern Ireland"},
    {"id":840,"alpha2":"us","alpha3":"usa","name":"United States of America"},
    {"id":581,"alpha2":"um","alpha3":"umi","name":"United States Minor Outlying Islands"},
    {"id":858,"alpha2":"uy","alpha3":"ury","name":"Uruguay"},
    {"id":860,"alpha2":"uz","alpha3":"uzb","name":"Uzbekistan"},
    {"id":548,"alpha2":"vu","alpha3":"vut","name":"Vanuatu"},
    {"id":862,"alpha2":"ve","alpha3":"ven","name":"Venezuela (Bolivarian Republic of)"},
    {"id":704,"alpha2":"vn","alpha3":"vnm","name":"Viet Nam"},
    {"id":92,"alpha2":"vg","alpha3":"vgb","name":"Virgin Islands (British)"},
    {"id":850,"alpha2":"vi","alpha3":"vir","name":"Virgin Islands (U.S.)"},
    {"id":876,"alpha2":"wf","alpha3":"wlf","name":"Wallis and Futuna"},
    {"id":732,"alpha2":"eh","alpha3":"esh","name":"Western Sahara"},
    {"id":887,"alpha2":"ye","alpha3":"yem","name":"Yemen"},
    {"id":894,"alpha2":"zm","alpha3":"zmb","name":"Zambia"},
    {"id":716,"alpha2":"zw","alpha3":"zwe","name":"Zimbabwe"}]',true);
    
    if(array_search(strtolower($value), array_column($countries, $currentType))==false)
    {
        return false;
    }
    else
    {
        $out = strtoupper($countries[array_search(strtolower($value), array_column($countries, $currentType))][$newType]);
    }
    return $out;
}

function findCustomer($aInputFile, $aBINs)
{   
    //echo"INPUT FILE\n";
    //print_r($aInputFile);
    global $bIsExtendedBINused;
    $bCustomerFound = false;
    $bIsExtendedBINused = false;
    $i = 0;
    $maxRec = count($aInputFile)-1;
    while(!$bCustomerFound)
    {  
        $sBIN = substr(preg_replace("/\s+/","",($aInputFile[$i][1])),0,6);
        $sBINExtended = substr(preg_replace("/\s+/","",($aInputFile[$i][1])),0,8);
        //echo"sBINinFile: $sBIN\n";
        if(isset($aBINs[$sBIN]))
        {
            $bCustomerFound = true;
            return $sBIN;
        }
        else if(isset($aBINs[$sBINExtended]))
        {
            $bCustomerFound = true;
            $bIsExtendedBINused = true;
            return $sBINExtended;
        }
        else if(!(isset($aBINs[$sBIN])) && $maxRec > $i)
        {
            $i++;
        }
        else
        {
            return false;
        }
        
    }

}


function getDetailOverview($aInputData){

    global $sCustomerName;
    global $aBINs;
    global $sBIN;
    global $iNoErrorRecs;
    $iTotalNumberOfRecords=0;
    //$aInputData = array_slice($aInputData,0,1);
    //print_r($aInputData);
    echo "\n\t Detail Bulk Summary of records in file Shipment Method and per Product for customer $sCustomerName: \n";
    printf('            %-15s|  %-11s|  %-21s|  %-25s|  %-12s', 'ProductID-Name', 'CardStockID','ShipmentMethodID-Name','BULK Group/Location ID','Total Records');
    echo"\n";

    foreach($aInputData as $keyShipmentMethod => $aShipRecords)
    { 
        foreach($aShipRecords as $keyPerProduct => $aProdRecords)
        {
            foreach($aProdRecords as $keyCardStock => $aCardStocks)
            {
                //$sShipmentAlias= $aBINs[$sBIN][$keyPerProduct][$keyCardStock]['ShippingMethodsBulk'][$keyShipmentMethod];
                $iGroup = 0;

                foreach($aCardStocks as $keyBulkShipment => $aRecords)
                {
                     if(!empty(explode("_",$keyBulkShipment)[1]))
                        $keyBulkShipment = explode("_",$keyBulkShipment)[1]."_".++$iGroup."-".explode("_",$keyBulkShipment)[3];
                     $sBIN = substr($aRecords[0][7],2,6);
                     $sBINExtended =  substr($aRecords[0][7],2,8);
 
                     if(isset($aBINs[$sBINExtended]))
                     {
                         $sBIN = $sBINExtended;
                         $bIsExtendedBINused = true;
                     }
                     $sProductAlias = $aBINs[$sBIN][$keyPerProduct][$keyCardStock]['Product'];

 
                     //$sBulkShipmentAlias= $aBINs[$sBIN][$keyPerProduct][$keyCardStock]['ShippingMethodsBulk'][$keyBulkShipment];
                     $iTotalNoPerService = 0;

                     //foreach($aGroupIDs as $keyGroupID => $aRecords)
                     {
                        $iTotalNoPerService = count($aRecords);
                        $iTotalNumberOfRecords+=count($aRecords);
                        $keyGroupID = "";
                        printf('            %-15s|  %-11s|  %-21s|  %-25s|  %12d ',($keyPerProduct."-".$sProductAlias), $keyCardStock, ($keyShipmentMethod),$keyBulkShipment,  $iTotalNoPerService);
                        echo"\n";
                     }
                }
            }
        }
    }
    
        printf('           %-87s','------------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-49s    %-20s    %20d', 'Total Good Processed Records','',$iTotalNumberOfRecords);
        echo"\n\n";

        printf('           %-87s','------------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-49s    %-20s    %20d', 'Total Bad/Errored Records in file','',$iNoErrorRecs);
        echo"\n\n";

        printf('           %-87s','------------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-49s    %-20s    %20d', 'Total Records in file','',$iTotalNumberOfRecords+$iNoErrorRecs);
        echo"\n\n";
        
    //return  $aCollectShipment;

}

function getProductsList($sProductConfigFile){
    $productsConfiguration = file($sProductConfigFile, FILE_SKIP_EMPTY_LINES);
    foreach($productsConfiguration as $aProductDetails)
        {
            if(preg_match("/^#/", $aProductDetails, $comments))
                {
                    //COMMENT IN COFIGURATION FILE, GO TO NEXT
                    continue;
                }
                else
                {
                    $aProducts = str_getcsv($aProductDetails);
                
                    if(empty($aProducts[3]))
                    {
                        $aProducts[3] = "NA";
                    }
                    	// echo("PRODUCTS\n");
                    	// print_r($aProducts);
                    $aBINs[$aProducts[1]]['Customer']=$aProducts[0];
                    $aBINs[$aProducts[1]][$aProducts[2]][$aProducts[3]]=array(
                        "Profile"=>$aProducts[5],
                        "Product"=>$aProducts[4],
                        "ServiceType"=>$aProducts[27],
                        "PackageType"=>$aProducts[28],
                        "WeightOz"=>$aProducts[29],
                        "ShipDate"=>$aProducts[30],
                        "FromFullName"=>$aProducts[31],
                        "FromAddress1"=>$aProducts[32],
                        "FromAddress2"=>$aProducts[33],
                        "FromCity"=>$aProducts[34],
                        "FromState"=>$aProducts[35],
                        "FromCountry"=>$aProducts[36],
                        "FromZIPCode"=>$aProducts[37], 
                        "BulkCompanyName" => $aProducts[38], 
                        "BulkFullName" => $aProducts[39], 
                        "BulkAddress1" => $aProducts[40], 
                        "BulkAddress2" => $aProducts[41], 
                        "BulkCity" => $aProducts[42], 
                        "BulkState" => $aProducts[43], 
                        "BulkCountry" => $aProducts[44], 
                        "BulkZIPCode" => $aProducts[45], 
                        "FEDxAccount" => $aProducts[46],
                        "FEDEXPhoneNumber" => $aProducts[47],
                        "ShippingMethods" => array ('01'=> $aProducts[7],
                                                    '02'=> $aProducts[8],
                                                    '03'=> $aProducts[9],
                                                    '04'=> $aProducts[10],
                                                    '05'=> $aProducts[11],
                                                    '06'=> $aProducts[12],
                                                    '07'=> $aProducts[13],
                                                    '08'=> $aProducts[14],
                                                    '09'=> $aProducts[15],
                                                    '10'=> $aProducts[16],
                                                    '11'=> $aProducts[17],
                                                    '12'=> $aProducts[18],
                                                    '13'=> $aProducts[19],
                                                    '14'=> $aProducts[20],
                                                    '15'=> $aProducts[21],
                                                    '16'=> $aProducts[22],
                                                    '17'=> $aProducts[23],
                                                    '18'=> $aProducts[24],
                                                    '19'=> $aProducts[25],
                                                    '20'=> $aProducts[26],       
                                                ),
                        "ShippingMethodsBulk" =>  array (   '01'=> $aProducts[48],
                                                            '02'=> $aProducts[49],
                                                            '03'=> $aProducts[50],
                                                            '04'=> $aProducts[51],
                                                            '05'=> $aProducts[52],
                                                            '06'=> $aProducts[53],
                                                            '07'=> $aProducts[54],
                                                            '08'=> $aProducts[55],
                                                            '09'=> $aProducts[56],
                                                            '10'=> $aProducts[57],
                                                            '11'=> $aProducts[58],
                                                            '12'=> $aProducts[59],
                                                            '13'=> $aProducts[60],
                                                            '14'=> $aProducts[61],
                                                            '15'=> $aProducts[62],
                                                            '16'=> $aProducts[63],
                                                            '17'=> $aProducts[64],
                                                            '18'=> $aProducts[65],
                                                            '19'=> $aProducts[66],
                                                            '20'=> $aProducts[67], 
                                                        ));
                }
        }   
        return $aBINs;
}





?> 