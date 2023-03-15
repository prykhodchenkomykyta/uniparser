<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 01/18/2022
Revision: 06/03/2022
Name: Radovan Jakus
Version: 2.8
Notes: Adding Reference1 Composite Field
******************************/


/*Production Environment*/
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/i2c/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sFedexOutputDir = "/var/TSSS/Files/FEDEX/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sMailMergeOutputDir = "/var/TSSS/Files/MAILMERGE/";
$sMailMergeBadDataOutputDir = "/var/TSSS/Files/MAILMERGE/BAD_DATA/";
$sConfirmationReportDir = "/var/TSSS/Files/Reports/I2C/";
$sShipmentReportDir = "/var/TSSS/Files/Reports/I2C/waiting/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/i2c/";
$sProductConfigFile = "/home/erutberg/Radovan/Products_Configuration_I2C.csv";
$sDataMatrixImgDir =  "/var/TSSS/Files/MAILMERGE/IMAGES/";
$sQRCodeImgDir =   "/var/TSSS/Files/MAILMERGE/IMAGES/";
$sIMBurl = "https://atlas.tagsystems.net/barcode/imb/";
$sDataMatrixurl = "https://atlas.tagsystems.net/barcode/datamatrix/";
$sQRurl = "https://atlas.tagsystems.net/barcode/qr/";
$sSerialNumberurl = "https://atlas.tagsystems.net/barcode/serial/";
$SerialNumberLocal ="/home/erutberg/Radovan/SerialNumberCounter.csv";
$sCompositeFieldReference1Dir = "/home/erutberg/Radovan/Reference1.php";



// $sInputDir = "D:\Workspace\TagSystem\Parser_Plugin\I2C\in\\";
// $sOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\I2C\out\\";
// $sBulkOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\I2C\out\\USPS\\";
// $sMailOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\I2C\out\\USPS\\";
// $sMailMergeOutputDir = "D:\\Workspace\\TagSystem\Parser_Plugin\\I2C\\out\\MAILMERGE\\";
// $sMailMergeBadDataOutputDir = "D:\\Workspace\\TagSystem\Parser_Plugin\\I2C\\out\\MAILMERGE\\BAD_DATA\\";
// //$sMailOutputDir = "D:\\Production Data\\stamps\\indicium\\";
// $sFedexOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\I2C\out\\FEDEX\\";
// $sShipsiOutputDir = "D:\\Production Data\\stamps\\indicium\\";
// $sProcessedDir = "D:\Workspace\TagSystem\Parser_Plugin\I2C\in\\"; 
// $sProductConfigFile = "D:\\Workspace\\TagSystem\\Parser_Plugin\\I2C\\Products_Configuration_I2C.csv";
// $sConfirmationReportDir = "D:\Workspace\TagSystem\Parser_Plugin\I2C\out\REPORT\\";
// $sShipmentReportDir = "D:\Workspace\TagSystem\Parser_Plugin\I2C\out\REPORT\\SHIPMENT_REPORT\\";
// $sDataMatrixImgDir = "D:\\Workspace\\TagSystem\Parser_Plugin\\I2C\\out\\MAILMERGE\\IMAGES\\";
// $sQRCodeImgDir =  "D:\\Workspace\\TagSystem\Parser_Plugin\\I2C\\out\\MAILMERGE\\IMAGES\\";
// $sIMBurl = "https://atlas.tagsystems.net:8443/barcode/imb/";
// $sDataMatrixurl = "https://atlas.tagsystems.net:8443/barcode/datamatrix/";
// $sQRurl = "https://atlas.tagsystems.net:8443/barcode/qr/";
// $sSerialNumberurl = "https://atlas.tagsystems.net:8443/barcode/serial/";
// $SerialNumberLocal ="D:\\Workspace\\TagSystem\\Parser_Plugin\\I2C\\SerialNumberCounter.csv";
// $sCompositeFieldReference1Dir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Reference1.php";


 //Mailer Information
 $BarcodeID = "00";
 $ServiceTypeID = "270";
 $MailerID = "902695246";
 $maxRec = 1000;
 $SerialNumberOfDigits = (strlen($MailerID)==9)? 6 : 9;


//Options to run to NOAPI, APISN, APIQR, APIDM, APIIMB, APIIMBDM, DATAMATCHING
// NOAPI if used all API will be IGNORED must be removed if any API is supposed to be used
// APISN generated Serial Number using API, if fails to connect to API it will use local Serial number but file will be created as BAD DATA file 
// APIQR generate QR image file using API, if it fails connect to API it will still pass QR value but not image name and file will be created as BAD DATA file
// APIDM generate DM image file using API, if it fails connect to API it will create local DataMatrix using either local or API serial number no routing number, but the image would fail and file will be created as BAD DATA File
// APIIMB generate IMB using API, if it it fails connect to API there will not be IMB and bad DATA file will be created.
// APIIMBDM generate IMB and DM using IMB API,will create IMB and DataMatrix base on IMB if it fails bad data file will be created.  
// DATAMATCHING pass data for DATAMATCHING such as Track2 data

$sAPIOptions = "DATAMATCHING";

//Options to run to DATAPREP, MAILING, MAILMERGE, CONFIRMATION_REPORT, SHIPMENT_REPORT
$sProcessingOptions = "DATAPREP,MAILING,MAILMERGE,CONFIRMATION_REPORT";



 //Mailer Information
 $BarcodeID = "00";
 $ServiceTypeID = "270";
 $MailerID = "902695246";
 $maxRec = 1000;
 
 $BATCH_LOG_ID;
 $iNumberOfRecords;
 $sDataPrepProfile;
 $sCustomerName;
$sDataPrepProfile;
$sCustomerName;
$sBIN;
$bIsExtendedBINused;
$aBINs = getProductsList($sProductConfigFile);

$aErrors = [];

$aProducts;
$iNoErrorRecs;
$sFlexiaFileName;

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
                    //	echo("PRODUCTS\n");
                    //	print_r($aProducts);
                    $aBINs[$aProducts[1]]['Customer']=$aProducts[0];
                    $aBINs[$aProducts[1]][$aProducts[2]][$aProducts[3]]=array(
                        "Profile"=>$aProducts[5],
                        "Product"=>$aProducts[4],
                        "ServiceType"=>$aProducts[12],
                        "PackageType"=>$aProducts[13],
                        "WeightOz"=>$aProducts[14],
                        "ShipDate"=>$aProducts[15],
                        "FromFullName"=>$aProducts[16],
                        "FromAddress1"=>$aProducts[17],
                        "FromAddress2"=>$aProducts[18],
                        "FromCity"=>$aProducts[19],
                        "FromState"=>$aProducts[20],
                        "FromCountry"=>$aProducts[21],
                        "FromZIPCode"=>$aProducts[22], 
                        "ShippingMethods" => array (1=> $aProducts[7],
                                                    2=> $aProducts[8],
                                                    3=> $aProducts[9],
                                                    4=> $aProducts[10],
                                                    5=> $aProducts[11])
                    );
                }
        }   
        return $aBINs;
}
//echo"aBINs:\n";
//print_r($aBINs);

date_default_timezone_set ("America/New_York");
$sDateStamp = date('Y-m-d H:i:s', time());
$sUser = get_current_user();
echo "\n$sDateStamp [$sUser]: Starting Script \n";

$aOptions  = getopt("p::n::");
$sInputFilePath;

if(!empty($aOptions ['p'])){
    $sInputFilePath = $aOptions ['p'];
    echo "$sDateStamp [$sUser]: Using full path option \n";
    if(file_exists($sInputFilePath))
        {
            I2CParserToCSV ($sInputFilePath, $sOutputDir, $sProcessedDir, $sBulkOutputDir, $sMailOutputDir);
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
        I2CParserToCSV ($sInputFilePath, $sOutputDir, $sProcessedDir, $sBulkOutputDir, $sMailOutputDir);
    }
    else
    {
        die("\nERROR: The file does not exist. Check the name or path of the file. FILE:".$sInputFilePath);  
    }
}
else{
 echo "$sDateStamp [$sUser]: Using option to process files from predefined directory automatically. Directory: $sInputDir \n";
 $aInputFiles = glob("$sInputDir*.csv");
    if($aInputFiles){
            foreach($aInputFiles as $sInputFilePath){
                echo "\t".basename($sInputFilePath)." \n";
            }
            $file = 0;
            foreach($aInputFiles as $sInputFilePath){
            
                progressBar(++$file,count($aInputFiles));
               
                $bFileProcessed = I2CParserToCSV($sInputFilePath, $sOutputDir, $sProcessedDir, $sBulkOutputDir, $sMailOutputDir); 
                if($bFileProcessed)
                {
                    $sProcessedFilename = basename($sInputFilePath);
                    $bFileMoved = rename($sInputFilePath ,$sProcessedDir.$sProcessedFilename);
                    if($bFileMoved)
                    {
                        echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sProcessedFilename \n";
                        echo "$sDateStamp [$sUser]: Total Number of records: $iNumberOfRecords in file: $sProcessedFilename  \n"; 
                        echo "$sDateStamp [$sUser]: Total Number of good records: ".($iNumberOfRecords-$iNoErrorRecs)." in file: $sProcessedFilename  \n"; 
                        echo "$sDateStamp [$sUser]: Total Number records that errored out: $iNoErrorRecs in file: $sProcessedFilename  \n"; 
                        getDetailOverview($bFileProcessed);
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


/*@DataPrepToMRF function process Galileo input file and transform it to DataPrep input file & Mailing CSV file. 
    $inputDir - string path to the file that will be processed
    $outputDir - string path to the directory where file will be saved DataPrep results
*/

function I2CParserToCSV  ($inputDir, $outputDir, $processedDir, $bulkOutputDir, $mailOutputDir) {
    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
    global $sDataPrepProfile;
    global $aBINs;
    global $sBIN;
    global $aShippingMethods;
    global $sMailOutputDir;
    global $sFedexOutputDir;
    global $iNumberOfRecords;
    //global $sOriginalFile;
    global $sConfirmationReportDir;
    global $sShipmentReportDir;
    global $aErrors;
    global $iNoErrorRecs;
    global $SerialNumberOfDigits;
    global $bIsExtendedBINused;
    global $BarcodeID;
    global $ServiceTypeID;
    global $MailerID;
    global $sDataMatrixurl;
    global $sQRurl;
    global $sIMBurl;
    global $sSerialNumberurl;
    global $SerialNumberLocal;
    global $sQRCodeImgDir;
    global $sDataMatrixImgDir;
    global $sAPIOptions;
    global $sProcessingOptions;
    global $sMailMergeBadDataOutputDir;
    global $sMailMergeOutputDir;
    global $maxRec;
    global $sCompositeFieldReference1Dir;


    

    /*SERIAL NUMBER*/
    $SerialNumber = getSerialNumber($SerialNumberLocal,$SerialNumberOfDigits);
    $SerialNumber = str_pad($SerialNumber,$SerialNumberOfDigits,'0',STR_PAD_LEFT);        
  
  
    
    $Status = "";
    $ErrorCode = "";
    $ErrorDescription = "";
    $bHasError = false;
    $aHasError = array();
    $MAX_FIELDS = 106;
    

    echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";
   
    $iNoErrorRecs = 0;
    $sProcessedFilename = basename($inputDir);
    $sFileName = basename($inputDir, "csv")."csv";
    $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);
    $iNumberOfRecords = count($aInputFile);
    if($iNumberOfRecords==0)
    {
        echo "\n$sDateStamp [$sUser]: ERROR: The $sProcessedFilename does not contain any data, the file is empty.  \n";
        return false;
    }

    echo "$sDateStamp [$sUser]: Starting Parsing Data\n";
    $iRecordNo =0;
    //print_r($aInputFile);
    $aInputFile = array_slice($aInputFile, 1);

    foreach($aInputFile as $aData)
    {
        if(strlen($SerialNumber)>$SerialNumberOfDigits)
        {
            $SerialNumber = 1;
        }
        $aInputFileData =str_getcsv($aData);
        $aInputFileData['SerialNumber'] =  str_pad($SerialNumber++,$SerialNumberOfDigits,'0',STR_PAD_LEFT); 
        $aRecordData[] =$aInputFileData;
        setSerialNumber($SerialNumberLocal,$SerialNumberOfDigits,$SerialNumber);
    }   

    $sBIN = findCustomer($aRecordData, $aBINs);
    if(!$sBIN)
    {
        echo "\n$sDateStamp [$sUser]: ERROR: The $sProcessedFilename does not contain any BIN data related to any customers from Products Configuration  \n";
        return false;
    }
    $sCustomerName = $aBINs[$sBIN]['Customer'];

    //$sDataPrepProfile =  $aBINs[$sBIN]['Profile'];
    $aFilesWritingStatus = [];
    echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n";
    echo "$sDateStamp [$sUser]: BIN: $sBIN \n";
    
    /*DATAPREP*/
    $aDataPrepOutputData = [];
    
    /*MAILING*/
    $aBulkShippingOutputData = [];
    $aMailShippingOutputData = [];

    /*REPORT*/
    $aConfirmationReportOutputData =[];
    $bBulk = false;

  

    /*PARSING*/
   
    
        

      $iNumberOfRecords = count($aRecordData);
        //print_r($aRecordData);
    foreach($aRecordData as $aRecord) { 
         
            ++$iRecordNo;
    
           progressBar($iRecordNo,$iNumberOfRecords);
        
            
        
            
             if(count($aRecord)!=$MAX_FIELDS)
            {
                $iNoErrorRecs++;
                $sError = "";
                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, the CSV file has more than expected fields. File contains possible unescaped comma. Max expected CSV fields $MAX_FIELDS, the fields in the record is ".count($aRecord)."\n"; 
                $aErrors[] = $sError;
                echo $sError;
                $Status = "NOK";
                $ErrorCode = "305";
                $ErrorDescription = "Data Format Error";
                $bHasError= true;
                $ProductProp['Product'] = "NOK";
                //$ProductProp['ShippingMethods'][(!isset(trim($aRecord['20']))] = "NOK";
                $ProductID = !isset($aRecord['9']) ? "bad data format cannot access value": trim($aRecord['9']);
                $Token = !isset($aRecord['1']) ? "bad data format cannot access value": trim($aRecord['1']);
                $FileName = $sProcessedFilename;
                $ShipSuffix = "";
                //$sBIN;
                //$Status;
                //$ErrorCode;
                //$ErrorDescription;
                $DateReceived = "N/A";
                //$DateReceived = filemtime($sOriginalFile.str_replace("emb","pgp",$sProcessedFilename));
                $CardType = !isset($aRecord['1']) ? "bad data format cannot access value": trim($aRecord['1']);
                $PAN4 = !isset($aRecord['2']) ? "bad data format cannot access value": substr(trim($aRecord['2']), -4);
                $san2 = "";
                $name1 = !isset($aRecord['3']) ? "bad data format cannot access value": trim($aRecord['3']);
                $name2 = "";
                $Address1 = !isset($aRecord['11']) ? "bad data format cannot access value": trim($aRecord['11']);
                $Address2 = !isset($aRecord['12']) ? "bad data format cannot access value": trim($aRecord['12']);
                $City = !isset($aRecord['16']) ? "bad data format cannot access value": trim($aRecord['16']);
                $State =  !isset($aRecord['17']) ? "bad data format cannot access value": trim($aRecord['17']);
                $ZIPCode =  !isset($aRecord['18']) ? "bad data format cannot access value": trim($aRecord['18']);
                $Country =  !isset($aRecord['19']) ? "bad data format cannot access value": trim($aRecord['19']);
                $aConfirmationReportOutputData[] = array($Token,$FileName,"$ShipSuffix",$sBIN,$Status,"$ErrorCode","$ErrorDescription",$DateReceived,$CardType,$PAN4,$san2,"$name1","$name2","$Address1","$Address2",$City,$State,$ZIPCode,$Country);
                $aConfirmationReportHeader = implode(",",array("Token", "FileName", "ShipSuffix","BIN","Status", "ErrorCode", "ErrorDescription", "DataReceived","CardType", "PAN4","san2","Name1","Name2","Address1","Address2","City","State","ZipCode","Country"));
   
                if($iNumberOfRecords==$iNoErrorRecs)
                {
                    echo "$sDateStamp [$sUser]: ERROR: All the records in  $inputDir contains error, therefor this file cannot be processed.\n";
                    echo "$sDateStamp [$sUser]: \n\n CONFIRMATION REPORT START \n\n";
                    $sConfirmationReportOutputFile = $sConfirmationReportDir.(preg_replace("/(\.).*/","",$FileName)).".conf_rep.csv";
                    $fp = fopen($sConfirmationReportOutputFile,"w");
                    fwrite($fp,$aConfirmationReportHeader).fwrite($fp, "\r\n");

                    foreach($aConfirmationReportOutputData as $row)
                    {
                    
                        //$bFileWriting1 =fwrite($fp, implode(",",$row)).fwrite($fp, "\r\n");
                        $bFileWriting1 =fputcsv($fp, $row);
                        $aFilesWritingStatus[] = $bFileWriting1;
                    }
                        if($bFileWriting1)
                        {
                            echo "$sDateStamp [$sUser]: Report File for batch #: ".trim($aRecord['0'])." succesfully written as: $sConfirmationReportOutputFile\n";
                            fclose($fp);

                        }
                        else 
                        {
                            echo "$sDateStamp [$sUser]: Writing Report file for batch ".trim($aRecord['0'])." failed\n";
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

            $PRODUCT_ID = trim($aRecord['9']);
            $SHIPPING_METHOD = trim($aRecord['20']);
            $PAN_CARD_1 = str_replace(' ', '', trim($aRecord['2']));
            $CARD_HOLDER_NAME_CARD_1 = trim($aRecord['3']);
            $CARD_HOLDER_NAME_CARD_2 = "";
            $CARD_STOCK_ID = "NA";
            $sBIN = substr(trim($aRecord['2']), 0,6);
            if($bIsExtendedBINused)
            {
                $sBIN = substr(trim($aRecord['2']), 0,8);
            }
            $SHIPADDRESS_LINE_1 = trim($aRecord['11']);
            $SHIPADDRESS_LINE_2 = empty(trim($aRecord['12'])) ? "" : trim($aRecord['12']);
            $SHIP_CITY = trim($aRecord['16']);
            $SHIP_STATE = trim($aRecord['17']);
            $SHIP_ZIP = substr(trim($aRecord['18']),0,5);
            $PAYMENT_REFERENCE_NUMBER = trim($aRecord['1']);
            $SHIPNAME_PAN_PRN =  trim($aRecord['5']);
            $BATCH_LOG_ID =  trim($aRecord['0']);
            $Facility = "";


            $sBIN=substr(preg_replace('/\s+/', "", $PAN_CARD_1),0,6); 
            
            if(empty(trim($CARD_STOCK_ID)))
            { 
                $CARD_STOCK_ID = "NA";
            }

            $iPanPosition ="";
            $sMaskedPAN = "";
            $sMaskedTrack1 ="";
            $sPAN = trim($aRecord['2']);

            $iPanPosition = strpos($sPAN,$sBIN);
            if($iPanPosition!==false)
            {
                $iBINln = 6;
                $iPANln = strlen($sPAN);
                $iMaskedCharsln = abs($iPANln-4-$iBINln);
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
                if(isset($aBINs[$sBIN][trim($PRODUCT_ID)]))
                {
                    //ERROR CHECK TO CONFIRM CARD STOCK
                    if(isset($aBINs[$sBIN][trim($PRODUCT_ID)][trim($CARD_STOCK_ID)]))
                    {
            
                        
                            $ProductProp = $aBINs[$sBIN][trim($PRODUCT_ID)][trim($CARD_STOCK_ID)];
                            $ProductID = trim($PRODUCT_ID);
                            $Status = "OK";
                            $ErrorCode = "N/A";
                            $ErrorDescription = "N/A";
                            $bHasError= false;

                             //DATA VALIDATION
                            if(!preg_match('/%?B\d{1,19}\^[-\w\s\/]{2,26}\^([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\??/',trim($aRecord['7'])))
                            {
                                
                                $iPanPosition = strpos(trim($aRecord['7']),$sBIN);
                                if($iPanPosition!==false)
                                {
                                    $iBINln = 6;
                                    $iPANln = strlen($sPAN);
                                    $iMaskedCharsln = abs($iPANln-4-$iBINln);
                    
                                    $sMaskedTrack1 = substr_replace(trim($aRecord['7']),"XXXXXX",$iPanPosition+strlen($sBIN), $iMaskedCharsln);
                                }
                                else
                                {
                                    $sMaskedTrack1 = "unable to mask the track data - view not allowed";
                                }
                                $iNoErrorRecs++;
                                //$iNoErrorRecs++;
                                $sError = "";
                                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." with PAN ".$sMaskedPAN." , Track1 data have incorrect magnetic stripe format, received value: ".$sMaskedTrack1." \n";
                                $aErrors[] = $sError;
                                echo $sError;
                                $Status = "NOK";
                                $ErrorCode = "306";
                                $ErrorDescription = "Data validation error - Magnetic Stripe Track1 format";
                                $bHasError= true;
                                $ProductProp['Product'] = "NOK";
                                $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                $ProductID = trim($PRODUCT_ID);
                                
                            }
                    }
                    else
                    {
                        $iNoErrorRecs++;
                        $sError = "";
                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." with PAN ".$sMaskedPAN." ,the card stock ID: ".trim($CARD_STOCK_ID)." is not defined in Products_configuration.csv. Please, review products configuration. \n";
                        $aErrors[] = $sError;
                        echo $sError;
                        $Status = "NOK";
                        $ErrorCode = "303";
                        $ErrorDescription = "The card stock ID from the file: ".trim($CARD_STOCK_ID).", is unknown";
                        $bHasError= true;
                        $ProductProp['Product'] = "NOK";
                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                        $ProductID = trim($PRODUCT_ID);
                        
                    }
                }
                else
                {
                    $iNoErrorRecs++;
                    $sError = "";
                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." with PAN ".$sMaskedPAN." ,the product ID: ".trim($PRODUCT_ID)." is not defined in Products_configuration.csv. Please, review products configuration. \n";
                    $aErrors[] = $sError;
                    echo $sError;
                    $Status = "NOK";
                    $ErrorCode = "302";
                    $ErrorDescription = "The product ID from the file: ".trim($PRODUCT_ID).", is unknown";
                    $bHasError= true;
                    $ProductProp['Product'] = "NOK";
                    $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                    $ProductID = trim($PRODUCT_ID);
                   
                }
            }
            else
            {
                $iNoErrorRecs++;
                $sError = "";
                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." with the PAN ".$sMaskedPAN." , the BIN: ".$sBIN." is not defined in Products_configuration.csv. Please, review products configuration.\n";
                $aErrors[] = $sError;
                echo $sError;
                $Status = "NOK";
                $ErrorCode = "301";
                $ErrorDescription = "The BIN from the file: ".$sBIN.", is unknown";
                $bHasError= true;
                $ProductProp['Product'] = "NOK";
                $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                $ProductID = trim($PRODUCT_ID);
                
     
            }

      

             /*CONFIRMATION REPORT*/
             //INIT CONFIRMATION REPORT
             $Token = trim($PAYMENT_REFERENCE_NUMBER);
             $FileName = $sProcessedFilename;
             $ShipSuffix = $sCustomerName."_".$BATCH_LOG_ID."_".$ProductProp['Product']."_".$ProductProp['ShippingMethods'][$SHIPPING_METHOD]."_".trim($SHIPNAME_PAN_PRN)."_".substr($PAN_CARD_1, -4);
             //$sBIN;
             //$Status;
             //$ErrorCode;
             //$ErrorDescription;
             $DateReceived = "N/A";
             //$DateReceived = filemtime($sOriginalFile.str_replace("emb","pgp",$sProcessedFilename));
             $CardType = trim($PRODUCT_ID);
             $PAN4 = substr($PAN_CARD_1, -4);
             $san2 = "";
             $name1 = trim($CARD_HOLDER_NAME_CARD_1);
             $name2 = "";
             $Address1 = trim($SHIPADDRESS_LINE_1);
             $Address2 = trim($SHIPADDRESS_LINE_2);
             $City = trim($SHIP_CITY);
             $State =  trim($SHIP_STATE); 
             $ZIPCode = trim($SHIP_ZIP);
             if( (trim($aRecord['19']=="USA")) || (trim($aRecord['19']=="United States of America"))){

                $Country = "US";
                }
                else 
                {
                    $Country =trim($aRecord['19']);
                }  

             $aConfirmationReportOutputData[] = array($Token,$FileName,"$ShipSuffix",$sBIN,$Status,"$ErrorCode","$ErrorDescription",$DateReceived,$CardType,$PAN4,$san2,"$name1","$name2","$Address1","$Address2",$City,$State,$ZIPCode,$Country,$Facility);
             $aConfirmationReportHeader = implode(",",array("Token", "FileName", "ShipSuffix","BIN","Status", "ErrorCode", "ErrorDescription", "DataReceived","CardType", "PAN4","san2","Name1","Name2","Address1","Address2","City","State","ZipCode","Country","Facility"));

             
            if($iNumberOfRecords==$iNoErrorRecs)
            {
                echo "$sDateStamp [$sUser]: ERROR: All the records in  $inputDir contains error, therefor this file cannot be processed.\n";
                return false;

            }
            if($bHasError)
            {
                  //DO NOT WRITE RECORD TO REST OF THE FILE
                  continue;
            }
                /*DATAPREP*/
                $sTrack1 = preg_replace('/\./','',trim($aRecord['7']));
                $sTrack2 =str_replace(";","", trim($aRecord['8']));
                $sICVV = trim($aRecord['62']);
                $PSN = trim($aRecord['63']);
                $CVC2 = trim($aRecord['6']);
                $sEmbName = strtoupper(trim($aRecord['3']));
                $sBatchID = $BATCH_LOG_ID."/".$iRecordNo;
                $sUniqueNumber= sha1($PAN_CARD_1);
                $sNotUsed1 = "0000";
                $sNotUsed2 = "00";
                $sNotUsed3 = "000";
                $sDataPrepProfile =  $ProductProp['Profile'];
                $sNotUsed4 = "0000000";
                $sChipData = "$sTrack1#$sTrack2#$sICVV#$CVC2#$PSN#$sEmbName";
           
            //DATAPREP RESULT

             //Used by Reference1.php
             $sCustomerName3 = substr($sCustomerName,0,3);
             $sProductName = $ProductProp['Product'];
             $sProductName3 = substr($sProductName,0,3);
             $sSerialNumber = $SerialNumber;
             $sPAN4 = substr($sPAN, -4);
             
            /*MAILING*/
            $FullName = trim($aRecord['4']);
            $Company ="";
            $Address1 = trim($aRecord['11']);
            $Address2 = empty(trim($aRecord['12'])) ? "" : trim($aRecord['12']);
            $City = trim($aRecord['16']);
            $State =  trim($aRecord['17']); 
            $ZIPCode = substr(trim($aRecord['18']),0,5);
            $ZIPCodeAddOn = empty(substr($aRecord['18'],5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['18']),5));
            if( (trim($aRecord['19']=="USA")) || (trim($aRecord['19']=="United States of America"))){

                $Country = "US";
            }
            else 
            {
                $Country =trim($aRecord['19']);
            }               
            $FromFullName =  $ProductProp["FromFullName"];
            $FromAddress1 =  $ProductProp["FromAddress1"]; 
            $FromAddress2 =  $ProductProp["FromAddress2"]; 
            $FromCity = $ProductProp["FromCity"];
            $FromState=  $ProductProp["FromState"];
            $FromCountry =  $ProductProp["FromCountry"];
            $FromZIPCode =  $ProductProp["FromZIPCode"];
          
            $ServiceType = $ProductProp["ServiceType"];
            $PackageType = $ProductProp["PackageType"];
            $WeightOz = $ProductProp["WeightOz"];
            $ShipDate = $ProductProp["ShipDate"];
            $ImageType = "Pdf";
            
            $Reference2 = strtoupper(hash("sha256",trim($aRecord['2']), false));
            $Reference3 = trim($aRecord['1']);
            $Reference4 = "";

           
            if(empty($ServiceType)){
                switch($ProductProp["ShippingMethods"][trim($aRecord['20'])])
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
                switch($ProductProp["ShippingMethods"][trim($aRecord['20'])])
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

            /*MAIL MERGE -> Standard mail adding more fields*/
            if(preg_match('/MAILMERGE/', strtoupper($sProcessingOptions)))
            {

                        //DATAMATCHING TRACK2
                        $DataMatching = "";
                        if(preg_match('/DATAMATCHING/', strtoupper($sAPIOptions)))
                            $DataMatching =  str_replace("?","",str_replace(";","", trim($aRecord['8'])));
                        
                        //QR CODE
                        $QRCode = trim($aRecord['1']);
                        $QRImgFileName = "";
                        $QRCodeRequest = base64_encode($QRCode);
                        if(preg_match('/APIQR/', strtoupper($sAPIOptions))&&!preg_match('/NOAPI/', strtoupper($sAPIOptions)))
                        {
                                $QRCodeImagePng = AtlasRequestBase64($sQRurl, $QRCode);
                                if($QRCodeImagePng['result']==-1)
                                {
                                    //$QRCodeImageBase64 = $QRCodeImageBase64['data'];
                                    $QRImgFileName = $QRCodeImagePng['data'];
                                    $aHasError[] = true;
                                }
                                else
                                {
                                    //$QRCodeImageBase64 = $QRCodeImageBase64;
                                    $QRImgFileName = $sCustomerName."_".$Reference3."_QR.png";
                                    file_put_contents("$sQRCodeImgDir$QRImgFileName",$QRCodeImagePng['data']);
                                    $aHasError[] = false;         
                                }
                        }

                        //REQUEST SERIAL NUMBER
                        $SerialNumber = trim($aRecord['SerialNumber']);
                        if(preg_match('/APISN/', strtoupper($sAPIOptions))&&!preg_match('/NOAPI/', strtoupper($sAPIOptions)))
                        {
                            
                            $SerialNumberReq = AtlasRequestStd($sSerialNumberurl, $SerialNumberOfDigits);
                            if($SerialNumberReq['result']!=0)
                            {
                                $aHasError[] = true;
                                echo "$sDateStamp [$sUser]: Error: Serial Number code - will use local serial number\n";
                            }
                            else
                            {
                                $SerialNumber =$SerialNumberReq['data'];
                                $aHasError[] = false;
                            }
                        }

                        //DATA MATRIX WITHOUT ROUTING CODE and IMB
                        $DataMatrix = $BarcodeID.$ServiceTypeID.$MailerID.$SerialNumber.",,,,".$sDateStamp.",".$BATCH_LOG_ID.",".$QRCode;
                        $DataMatrixImgFileName = "";
                        $DataMatrixRequest = base64_encode($DataMatrix);
                        if(preg_match('/APIDM/', strtoupper($sAPIOptions))&&!preg_match('/NOAPI/', strtoupper($sAPIOptions)))
                        {
                            
                            $DataMatrix = $BarcodeID.$ServiceTypeID.$MailerID.$SerialNumber.",,,,".$sDateStamp.",".$BATCH_LOG_ID.",".$QRCode;
                            $DataMatrixPng = AtlasRequestBase64($sDataMatrixurl, $DataMatrix);
                            if($DataMatrixPng['result']!=0)
                            {
                                //$DataMatrixPng = $DataMatrixPng['data'];
                                $DataMatrixImgFileName = $DataMatrixPng['data'];
                                $aHasError[] = true;
                                echo "$sDateStamp [$sUser]: Error: DataMatrix code\n";
                            }
                            else 
                            {
                                $DataMatrixImgFileName = $sCustomerName."_".$Reference3."_DATAMATRIX.png";
                                file_put_contents("$sDataMatrixImgDir$DataMatrixImgFileName",$DataMatrixPng['data']);
                                $aHasError[] = false;
                            }
                        }

                        //IMB CAN INCLUDE DATAMATRIX
                        $IMB1 = "";
                        if(preg_match('/APIIMB/', strtoupper($sAPIOptions)) && !preg_match('/NOAPI/', strtoupper($sAPIOptions))){
                        
                            $aToGetZip4 = array("BarcodeID"=>$BarcodeID,
                                                "ServiceType"=>$ServiceTypeID,
                                                "MailerID"=>$MailerID,
                                            // "SerialNumber"=>$TagRecordSequenceNumber,
                                                "Address1"=>$Address1,
                                                "Address2"=>$Address2,
                                                "City"=>$City,
                                                "State"=>$State
                                                );
                        
                            //$Zip4 = lookupZip($sUserID,$Address1,preg_replace('/#/',"",$Address2),$City,$State,$ZIPCode,$iiRecordNo);
                            $aIMBSerialNo4Zip = AtlasRequestJSON($sIMBurl, $aToGetZip4);
                            if($aIMBSerialNo4Zip['result']==0)
                            {
                                    $IMB1 = $aIMBSerialNo4Zip['data'];
                                    $SerialNumber = $aIMBSerialNo4Zip['SerialNumber'];
                                    $RoutingCode = preg_replace("/-/","",$aIMBSerialNo4Zip['RoutingCode']);

                                    if(preg_match('/APIIMBDM/', strtoupper($sAPIOptions)&&!preg_match('/NOAPI/', strtoupper($sAPIOptions))))
                                    {
                                        $DataMatrix = $BarcodeID.$ServiceTypeID.$MailerID.$SerialNumber.",".$RoutingCode.",,,".$sDateStamp.",".$BATCH_LOG_ID.",".$QRCode;
                                        $DataMatrixRequest = base64_encode($DataMatrix);
                                        $DataMatrixPng = AtlasRequestBase64($sDataMatrixurl, $DataMatrix);
                                        if($DataMatrixPng['result']==-1)
                                        {
                                            //$DataMatrixPng = $DataMatrixPng['data'];
                                            $DataMatching ="";
                                            $DataMatrixImgFileName = $DataMatrixPng['data'];
                                            $aHasError[] = true;
                                        }
                                        else 
                                        {
                                            $DataMatrixImgFileName = $sCustomerName."_".$Reference3."_DATAMATRIX.png";
                                            file_put_contents("$sDataMatrixImgDir$DataMatrixImgFileName",$DataMatrixPng['data']);
                                            $aHasError[] = false;
                                        }
                                    }
                                    $aHasError[] = false;
                            }
                            else
                            {
                                $IMB1 = $aIMBSerialNo4Zip['data']; 
                                if(preg_match('/APIIMBDM/', strtoupper($sAPIOptions))&&!preg_match('/NOAPI/', strtoupper($sAPIOptions)))
                                {
                                    
                                        $IMB1 = $aIMBSerialNo4Zip['data'];
                                        $DataMatrix = $aIMBSerialNo4Zip['data'];
                                        $DataMatrixRequest = $aIMBSerialNo4Zip['data'];
                                }
                                $aHasError[] = true;
            
                            }
                        }
            }
            
            $Reference1 = include($sCompositeFieldReference1Dir);
           // $Reference1 = substr($sCustomerName,0,3)."_".$SerialNumber."_".substr($aRecord['2'], -4);                

            //SHIPMENT REPORT
            $Token =   trim($PAYMENT_REFERENCE_NUMBER);
            //$ShipmentMethod = $aBINs[$sBIN]['ShippingMethods'][trim($SHIPPING_METHOD)]."|".$ServiceType."|".$ProductProp["PackageType"];
            if( $ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)]=="FEDx")
            {
                 $ShipmentMethod = $ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)];
            }
            else
            {
                $ShipmentMethod = $ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)]."|".$ServiceType;
            }
            $Tracking =  "Not Available";
            $name1 =  preg_match('/,/',trim($SHIPNAME_PAN_PRN)) ? "\"".trim($SHIPNAME_PAN_PRN)."\"" : trim($SHIPNAME_PAN_PRN);
            $name2 =  preg_match('/,/',trim($CARD_HOLDER_NAME_CARD_2)) ? "\"".trim($CARD_HOLDER_NAME_CARD_2)."\"" : trim($CARD_HOLDER_NAME_CARD_2);
            $adr1 =   preg_match('/,/',trim($SHIPADDRESS_LINE_1)) ?  "\"".trim($SHIPADDRESS_LINE_1)."\"" :  trim($SHIPADDRESS_LINE_1);
            $adr2 =   preg_match('/,/',trim($SHIPADDRESS_LINE_2)) ?  "\"".trim($SHIPADDRESS_LINE_2)."\"" : trim($SHIPADDRESS_LINE_2);
            $city =   preg_match('/,/',trim($SHIP_CITY)) ? "\"".trim($SHIP_CITY)."\"" :  trim($SHIP_CITY);
            $state =  preg_match('/,/',trim($SHIP_STATE)) ? "\"". trim($SHIP_STATE)."\"":  trim($SHIP_STATE);
            $zipcode = preg_match('/,/',trim($SHIP_ZIP)) ? "\"".trim($SHIP_ZIP)."\"" :  trim($SHIP_ZIP);

            if($ServiceType=="US-PM")
                {
                    $ForecastDeliveryDate =  date('m/d/Y',strtotime(' + 2 days'));
                }
                else if($ServiceType=="US-FC")
                {
                    $ForecastDeliveryDate =   date('m/d/Y',strtotime(' + 4 days'));
                }
            $Product = $ProductProp["Product"];
           
            $aDataPrepOutputData[$sBIN][$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData);
            $aShipmentReportOutputData[] = array($Token,$ShipmentMethod,$Tracking,$name1,$name2,$adr1,$adr2,$city,$state,$zipcode,$ForecastDeliveryDate,$Product,$Status);
            $aShipmentReportOutputDataHeader = implode(",",array("Token","ShipmentMethod","Tracking","name1","name2","adr1","adr2","city","state","zipcode","ForecastDeliveryDate","Product","Status","\r\n"));
            $aMailShippingOutputData[$sBIN][$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);
            $aMailShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4","\r\n"));
  
            if(preg_match('/MAILMERGE/', strtoupper($sProcessingOptions)))
            {
                foreach($aHasError as $i)
                {
                    if($i==true)
                    {
                        $bHasError = true;
                    }

                }


                if($bHasError == true)
                {
                    $aMailMergeShippingOutputBadData[$sBIN][$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($Company, $FullName, $Address1, $Address2,
                    $City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry,
                    $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4,
                    $DataMatching,$QRCode,$QRImgFileName,$IMB1,$DataMatrix, $DataMatrixImgFileName,$QRCodeRequest,$DataMatrixRequest); 

                }
                else
                {
                    $aMailMergeShippingOutputData[$sBIN][$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($Company, $FullName, $Address1, $Address2,
                    $City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry,
                    $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4,
                    $DataMatching,$QRCode,$QRImgFileName,$IMB1,$DataMatrix, $DataMatrixImgFileName,$QRCodeRequest,$DataMatrixRequest);
                }
                $aMailMergeShippingOutputDataHeader =implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn",
                "Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType",
                "WeightOz","ShipDate", "ImageType", "Reference1", "Reference2", "Reference3","Reference4","DataMatching","QRCode", 
                "QRImgFileName","IMB1","DataMatrix", "DataMatrixImgFileName","QRCodeRequest","DataMatrixRequest","\r\n"));  
            }

    }
    
    $iNumberOfRecords = $iRecordNo;
    
    echo "$sDateStamp [$sUser]: Batch of the file: $BATCH_LOG_ID \n";
    echo "$sDateStamp [$sUser]: Parsing Data Finished\n";
    echo "$sDateStamp [$sUser]: Total Number of records: $iNumberOfRecords \n"; 
    echo "$sDateStamp [$sUser]: Total Number of good records: ".($iNumberOfRecords-$iNoErrorRecs)." \n"; 
    echo "$sDateStamp [$sUser]: Total Number of records that errored out: $iNoErrorRecs \n\n"; 
    
    //echo "aDataPrepOutputData\n";
    //print_r($aDataPrepOutputData);
    //echo "aMailShippingOutputData\n";
    //print_r($aMailShippingOutputData);

    if(preg_match('/DATAPREP/', strtoupper($sProcessingOptions)))
    {

        echo "$sDateStamp [$sUser]: \n\n DATAPREP START \n\n";

        //print_r($aDataPrepOutputData);
        //print_r($aBINs);
        foreach($aDataPrepOutputData as $sBIN => $Data)   
        {

            foreach($Data as $keyShipment => $aShippingRecord)
            {  
        
            //echo "aShippingRecord\n";
            //print_r($aShippingRecord);

        // $sShippingName = $ProductProp['ShippingMethods'][$keyShipment];
            //echo"\nSHIPPING NAME $sShippingName\n";
                foreach($aShippingRecord as $keyProduct => $aProductRecord)
                {
                    foreach($aProductRecord as $keyCardStock => $aCardStockRecord)
                    {
                            $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                            $sShippingName = $sProductProp['ShippingMethods'][$keyShipment];
                            $sProductName =  $sProductProp['Product'];
                            echo "$sDateStamp [$sUser]: DataPrep: Records Shipment $sShippingName and per product $sProductName: ".count($aCardStockRecord)."\n";
                        
                            
                            $bFileWriting1 = false; 
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
                                    $sDataPrepOutputFile = $outputDir."MS_CHIPREP_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_";
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
                                
                                echo "$sDateStamp [$sUser]: File for $BATCH_LOG_ID already exists\n";
                                fclose($fp);
                            }
                            else if($bFileWriting1)
                            {
                                echo "$sDateStamp [$sUser]: File for batch #: $BATCH_LOG_ID succesfully written as: $sDataPrepOutputFile.\n";
                                fclose($fp);
                            }
                            else
                            {
                                echo "$sDateStamp [$sUser]: Writing file for batch $BATCH_LOG_ID failed\n";
                                fclose($fp);

                            }
                            unset($aDataPrepOutputData);
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

    if(preg_match('/MAILING/', strtoupper($sProcessingOptions)))
    {
            echo "$sDateStamp [$sUser]: \n\n MAILING START \n\n";

            foreach($aMailShippingOutputData as $sBIN => $Data)    
            {
                foreach($Data as $keyShipment => $aShippingRecord) 
                {
                    // $sShippingName = $ProductProp['ShippingMethods'][$keyShipment];
                
                
                    foreach($aShippingRecord as $keyProduct => $aProductRecord)
                    {
                        foreach($aProductRecord as $keyCardStock => $aCardStockRecord)
                        {
                                $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                                $sShippingName = $sProductProp['ShippingMethods'][$keyShipment];
                                $sProductName =  $sProductProp['Product'];
                                if(preg_match('/FEDx/',$sShippingName))
                                {
                                    $mailOutputDir = $sFedexOutputDir;
                                }
                                else
                                {
            
                                    $mailOutputDir = $sMailOutputDir;
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
                                        $sDataPrepOutputFile =  $mailOutputDir."MAIL_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_";
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
                                            
                                            echo "$sDateStamp [$sUser]: File for $BATCH_LOG_ID already exists\n";
                                            fclose($fp);
                                        }
                                        else if($bFileWriting1)
                                        {
                                            echo "$sDateStamp [$sUser]: File for batch #: $BATCH_LOG_ID succesfully written as: $sDataPrepOutputFile.\n";
                                            fclose($fp);
                                        }
                                        else
                                        {
                                            echo "$sDateStamp [$sUser]: Writing file for batch $BATCH_LOG_ID failed\n";
                                            fclose($fp);

                                        }
                                        unset($aDataPrepOutputData);
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

    //MAILMERGE GOOD DATA
    if(preg_match('/MAILMERGE/', strtoupper($sProcessingOptions)))
    {
            echo "$sDateStamp [$sUser]: \n\n MAILMERGE START \n\n";

            if(!empty($aMailMergeShippingOutputData))
            {
                $outputDir = $sMailMergeOutputDir;
                
                foreach($aMailMergeShippingOutputData as $sBIN => $Data)    
                {
                    foreach($Data as $keyShipment => $aShippingRecord)     
                    {
                                
                        foreach($aShippingRecord as $keyProduct => $aProductRecord)
                        {
                            foreach($aProductRecord as $keyCardStock => $aCardStockRecord)
                            {
                                $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                                $sShippingName = $sProductProp['ShippingMethods'][$keyShipment];
                                $sProductName =  $sProductProp['Product'];
                            
                                echo "$sDateStamp [$sUser]: Mailing: Records per product $sProductName and shipment method $sShippingName: ".count($aProductRecord)."\n";
                                $bFileWriting1; 

                                // $maxRec = 1000;
                                $numSplits = 0;
                                $recordsDone = 0;
                                $fpm = null;
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
                                                fclose($fpm);
                                            ++$numSplits;
                                            $sDataPrepOutputFile =  $outputDir.$sCustomerName."_MAILMERGE_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_";
                                            if($neededSplits > 0)
                                                $sDataPrepOutputFile = $sDataPrepOutputFile.$numSplits."_of_".$neededSplits."_";
                                            $sDataPrepOutputFile = $sDataPrepOutputFile.$sFileName;
                                            echo "$sDateStamp [$sUser]: Writing Mail Input file to $sDataPrepOutputFile \n";
                                            $fpm = fopen($sDataPrepOutputFile, "w");
                                            fwrite($fpm, $aMailMergeShippingOutputDataHeader);

                                            //fwrite($fpm, implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1"))).fwrite($fpm, "\r\n");
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
                } 
            }

            //MAILMERGE BAD DATA
            if(!empty($aMailMergeShippingOutputBadData))
            {
                echo"BAAD DATA";
                $outputDir = $sMailMergeBadDataOutputDir;
                foreach($aMailMergeShippingOutputBadData as $sBIN => $Data)    
                {
                    foreach($Data as $keyShipment => $aShippingRecord)     
                    {
                                    
                        foreach($aShippingRecord as $keyProduct => $aProductRecord)
                        {
                            foreach($aProductRecord as $keyCardStock => $aCardStockRecord)
                            {

                                $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                                $sShippingName = $sProductProp['ShippingMethods'][$keyShipment];
                                $sProductName =  $sProductProp['Product'];

                                echo "$sDateStamp [$sUser]: Mailing: Records per product $sProductName and shipment method $sShippingName: ".count($aProductRecord)."\n";
                                $bFileWriting1; 

                                //$maxRec = 500;
                                $numSplits = 0;
                                $recordsDone = 0;
                                $fpm = null;
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
                                            fclose($fpm);
                                        ++$numSplits;
                                        $sDataPrepOutputFile =  $outputDir.$sCustomerName."MAILMERGE_BAD_DATA_".$BATCH_LOG_ID."_".$sShippingName."_".$sProductName."_";
                                        if($neededSplits > 0)
                                            $sDataPrepOutputFile = $sDataPrepOutputFile.$numSplits."_of_".$neededSplits."_";
                                        $sDataPrepOutputFile = $sDataPrepOutputFile.$sFileName;
                                        echo "$sDateStamp [$sUser]: Writing Mail Input file to $sDataPrepOutputFile \n";
                                        $fpm = fopen($sDataPrepOutputFile, "w");
                                        fwrite($fpm, $aMailMergeShippingOutputDataHeader);

                                        //fwrite($fpm, implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1"))).fwrite($fpm, "\r\n");
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
                }     
            }
        }


    if(preg_match('/CONFIRMATION_REPORT/', strtoupper($sProcessingOptions)))
        {

        echo "$sDateStamp [$sUser]: \n\n CONFIRMATION REPORT START \n\n";
        $sConfirmationReportOutputFile = $sConfirmationReportDir.(preg_replace("/(\.).*/","",$FileName)).".conf_rep.csv";
        $fp = fopen($sConfirmationReportOutputFile,"w");
        fwrite($fp, implode(",",array("Token", "FileName", "ShipSuffix","BIN","Status", "ErrorCode", "ErrorDescription", "DataReceived","CardType", "PAN4","san2","Name1","Name2","Address1","Address2","City","State","ZipCode","ExpDate"))).fwrite($fp, "\r\n");

        foreach($aConfirmationReportOutputData as $row)
        {
        
            //$bFileWriting1 =fwrite($fp, implode(",",$row)).fwrite($fp, "\r\n");
            $bFileWriting1 =fputcsv($fp, $row);
            $aFilesWritingStatus[] = $bFileWriting1;
        }
            if($bFileWriting1)
            {
                echo "$sDateStamp [$sUser]: Report File for batch #: $BATCH_LOG_ID succesfully written as: $sConfirmationReportOutputFile\n";
                fclose($fp);

            }
            else 
            {
                echo "$sDateStamp [$sUser]: Writing Report file for batch $BATCH_LOG_ID failed\n";
                fclose($fp);
            
            }
        }
    
 
        if(preg_match('/SHIPMENT_REPORT/', strtoupper($sProcessingOptions)))
        {

                echo "$sDateStamp [$sUser]: \n\n SHIPMENT REPORT START \n\n";
                $sShipmentReportOutputFile = $sShipmentReportDir.(preg_replace("/(\.).*/","",$FileName)).".ship_rep_not_processed.csv";
                $fp = fopen($sShipmentReportOutputFile,"w");
                fwrite($fp, $aShipmentReportOutputDataHeader);

                foreach($aShipmentReportOutputData as $row)
                {

                    $bFileWriting1 =fputcsv($fp, $row);
                    //$bFileWriting1 =fwrite($fp, implode(",",$row)).fwrite($fp, "\r\n");
                    $aFilesWritingStatus[] = $bFileWriting1;
                }
                    if($bFileWriting1)
                    {
                        echo "$sDateStamp [$sUser]: Report File for batch #: $BATCH_LOG_ID succesfully written as: $sShipmentReportOutputFile\n";
                        fclose($fp);

                    }
                    else 
                    {
                        echo "$sDateStamp [$sUser]: Writing Report file for batch $BATCH_LOG_ID failed\n";
                        fclose($fp);
                    
                    }
            }

            return $aMailShippingOutputData;
 }



 



// function prepareShipmentReport()

// function prepareConfirmationReport()

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
        $sBIN = substr(preg_replace("/\s+/","",($aInputFile[$i][2])),0,6);
        $sBINExtended = substr(preg_replace("/\s+/","",($aInputFile[$i][2])),0,8);
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

    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
    global $aBINs;

    global $iNoErrorRecs;
    $iTotalNumberOfRecords=0;
    //$aInputData = array_slice($aInputData,0,1);
    //print_r($aInputData);
    foreach($aInputData as $sBIN => $Data)
    {
        foreach($Data as $keyShip => $aShipRecords)
        { 
            foreach($aShipRecords as $keyProd => $aProdRecords)
            {
                foreach($aProdRecords as $keyCardID => $aCardStocks)
                {
                    foreach($aCardStocks as $keyCardStock => $aRecord)
                    {
                        
                        $aCollectShipment[$sBIN][$keyProd][$keyCardID][] = $keyShip;
                    }
                }
            }
        }
    }
    //array_flip($aCollectShipment);
    
    //$aShipmentServicesPerProduct = array_count_values($aCollectShipment);
    
    
    echo "\n\t Detail Summary of records in file Shipment Method and per Product for customer $sCustomerName: \n";
    printf('            %-10s| %-20s| %-20s| %-20s|%-10s ','BIN' , 'ProductID-Name', 'CardStockID','ShipmentMethodID-Name', 'Total Number of Records');
    echo"\n";

    foreach($aCollectShipment as $sBIN => $Data)
    {
        foreach($Data as $keyShipPerProduct => $aProducts)
        {
            foreach($aProducts as $keyCardStock => $keyCardStocks)
            {
                //print_r($aProducts);
                $aShipmentServices = array_count_values($keyCardStocks);
                $iSubTotalNumberOfRecords = 0;
                //print_r( $aShipmentServices);
                foreach($aShipmentServices as $keyShipService  => $iTotalNoPerService)
                {
                    //print_r($aBINs);
                    //echo("BIN: ".$sBIN);
                    //echo($aBINs[$sBIN][$keyShipPerProduct]);
                // echo"\nTOTAL: ";
                // echo"$iTotalNoPerService";
                    $sProductAlias = $aBINs[$sBIN][$keyShipPerProduct][$keyCardStock]['Product'];
                    $sShipmentAlias= $aBINs[$sBIN][$keyShipPerProduct][$keyCardStock]['ShippingMethods'][$keyShipService];
                    $sShipmentMethodType = $keyShipService;
                    $sProductType = $keyShipPerProduct;
                    $iTotalNumberOfRecords+=$iTotalNoPerService;
                    $iSubTotalNumberOfRecords+=$iTotalNoPerService;
                // echo"\nSUBTOTAL: $iSubTotalNumberOfRecords";
                    printf('            %-10s| %-20s| %-20s| %-20s |   %10d ',$sBIN, ($sProductType."-".$sProductAlias), $keyCardStock, ($sShipmentMethodType."-".$sShipmentAlias), $iTotalNoPerService);
                    echo"\n";
                }
                printf('           %-87s','.................................................................................................');
                echo"\n";
                printf('           %-70s  %20d', 'Subtotal Records per Product',$iSubTotalNumberOfRecords);
                echo"\n\n";
                
            }
        
            
        }
    }
    
        printf('           %-87s','-------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-44s    %-20s    %20d', 'Total Good Processed Records','',$iTotalNumberOfRecords);
        echo"\n\n";

        printf('           %-87s','-------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-44s    %-20s    %20d', 'Total Bad/Errored Records in file','',$iNoErrorRecs);
        echo"\n\n";

        printf('           %-87s','-------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-44s    %-20s    %20d', 'Total Records in file','',$iNoErrorRecs+$iTotalNumberOfRecords);
        echo"\n\n";
    
    return  $aCollectShipment;

}

function getFlexiaToken($sPRN)
{
    global $sDateStamp;
    global $sUser;
    global $sInputDir;
    global $sFlexiaFileName;
    global $sFileName;
    global $sFlexiaNoOfFiles;
    global $iCheckedFiles;

    if(empty($sFlexiaFileName))
    {
        //$bIsPRNMatch = false;
        $aFlexiaInputFiles = glob($sInputDir."FLEXIA*.csv");
        // echo("Flexia File:\n");
        // echo("input dir: $sInputDir; Batch ID: $sBatchID\n");
        // print_r($aFlexiaInputFile);
        if($aFlexiaInputFiles)
        {
            $iFlexiaNoOfFiles = count($aFlexiaInputFiles);
          
           
            if($iFlexiaNoOfFiles!=0 && $iFlexiaNoOfFiles!=$iCheckedFiles)
            {
                // echo"Input File\n";
                // print_r($aFlexiaInputFile);
                echo "$sDateStamp [$sUser]: Flexia input files in $sInputDir \n";
                foreach($aFlexiaInputFiles as $sFlexiaInputFile)
                {
                
                    echo "\t".basename($sFlexiaInputFile)." \n";
                }
                foreach($aFlexiaInputFiles as $sFlexiaInputFile)
                {
                    $aInputFile = file($sFlexiaInputFile, FILE_SKIP_EMPTY_LINES);
                    if(count($aInputFile)==0)
                    {
                        echo "\n$sDateStamp [$sUser]: ERROR: The ".$sFlexiaInputFile." does not contain any data, the file is empty.  \n";
                        return false;
                    }
                    else
                    {
                        // echo"Input File to read\n";
                        // print_r($aInputFile);
                        foreach($aInputFile as $sRecord)
                        {
                                $aRecordData = str_getcsv($sRecord,"\t");
                                $PRN_TO_MATCH = trim($aRecordData[0]);
                                $TRACK1_TOKEN = trim($aRecordData[1]);
                                if($sPRN == $PRN_TO_MATCH)
                                {
                                    echo "\n$sDateStamp [$sUser]: Flexia matching file found: ".$sFlexiaInputFile."\n for Galileo Matching file: $sFileName \n";
                                    $sFlexiaFileName = $sFlexiaInputFile;
                                    return $TRACK1_TOKEN;
                                }
                                
                        }
                        $iCheckedFiles++;
                    // echo "\n$sDateStamp [$sUser]: ERROR:  The ".$aFlexiaInputFile[0]." does not contain any matching PRNs data to assign Track 1 Token.  \n";
                    // return false;

                    }
                }
                
            }
            else
            {
                echo "\n$sDateStamp [$sUser]: ERROR: No Matching files for Flexia found\n";
            }

        }
        else
        {
            return false;
        }
    }
    else
    {
        $aInputFile = file($sFlexiaFileName, FILE_SKIP_EMPTY_LINES);
        if(count($aInputFile)==0)
        {
            echo "\n$sDateStamp [$sUser]: ERROR: The ".$sFlexiaFileName." does not contain any data, the file is empty.  \n";
            return false;
        }
        else
        {
            // echo"Input File to read\n";
            // print_r($aInputFile);
            foreach($aInputFile as $sRecord)
            {
                    $aRecordData = str_getcsv($sRecord,"\t");
                    $PRN_TO_MATCH = trim($aRecordData[0]);
                    $TRACK1_TOKEN = trim($aRecordData[1]);
                    if($sPRN == $PRN_TO_MATCH)
                    {
                        return $TRACK1_TOKEN;
                    }
            }
            echo "\n$sDateStamp [$sUser]: ERROR:  The ".$sFlexiaFileName." does not contain any matching PRNs data to assign Track 1 Token.  \n";
            return false;

        }
    }


}

function lookupZip($sUserID, $sAddr1, $sAddr2, $sCity, $sState, $sZIP, $iRecNo)
{

    $url = "http://production.shippingapis.com/ShippingAPI.dll?API=Verify&XML=<AddressValidateRequest USERID=\"".$sUserID."\"><Address ID=\"".$iRecNo."\"><Address1>".$sAddr1."</Address1><Address2>".$sAddr2."</Address2><City>".$sCity."</City><State>".$sState."</State><Zip5>".$sZIP."</Zip5><Zip4></Zip4></Address></AddressValidateRequest>";
    
    $request = curl_init($url);
    curl_setopt($request, CURLOPT_URL, $url);
    //curl_setopt($request,CURLOPT_HTTP_VERSION,CURLOPT_HTTP_VERSION_3);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($request);
    //$response = file_get_contents($url);
    curl_close($request);
}

function AtlasRequestJSON($url, $aJsData)
{
    global $sDateStamp;
    global $sUser;
    $jsData = json_encode($aJsData);
    echo("JSON Request: ".$jsData."\n");
    $jsDataBase64 = base64_encode($jsData);
    $url = "$url$jsDataBase64";
    
    $request = curl_init($url);
    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($request);
    curl_close($request);

    //$goodresponse = json_decode({"result":0,"data":"ADDDDAFTFATAADTTDTFTFTTATFDDDTDFFFAFFTTAADAFDDDTTAATDAADDFDDTFFTD","RoutingCode":"95112-2975","SerialNumber":"000005","Address":{"Address1":"APT 220","Address2":"817 N 10TH ST","City":"SAN JOSE","State":"CA","Zip5":"95112","Zip4":"2975"}});
    //$badresponse = json_decode({"result":-1,"data":"Bad address format"});
    //echo("Response: ".$response."\n");
    $aResponse = json_decode($response, true);
    //return json_decode($response);
 
    if(!isset($aResponse['result']) || empty($aResponse))
    {
        echo "$sDateStamp [$sUser]: ERROR: No response back or response is empty.\n";
        return array('result'=>'-1',
                     'data'=>'No response back or response is empty.');
    }
    else
    {
        return $aResponse; 
    }
}

function AtlasRequestBase64($url, $data)
{
    global $sDateStamp;
    global $sUser;
    
    $dataBase64 = base64_encode($data);
    $url = "$url$dataBase64";
    $request = curl_init($url);
    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($request, CURLOPT_TIMEOUT,30);
    
    
    $response = curl_exec($request);
    curl_close($request);
    //$goodresponse = json_decode({"result":0,"data":"ADDDDAFTFATAADTTDTFTFTTATFDDDTDFFFAFFTTAADAFDDDTTAATDAADDFDDTFFTD","RoutingCode":"95112-2975","SerialNumber":"000005","Address":{"Address1":"APT 220","Address2":"817 N 10TH ST","City":"SAN JOSE","State":"CA","Zip5":"95112","Zip4":"2975"}});
    //$badresponse = json_decode({"result":-1,"data":"Bad address format"});
    //$aResponse = json_decode($response, true);
    $aResponse = $response;
    
    if(empty($aResponse))
    {
        echo "$sDateStamp [$sUser]: ERROR: No response back or response is empty.\n";
        return array('result'=>'-1',
                     'data'=>'No response back or response is empty.');
    }
    else
    {  
        return array('result'=>'0',
                    'data'=>$aResponse); 
    }
}

function AtlasRequestStd($url, $data)
{
    global $sDateStamp;
    global $sUser;
    
    $url = "$url$data";
    $request = curl_init($url);
    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($request, CURLOPT_TIMEOUT,30);
    
    
    $response = curl_exec($request);
    curl_close($request);
    //$goodresponse = json_decode({"result":0,"data":"ADDDDAFTFATAADTTDTFTFTTATFDDDTDFFFAFFTTAADAFDDDTTAATDAADDFDDTFFTD","RoutingCode":"95112-2975","SerialNumber":"000005","Address":{"Address1":"APT 220","Address2":"817 N 10TH ST","City":"SAN JOSE","State":"CA","Zip5":"95112","Zip4":"2975"}});
    //$badresponse = json_decode({"result":-1,"data":"Bad address format"});
    $aResponse = json_decode($response, true);
    //$aResponse = $response;
    
    if(empty($aResponse))
    {
        echo "$sDateStamp [$sUser]: ERROR: No response back or response is empty.\n";
        return array('result'=>'-1',
                     'data'=>'No response back or response is empty.');
    }
    else
    {  
        return $aResponse; 
    }
}

function progressBar($done, $total) {
    
 
    $perc = floor(($done / $total) * 100);
    $left = 100 - $perc;
    $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
    //$write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
    fwrite(STDERR, $write);
 
    
    
    
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
        $aRecordData =[];
        foreach($aInputFile as $aData)
        {
            $aRecordData = array_combine(str_getcsv($header,','),str_getcsv($aData,','));
        }   
        
        
        if(isset($aRecordData['SerialNumber']))
        {
            $SerialNumber = $aRecordData['SerialNumber'];
            if(!preg_match('/[0-9]{3,}/',$SerialNumber))
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

if($aErrors!=null)
{
   echo "\n$sDateStamp [$sUser]: ERRORS during processing, to take action is needed: \n";
   foreach($aErrors as $sErrorMessage)
   {
       echo  $sErrorMessage;
   }
}

echo "\n$sDateStamp [$sUser]: Ending Script\n";

?> 
