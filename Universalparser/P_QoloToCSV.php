<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 10/20/2020
Revision: 07/19/2022
Name: Radovan Jakus
Version: 2.2
Notes: Adding Reference1 Composite Field
******************************/

//Production Environment
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/qolo/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sBulkOutputDir = "/var/TSSS/Files/USPS/BULK/";
$sBulkFedexOutputDir =  "/var/TSSS/Files/FEDEX/BULK/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sMailMergeOutputDir = "/var/TSSS/Files/MAILMERGE/";
$sFedexOutputDir = "/var/TSSS/Files/FEDEX/";
$sProcessedDir =  "/home/erutberg/Radovan/DataPrep/processed/qolo/";
$sProductConfigFile = "/home/erutberg/Radovan/Products_Configuration_Qolo.csv";
$sConfirmationReportDir = "/var/TSSS/Files/Reports/qolo/";
$sShipmentReportDir = "/var/TSSS/Files/Reports/galileo/waiting/";
$SerialNumberLocal ="/home/erutberg/Radovan/SerialNumberCounter.csv";
$sSerialNumberurl = "https://atlas.tagsystems.net/barcode/serial/";
$sCompositeFieldReference1Dir = "/home/erutberg/Radovan/Reference1.php";

//Test Environment
// $sInputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\in\\";
// $sOutputDir = "D:\\Production Data\\in\\";
// $sBulkOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\out\\USPS\\BULK\\";
// $sBulkFedexOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\out\\FEDEX\\BULK\\";
// $sMailOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\out\\USPS\\";
// $sMailMergeOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\out\\MAILMERGE\\";
// $sFedexOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\out\\FEDEX\\";
// $sProcessedDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\in\\";
// $sProductConfigFile = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\Products_Configuration_Qolo.csv";
// $sConfirmationReportDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\out\\REPORTS\\";
// $sShipmentReportDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\out\\REPORTS\\";
// $SerialNumberLocal ="D:\\Workspace\\TagSystem\\Parser_Plugin\\Qolo\\SerialNumberCounter.csv";
// $sSerialNumberurl = "https://atlas.tagsystems.net/barcode/serial/";
// $sCompositeFieldReference1Dir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Reference1.php";


$BATCH_LOG_ID;
$sBIN;

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
                        "ServiceType"=>$aProducts[11],
                        "PackageType"=>$aProducts[12],
                        "WeightOz"=>$aProducts[13],
                        "ShipDate"=>$aProducts[14],
                        "FromFullName"=>$aProducts[15],
                        "FromAddress1"=>$aProducts[16],
                        "FromAddress2"=>$aProducts[17],
                        "FromCity"=>$aProducts[18],
                        "FromState"=>$aProducts[19],
                        "FromCountry"=>$aProducts[20],
                        "FromZIPCode"=>$aProducts[21], 
                        "BulkCompanyName" => $aProducts[22], 
                        "BulkFullName" => $aProducts[23], 
                        "BulkAddress1" => $aProducts[24], 
                        "BulkAddress2" => $aProducts[25], 
                        "BulkCity" => $aProducts[26], 
                        "BulkState" => $aProducts[27], 
                        "BulkCountry" => $aProducts[28], 
                        "BulkZIPCode" => $aProducts[29], 
                        "FEDxAccount" => $aProducts[30],
                        "FEDEXPhoneNumber" => $aProducts[31],
                        "ShippingMethods" => array ('STDMAIL'=> $aProducts[7],
                                                    'TRACKMAIL'=> $aProducts[8],
                                                    'EXPRESS2DAY'=> $aProducts[9],
                                                    'EXPRESSOVERNIGHT'=> $aProducts[10]),
                        "ShippingMethodsBulk" =>  array ('STDMAIL'=> $aProducts[32],
                                                         'TRACKMAIL'=> $aProducts[33],
                                                         'EXPRESS2DAY'=> $aProducts[34],
                                                         'EXPRESSOVERNIGHT'=> $aProducts[35],)
                    );
                }
        }   
        return $aBINs;
}

//  echo"aBINs:\n";
//  print_r($aBINs);


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
 $aInputFiles = glob($sInputDir."*txt", GLOB_NOSORT);
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
                           // print_r($bDataProcessed);
                            getDetailOverview($bDataProcessed);
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

function Parser($inputDir)
{
    global $sDateStamp;
    global $sUser;
    global $sCustomerName; 
    

    $sProcessedFilename = basename($inputDir);
    $sFileName = basename($inputDir).".csv";
    $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);
   
  
   
    
    /********************************************************
     *  LENGTHS OF DATA
     ********************************************************/

    $aFILE_HEADER_RECORD = array(
        "RECORD_TYPE" => 2,
        "FILE_FORMAT_VERSION" => 3,
        "FILE_CREATION_DATE" => 8,
        "CLIENT_FILE_IDENTIFIER"=>8,
        "CLIENT_FILE_BATCH_NO"=>10
    );

    $aORDER_HEADER = array(
        "RECORD_TYPE_H2" => 2,
        "CLIENT_ORDER_ID_H2" => 30,
    );
    
    
    $aLINE_ITEM_HEADER = array(
        "RECORD_TYPE_H3" => 2,
        "CLIENT_ORDER_ID_H3" => 30,
        "SHIPPING_METHOD" => 6,
        "PRODUCT_ID"=> 10,
        "FILLER1" => 20,
        "FILLER2" => 20,
        "IMAGE_URL" => 200,
        "CARD_ID" => 10,
        "CARRIER_ID" => 10,
        "SHIPPING_SERVICE" => 10,
        "SIGNATURE_REQIRED" => 1,
        "RETURN_ADDRESS_FNAME"=>100,
        "RETURN_ADDRESS_LNAME"=>100,
        "RETURN_ADDRESS_1"=>100,
        "RETURN_ADDRESS_2"=>100,
        "RETURN_ADDRESS_CITY" => 100,
        "RETURN_ADDRESS_STATE" => 50,
        "RETURN_ADDRESS_ZIP"=>25,
        "RETURN_ADDRESS_COUNTRY"=>50,
        "BULK_ADDRESS_FNAME"=>100,
        "BULK_ADDRESS_LNAME"=>100,
        "BULK_ADDRESS_1"=>100,
        "BULK_ADDRESS_2"=>100,
        "BULK_ADDRESS_CITY"=>100,
        "BULK_ADDRESS_STATE"=>50,
        "BULK_ADDRESS_ZIP"=>25,
        "BULK_ADDRESS_COUNTRY"=>50,
        "BULK_ADDRESS_PHONE"=>20,
        "BULK_ADDRESS_COMPANY"=>50,
        "GROUP_ID"=>10,
        "CARRIER_IMAGE_1"=>50,
        "CARRIER_IMAGE_2"=>50,
        "INSERT_ID_1"=>10,
        "INSERT_ID_2"=>10,
        "INSERT_ID_3"=>10,
        "INSERT_ID_4"=>10,
        "INSERT_ID_5"=>10,
        "INSERT_ID_6"=>10



    );
    

    $aDETAIL_RECORDS = array (
        "RECORD_TYPE" => 2,
        "CARD_TOKEN"=>20,
        "CLIENT_CARD_SEQUENCE_NO"=>10,
        "PAN" => 16,
        "EXPIRATION_DATE" => 8,
        "VALID_FROM_DATE"=>8,
        "MEMBER_SINCE_DATE"=>8,
        "EMBOSS_LINE_3" => 26,
        "EMBOSS_LINE_4" => 26,
        "MAIL_TO_FNAME" =>100,
        "MAIL_TO_LNAME" => 100,
        "MAIL_TO_ADDRESS_1" => 100,
        "MAIL_TO_ADDRESS_2" => 100,
        "MAIL_TO_CITY" => 100,
        "MAIL_TO_STATE" => 50,
        "MAIL_TO_ZIP" => 25,
        "MAIL_TO_COUNTRY" => 50,
        "CARD_VALUE"=> 10,
        "TRACK_1" => 80,
        "TRACK_2" => 40,
        "CVV1" => 3,
        "CVV2" => 3,
        "CSC" => 4,
        "SERVICE_CODE"=>3,
        "DD_TRACK_1"=>8,
        "PIN"=>10,
        "PIN_OFFSET"=>12,
        "CARRIER_MESSAGE_1" => 100,
        "CARRIER_MESSAGE_2" => 100,
        "CARRIER_MESSAGE_3" => 100,
        "CARRIER_MESSAGE_4" => 100,
        "CARRIER_MESSAGE_5" => 100,
        "CARRIER_MESSAGE_6" => 100,
        "CARRIER_MESSAGE_7" => 100,
        "CARRIER_MESSAGE_8" => 500,
        "TRACK_1_NAME"=>26,
        "TRACK_2_CHIP"=>108,
        "ICVV"=>16,
        "PIN_BLOCK"=>32,
        "PIN_MAILER_FLAG"=>1,
        "IVCVC3_TRACK_1"=>4  
    );

    $aFILE_FOOTER_RECORD = array(
        "RECORD_TYPE" => 2,
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
    foreach($aInputFile as $iIndex => $sRecord){
        
        $sBOM = pack('H*','EFBBBF');        
        $sRecord = preg_replace("/^$sBOM/", '', $sRecord);

        switch(substr($sRecord,0,2))
            {
                case "H1":
                    $iFileHeaderNo++;
                    $iPos = 0;
                    foreach($aFILE_HEADER_RECORD as $sKey => $iLength)
                    {
                       $aFileRecords["FILE_HEADER"][$sKey] =  substr($sRecord, $iPos, $iLength);
                        $iPos+=$iLength;
                    } 
                    break;
              case "H2":
                    $iBatchHeaderNo++; 
                    $aBatchesIndex[] =$iIndex;
                    $iPos = 0;
                    //$iBatchId = trim(substr($sRecord,22,10));
                    foreach($aORDER_HEADER as $sKey => $iLength)
                    {
                        $aFileRecords["FILE_HEADER"]["BATCH_HEADER"][$iBatchHeaderNo][$sKey] = substr($sRecord, $iPos, $iLength);
                         $iPos+=$iLength;

                    } 
                    break;
                case "H3":
                        //$iBatchHeaderNo++; 
                        $aBatchesIndex[] =$iIndex;
                        $iPos = 0;
                        $iBatchId = trim(substr($sRecord,2,30));
                        foreach($aLINE_ITEM_HEADER as $sKey => $iLength)
                        {
                            $aFileRecords["FILE_HEADER"]["BATCH_HEADER"][$iBatchHeaderNo][$sKey] = substr($sRecord, $iPos, $iLength);
                             $iPos+=$iLength;
    
                        } 
                        break;
                case "D4":
                    $iDetailRecordNo++;
                    $iPos = 0;
                    foreach($aDETAIL_RECORDS as $sKey => $iLength)
                    {
                        
                        $aFileRecords["FILE_HEADER"]["BATCH_HEADER"][$iBatchHeaderNo]["DETAIL_RECORD"][$iRecordNo][$sKey] = substr($sRecord, $iPos, $iLength);
                        $iPos+=$iLength;
                    }                    
                    $iRecordNo++;
                    break;                  
                case "T1":
                    $iFileFooterNo++;
                    $iPos = 0;
                    foreach($aFILE_FOOTER_RECORD as $sKey => $iLength)
                    {
                     
                        $aFileRecords["FILE_FOOTER"][$sKey] = substr($sRecord, $iPos, $iLength);
                        $iPos+=$iLength;
                    } 
                    break;
                    
            
                }
            }

  // echo("PARSING:");
  //print_r($aFileRecords);
    return $aFileRecords;
    
}

function ConfirmationReportErrorCheck($input, $inputDir)
{
    $sDateStamp = date('Y-m-d H:i:s', time());
    $sUser = get_current_user(); 
    $aConfirmationReportOutputData = array();    
    global $bIsExtendedBINused;
    global $aBINs;
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
   
   // print_r($input);
    foreach($input['FILE_HEADER']['BATCH_HEADER'] as $BatchID => $aBatch)
    {
        $iRecordNo = 0;
        $iNoErrorRecsPerBatch = 0;
        //VALIDATION DATA
        $PRODUCT_ID = trim($aBatch['PRODUCT_ID']);
        $CARD_STOCK_ID = "NA";
        $BATCH_LOG_ID = $BatchID;        
        //$SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD']).'_'.trim($aBatch['SHIPPING_SERVICE']);
        $iNumberOfRecords +=count($aBatch['DETAIL_RECORD']);
        $iNumberOfRecordsPerBatch = count($aBatch['DETAIL_RECORD']);
        $SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD']);
        $SHIPPING_SERVICE = trim($aBatch['SHIPPING_SERVICE']);
        $SHIPPING_METHOD = strtoupper($SHIPPING_METHOD);
        $SHIPPING_SERVICE = strtoupper($SHIPPING_SERVICE);

        foreach($aBatch['DETAIL_RECORD'] as $DetailRecId => $aRecord)
        {
            $iRecordNo++;
            //VALIDATION DATA
            $sPAN = trim($aRecord['PAN']);
            $sBIN = substr($sPAN,0,6);
            $sBINExtended = substr($sPAN,0,8);
            $PAN4 =  substr($sPAN,-4);

            if(isset($aBINs[$sBINExtended]))
            {
                $sBIN = $sBINExtended;
                $bIsExtendedBINused = true;
            }
            $sTrack1 = trim($aRecord['TRACK_1']);
            $sTrack2 = trim(substr($aRecord['TRACK_2'],1,strlen($aRecord['TRACK_2'])-2));
            $sTrack2Chip = trim($aRecord['TRACK_2']);
            $iCVV = trim($aRecord['ICVV']);
            $CVV2 = trim($aRecord['CVV2']);
            $sEmbName = trim($aRecord['EMBOSS_LINE_3']);
            $sCompanyName = "\"".trim($aRecord['EMBOSS_LINE_4'])."\"";
            $FullName = trim($aRecord['MAIL_TO_FNAME'])." ". trim($aRecord['MAIL_TO_LNAME']);
            $Company = "";
            $Address1 = trim($aRecord['MAIL_TO_ADDRESS_1']);
            $Address2 = trim($aRecord['MAIL_TO_ADDRESS_2']);
            $City = trim($aRecord['MAIL_TO_CITY']);
            $State =  trim($aRecord['MAIL_TO_STATE']); 
            $SHIP_ZIP = trim($aRecord['MAIL_TO_ZIP']);
            $ZIPCode = substr(trim($SHIP_ZIP), 0,5);
            $ZIPCodeAddOn = empty(substr($SHIP_ZIP,5)) ? "" : preg_replace("/-/","",substr(trim($SHIP_ZIP),5));
            $Country = trim($aRecord['MAIL_TO_COUNTRY']);
            $Country = convertCountry('alpha3',$Country,'alpha2');

            $Reference3 = $aRecord['CARD_TOKEN'];


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
                if(isset($aBINs[$sBIN][trim($PRODUCT_ID)]))
                {
                    //ERROR CHECK TO CONFIRM CARD STOCK
                    if(isset($aBINs[$sBIN][trim($PRODUCT_ID)][trim($CARD_STOCK_ID)]))
                    {
                        $ProductProp = $aBINs[$sBIN][trim($PRODUCT_ID)][trim($CARD_STOCK_ID)];
                        $ProductID = trim($PRODUCT_ID);
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
                                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BatchID record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , the shipping method $SHIPPING_METHOD and it's shipping service $SHIPPING_SERVICE is invalid. The valid shipping service for product ".$ProductProp['Product']." and shipment method 00001 Standard Shipment are 00-".$ProductProp['ShippingMethods']['00'].", 11-".$ProductProp['ShippingMethods']['11'].", 12-".$ProductProp['ShippingMethods']['12'].", 13-".$ProductProp['ShippingMethods']['13'].", and for 00002 Bulk Shipment are 10-".$ProductProp['ShippingMethodsBulk']['10'].", 11-".$ProductProp['ShippingMethodsBulk']['11'].", 12-".$ProductProp['ShippingMethodsBulk']['12'].", 13-".$ProductProp['ShippingMethodsBulk']['10'].", each is configured in products_configuration_Marqeta.csv  \n";
                                $aErrors[] = $sError;
                                echo $sError;
                                $Status = "NOK";
                                $ErrorCode = "307";
                                $ErrorDescription = "Wrong Shipping Method";
                                $bHasError= true;
                                $ProductProp['Product'] = "NOK";
                                $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                $ProductID = trim($PRODUCT_ID);
                            }
                        }
                        else if(preg_match('/BULK/',$SHIPPING_METHOD))
                        {
                            if(!isset($ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE]))
                            {
                                $iErrorsPerRecord++;
                                $sError = "";
                                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BatchID  record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , the shipping method $SHIPPING_METHOD and it's shipping service $SHIPPING_SERVICE is invalid. The valid shipping service for product ".$ProductProp['Product']." and shipment method 00001 Standard Shipment are 00-".$ProductProp['ShippingMethods']['00'].", 11-".$ProductProp['ShippingMethods']['11'].", 12-".$ProductProp['ShippingMethods']['12'].", 13-".$ProductProp['ShippingMethods']['13'].", and for 00002 Bulk Shipment are 10-".$ProductProp['ShippingMethodsBulk']['10'].", 11-".$ProductProp['ShippingMethodsBulk']['11'].", 12-".$ProductProp['ShippingMethodsBulk']['12'].", 13-".$ProductProp['ShippingMethodsBulk']['10'].", each is configured in products_configuration_Marqeta.csv  \n";
                                $aErrors[] = $sError;
                                echo $sError;
                                $Status = "NOK";
                                $ErrorCode = "307";
                                $ErrorDescription = "Wrong Shipping Service";
                                $bHasError= true;
                                $ProductProp['Product'] = "NOK";
                                $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                $ProductID = trim($PRODUCT_ID);
                            }
                        }
                        else
                        {
                            $iErrorsPerRecord++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BatchID  record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , the shipping method $SHIPPING_METHOD is invalid. Valid options are 00001 for Standard Shipment and 00002 for Bulk Shipment. \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK";
                            $ErrorCode = "307";
                            $ErrorDescription = "Wrong Shipping Method";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($PRODUCT_ID);

                        }
            
                        //DATA VALIDATION
                        if(!preg_match('/%?B\d{1,19}\^[-\w\s\/]{2,26}\^([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\??/',$sTrack1))
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
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BatchID  record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , Track1 data have incorrect magnetic stripe format, received value: ".$sMaskedTrack1." \n";
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
                        $iNoErrorRecsPerBatch++;
                        $sError = "";
                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BatchID  record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN $sMaskedPAN ,the card stock ID: ".trim($CARD_STOCK_ID)." is not defined in Products_configuration.csv. Please, review products configuration. \n";
                        $aErrors[] = $sError;
                        echo $sError;
                        $Status = "NOK";
                        $ErrorCode = "303";
                        $ErrorDescription = "The card stock ID from the file: ".$sEmbName.", is unknown";
                        $bHasError= true;
                        $ProductProp['Product'] = "NOK";
                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                        $ProductID = trim($PRODUCT_ID);                       
                    }

                    if($iErrorsPerRecord>0)
                    {
                        $iNoErrorRecs++;
                        $iNoErrorRecsPerBatch++;
                        $iErrorsPerRecord=0;
                    }
                }
                else
                {
                    $iNoErrorRecs++;
                    $iNoErrorRecsPerBatch++;
                    $sError = "";
                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BatchID  record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN $sMaskedPAN ,the product ID: ".trim($PRODUCT_ID)." is not defined in Products_configuration_Marqeta.csv. Please, review products configuration. \n";
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
                $iNoErrorRecsPerBatch++;
                $sError = "";
                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BatchID  record ID: $iRecordNo, for cardholder ".$sEmbName." , with PAN $sMaskedPAN the BIN: ".$sBIN." is not defined in Products_configuration.csv. Please, review products configuration.\n";
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
             
            // echo"iNumberOfRecords $iNumberOfRecords \n";
            // echo"iNoErrorRecs $iNoErrorRecs \n";

            if($iNumberOfRecordsPerBatch==$iNoErrorRecsPerBatch)
            {
                unset($input['FILE_HEADER']['BATCH_HEADER'][$BatchID]);
                echo "$sDateStamp [$sUser]: ERROR: All records in  $inputDir in Batch ID $BatchID  Batch: $BatchID contains error, therefor this batch cannot be further processed.\n";
                //return false;

            }
        
            if($bHasError)
            {
                //DO NOT WRITE RECORD TO REST OF THE FILE
                unset($input['FILE_HEADER']['BATCH_HEADER'][$BatchID]['DETAIL_RECORD'][$DetailRecId]);
                continue;
            }
            if(strlen($SerialNumber)>$SerialNumberOfDigits)
            {
                $SerialNumber = 1;
            }
    
            $input['FILE_HEADER']['BATCH_HEADER'][$BatchID]['DETAIL_RECORD'][$DetailRecId]['SerialNumber'] = str_pad($SerialNumber++,$SerialNumberOfDigits,'0',STR_PAD_LEFT);

        }
        
     
    }
    setSerialNumber($SerialNumberLocal,$SerialNumberOfDigits,$SerialNumber);
    
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
        echo "$sDateStamp [$sUser]: Writing Report file for batch $FileName failed\n";
        fclose($fp);
    }

    // echo"PRINTINPUT\n";
    // print_r($input);
    if($input['FILE_HEADER']['BATCH_HEADER']!=null)
    {
        return $input;
    }
    else
    {

        $sError = "";
        $sError = "$sDateStamp [$sUser]: ERROR: All records in  $inputDir contains error, therefor this file could not be processed.\n";
        $aErrors[] = $sError;
        echo $sError;
        return false;
    }

}


function DataPrepInput($input, $inputDir, $outputDir)
{
    global $aBINs;
    global $maxRec;
    global $sDateStamp;
    global $sUser; 
    global $BATCH_LOG_ID;
    /*DATAPREP*/
    $aDataPrepOutputData;
    $sFileName = basename($inputDir,"txt")."csv";
    $aFilesWritingStatus = [];

    $ProductID = "";
    $BATCH_LOG_ID = trim($input['FILE_HEADER']['FILE_CREATION_DATE']);
    
        // echo"RECORDS:";
        // print_r($input);

    foreach($input['FILE_HEADER']['BATCH_HEADER'] as $BatchID => $aBatch)
    {   
        //  echo"RECORD:";
        //  print_r($aRecord);
        $iRecordNo = 0;
        $ProductID = trim($aBatch['PRODUCT_ID']);
        $CARD_STOCK_ID = "NA";
        $BATCH_LOG_ID = $BatchID;        
        $SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD']);
        $SHIPPING_SERVICE = trim($aBatch['SHIPPING_SERVICE']);


        foreach($aBatch['DETAIL_RECORD'] as $DetailRecId => $aRecord)
        {

            $sPAN = trim($aRecord['PAN']);
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
            }
            else if(preg_match('/BULK/',$SHIPPING_METHOD))
            {
                $SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD'])."-".$ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
            }

            $iRecordNo++;
            $sTrack1 = trim($aRecord['TRACK_1']);
            $sTrack2 = trim(substr($aRecord['TRACK_2'],1,strlen($aRecord['TRACK_2'])-2));
            $iCVV = trim($aRecord['ICVV']);
            $CVV2 = trim($aRecord['CVV2']);
            $sEmbName = trim(strtoupper($aRecord['EMBOSS_LINE_3']));        
    
            $sBatchID = $BATCH_LOG_ID."/".$iRecordNo;
            $sUniqueNumber= sha1($sPAN);        
            $sNotUsed1 = "0000";
            $sNotUsed2 = "00";
            $sNotUsed3 = "000";
            $sDataPrepProfile = $ProductProp['Profile'];
            $sNotUsed4 = "0000000";
            $sChipData = "$sTrack1#$sTrack2#$iCVV#$CVV2#$sEmbName";
            $aDataPrepOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][]=array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData);
    
        }

    }
        
        
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
            foreach($aCardStockBatchRecord as $BatchID => $aCardStockRecord)
            {
                
                    $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                    $BATCH_LOG_ID = $BatchID;
                /* if(!(isset($sProductProp)))
                    {
                        echo"sBIN $sBIN\n";
                        echo"sBIN $keyProduct\n";
                        echo"sBIN $keyCardStock\n";
                    }*/
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

                            $sDataPrepOutputFile = $outputDir."MS_CHIPREP_".$sProductName."_".$sShippingName."_".$BATCH_LOG_ID."_";
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
return $aDataPrepOutputData;

   
}


function MailingInput($input, $inputDir, $outputDir)
{
    global $sDateStamp;
    global $sUser;
    global $aBINs;
    global $maxRec;
    global $sBulkFedexOutputDir;
    global $sFedexOutputDir;
    global $sMailOutputDir;
    global $sBulkOutputDir;
    global $sMailMergeOutputDir;
    global $sCompositeFieldReference1Dir;

    $sFileName = basename($inputDir);
    $aMailShippingOutputData = array();
    $sFileName = basename($inputDir,"txt")."csv";

    $aFilesWritingStatus = [];
    $ProductID = "";
    $bBulk = false;


            /*MAILING*/
            foreach($input['FILE_HEADER']['BATCH_HEADER'] as $BatchID => $aBatch)
            {   
                $iRecordNo = 0;
                $ProductID = trim($aBatch['PRODUCT_ID']);
                $CARD_STOCK_ID = "NA";
                $BATCH_LOG_ID = $BatchID;        
                $SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD']);
                $SHIPPING_SERVICE = trim($aBatch['SHIPPING_SERVICE']); 
                $iNumberOfRecordsPerBatch = count($aBatch['DETAIL_RECORD']);

                //MAILMERGE
                $ClientLineItemId =  trim($aBatch['CLIENT_ORDER_ID_H3']);
                $ImageURL =  trim($aBatch['IMAGE_URL']);
                $GroupID =  trim($aBatch['GROUP_ID']);
                $CarrierImage1 = trim($aBatch['CARRIER_IMAGE_1']);
                $CarrierImage2 = trim($aBatch['CARRIER_IMAGE_2']);

       

                foreach($aBatch['DETAIL_RECORD'] as $aRecord)
                {
                    $sPAN = trim($aRecord['PAN']);
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
                        $SHIPPING_METHOD =  trim($aBatch['SHIPPING_METHOD'])."-".$ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
                        $SHIPPING_METHOD_PROD = $ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
                    }

                    //Used by Reference1.php
                    $sCustomerName3 = substr($sCustomerName,0,3);
                    $sProductName = $ProductProp['Product'];
                    $sProductName3 = substr($sProductName,0,3);
                    $sSerialNumber = $aRecord['SerialNumber'];
                    $sPAN4 = substr($sPAN, -4);

                    ++$iRecordNo;
                    $FullName = trim($aRecord['MAIL_TO_FNAME'])." ". trim($aRecord['MAIL_TO_LNAME']);
                    $Company = "";
                    $Address1 = trim($aRecord['MAIL_TO_ADDRESS_1']);
                    $Address2 = trim($aRecord['MAIL_TO_ADDRESS_2']);
                    $City = trim($aRecord['MAIL_TO_CITY']);
                    $State =  trim($aRecord['MAIL_TO_STATE']);  
                    $ZIPCode = substr(trim($aRecord['MAIL_TO_ZIP']), 0,5);
                    $ZIPCodeAddOn = empty(substr($aRecord['MAIL_TO_ZIP'],5)) ? "" : preg_replace("/-/","",substr(trim($aRecord['MAIL_TO_ZIP']),5));
                    $Country = trim($aRecord['MAIL_TO_COUNTRY']);
                    $Country = convertCountry('alpha3',$Country,'alpha2');
                    $FromFullName =  empty(trim($aBatch['RETURN_ADDRESS_FNAME'])." ".trim($aBatch['RETURN_ADDRESS_LNAME'])) ? $ProductProp["FromFullName"] : trim($aBatch['RETURN_ADDRESS_FNAME'])." ".trim($aBatch['RETURN_ADDRESS_LNAME']);
                    $FromAddress1 =  empty(trim($aBatch['RETURN_ADDRESS_1'])) ?  $ProductProp["FromAddress1"] :trim($aBatch['RETURN_ADDRESS_1']);
                    $FromAddress2 =  empty(trim($aBatch['RETURN_ADDRESS_2'])) ?  $ProductProp["FromAddress2"] :trim($aBatch['RETURN_ADDRESS_2']);
                    $FromCity =  empty(trim($aBatch['RETURN_ADDRESS_CITY'])) ?  $ProductProp["FromCity"] :trim($aBatch['RETURN_ADDRESS_CITY']);
                    $FromState=   empty(trim($aBatch['RETURN_ADDRESS_STATE'])) ?  $ProductProp["FromState"] :trim($aBatch['RETURN_ADDRESS_STATE']);
                    $FromCountry =  empty(trim($aBatch['RETURN_ADDRESS_COUNTRY'])) ?  $ProductProp["FromCountry"] :trim($aBatch['RETURN_ADDRESS_COUNTRY']);
                    $FromCountry =  (strlen($FromCountry)==2) ?  : convertCountry('alpha3',$FromCountry,'alpha2');
                    $FromZIPCode =  empty(trim($aBatch['RETURN_ADDRESS_ZIP'])) ?  $ProductProp["FromZIPCode"] :trim($aBatch['RETURN_ADDRESS_ZIP']);
                    $ServiceType = $ProductProp["ServiceType"];
                    $PackageType = $ProductProp["PackageType"];
                    $WeightOz = $ProductProp["WeightOz"];
                    $ShipDate = $ProductProp["ShipDate"];
                    $ImageType = "Pdf";
                    $Reference1 = include($sCompositeFieldReference1Dir);
                    //$Reference1 = substr($sCustomerName,0,3)."_".$aRecord['SerialNumber']."_".substr($sPAN, -4);
                    //$Reference1 = $sCustomerName."_".$BATCH_LOG_ID."_".$iRecordNo."_".substr($aRecord['PAN'], -4);
                    $Reference2 = strtoupper(hash("sha256",trim($aRecord['PAN']), false));
                    $Reference3 = $aRecord['CARD_TOKEN'];
                    $Reference4 = "";

                    //MAILMERGE
                 
                    $ClientCardID =  $aRecord['CARD_TOKEN'];
                    $ClientCardSequenceNumber =  $aRecord['CLIENT_CARD_SEQUENCE_NO'];
                    $CardValue = $aRecord['CARD_VALUE'];
                    $Message_1 = $aRecord['CARRIER_MESSAGE_1'];
                    $Message_2 = $aRecord['CARRIER_MESSAGE_2'];
                    $Message_3 = $aRecord['CARRIER_MESSAGE_3'];
                    $Message_4 = $aRecord['CARRIER_MESSAGE_4'];
                    $Message_5 = $aRecord['CARRIER_MESSAGE_5'];
                    $Message_6 = $aRecord['CARRIER_MESSAGE_6'];
                    $Message_7 = $aRecord['CARRIER_MESSAGE_7'];
                    $Message_8 = $aRecord['CARRIER_MESSAGE_8'];

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

                    $aMailMergeShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4,$ClientLineItemId,$ImageURL,$GroupID,$CarrierImage1,$CarrierImage2,$ClientCardID,$ClientCardSequenceNumber,$CardValue,$Message_1,$Message_2,$Message_3,$Message_4,$Message_5,$Message_6,$Message_7,$Message_8);
                    $aMailMergeShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4","ClientLineItemId","ImageURL","GroupID","CarrierImage1","CarrierImage2","ClientCardID","ClientCardSequenceNumber","CardValue","Message_1","Message_2","Message_3","Message_4","Message_5","Message_6","Message_7","Message_8","\r\n"));
                    $aMailShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4);
                    $aMailShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4","\r\n"));

                }
                //MAILING RESULT
                if(preg_match('/BULK/',$SHIPPING_METHOD))   
                {
                    $bBulk = true;
                    $BulkFullName = trim($aBatch['BULK_ADDRESS_FNAME'])." ". trim($aBatch['BULK_ADDRESS_LNAME']);
                    $BulkCompany = "";
                    $BulkAddress1 = trim($aBatch['BULK_ADDRESS_1']);
                    $BulkAddress2 = trim($aBatch['BULK_ADDRESS_2']);
                    $BulkCity = trim($aBatch['BULK_ADDRESS_CITY']);
                    $BulkState =  trim($aBatch['BULK_ADDRESS_STATE']);  
                    $BulkZIPCode = substr(trim($aBatch['BULK_ADDRESS_ZIP']), 0,5);
                    $BulkZIPCodeAddOn = empty(substr($aBatch['BULK_ADDRESS_ZIP'],5)) ? "" : preg_replace("/-/","",substr(trim($aBatch['BULK_ADDRESS_ZIP']),5));
                    $BulkCountry = (trim($aBatch['BULK_ADDRESS_COUNTRY']=="USA"))? "US":trim($aBatch['BULK_ADDRESS_COUNTRY']);
                    $BulkCountry =  (strlen($BulkCountry)==2) ?  : convertCountry('alpha3',$BulkCountry,'alpha2');
                    $Reference1Bulk = "CARDS_".$iNumberOfRecordsPerBatch;
                    $Reference2Bulk = "BATCH_".trim($aBatch['CLIENT_ORDER_ID_H3'])."CARDS_".$iNumberOfRecordsPerBatch;
                    $Reference3Bulk = "";
                    $Reference4Bulk = "";
                    $aBulkMultiShippingOutputData[$SHIPPING_METHOD_PROD][$BatchID."_".$GroupID."_".$iNumberOfRecordsPerBatch][] = array($Company, $BulkFullName, $BulkAddress1, $BulkAddress2,$BulkCity,$BulkState,$BulkZIPCode,$BulkZIPCodeAddOn, $BulkCountry, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1Bulk, $Reference2Bulk, $Reference3Bulk, $Reference4Bulk);

                }
                
    
            }

         


            echo "$sDateStamp [$sUser]: \n\n MAILING START \n\n";

            // echo "aMailShippingOutputData";
            // print_r($aMailShippingOutputData);
    

                foreach($aMailShippingOutputData as $keyShipment => $aShippingRecord) 
                {                            
                    foreach($aShippingRecord as $keyProduct => $aProductRecord)
                    {
                        foreach($aProductRecord as $keyCardStock => $aCardStockBatchRecord)
                        {
                            foreach($aCardStockBatchRecord as $BatchID => $aCardStockRecord)
                            {
                                    $BATCH_LOG_ID = $BatchID;
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
                                            $sDataPrepOutputFile =  $mailOutputDir."MAIL_".$sProductName."_".$sShippingName."_".$BATCH_LOG_ID."_";
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

                echo "$sDateStamp [$sUser]: \n\n MAILMERGE START \n\n";

            // echo "aMailShippingOutputData";
            // print_r($aMailShippingOutputData);
    

                foreach($aMailMergeShippingOutputData as $keyShipment => $aShippingRecord) 
                {                            
                    foreach($aShippingRecord as $keyProduct => $aProductRecord)
                    {
                        foreach($aProductRecord as $keyCardStock => $aCardStockBatchRecord)
                        {
                            foreach($aCardStockBatchRecord as $BatchID => $aCardStockRecord)
                            {
                                    $BATCH_LOG_ID = $BatchID;
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
                                            $sDataPrepOutputFile =  $mailOutputDir."MAILMERGE_".$sProductName."_".$sShippingName."_".$BATCH_LOG_ID."_";
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

                if($bBulk)
                {
                    //print_r($aBulkMultiShippingOutputData);
                    foreach($aBulkMultiShippingOutputData as $keyBulkShippingMethod => $aBulkShippingData)     
                    {
                        foreach($aBulkShippingData as $keyGroupID => $aGroupID)  
                        {   
        
                                $iNoOfCardsPerGroupID = explode('_',$keyGroupID)[2];
                                $Group_ID =  explode('_',$keyGroupID)[0]."_". explode('_',$keyGroupID)[1];
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
                                foreach($aGroupID as $row)
                                {
                                    $sDataToWrite =  implode("\t",$row)."\r\n"; 
                                
                                    $bFileWriting1 = fwrite($fp, $sDataToWrite);
                                    $aFilesWritingStatus[] = $bFileWriting1;
                            
                                }
                                if($bFileWriting1)
                                {
                                    echo "$sDateStamp [$sUser]: Bulk Multi Package File for batch #: $BATCH_LOG_ID succesfully written as: $sBulkMultiOutputFile\n";
                                    fclose($fp);
                                
                                }
                                else 
                                {
                                    echo "$sDateStamp [$sUser]: Writing Bulk Multi Package file for batch $BATCH_LOG_ID failed\n";
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

function getDetailOverview($aInputData){

    global $sCustomerName;
    global $aBINs;
    global $iNoErrorRecs;
    $iTotalNumberOfRecords=0;
    //$aInputData = array_slice($aInputData,0,1);
    //print_r($aInputData);
    echo "\n\t Detail Summary of records in file Shipment Method and per Product for customer $sCustomerName: \n";
    printf('            %-25s|  %-20s|  %-20s|    %-20s ', 'ProductID-Name', 'CardStockID','ShipmentMethodID-Name', 'Total Number of Records');
    echo"\n";

    //print_r($aInputData);
    foreach($aInputData as $keyShipmentMethod => $aShipRecords)
    { 
        foreach($aShipRecords as $keyPerProduct => $aProdRecords)
        {
            foreach($aProdRecords as $keyCardStock => $aCardStocks)
            {

                foreach($aCardStocks as $keyBatchID => $aRecords)
                {
                    $sBIN = substr($aRecords[0][7],2,6);
                    $sBINExtended =  substr($aRecords[0][7],2,8);

                    if(isset($aBINs[$sBINExtended]))
                    {
                        $sBIN = $sBINExtended;
                        $bIsExtendedBINused = true;
                    }
                    $sProductAlias = $aBINs[$sBIN][$keyPerProduct][$keyCardStock]['Product'];

                    $iTotalNoPerService = 0;

                   

                        $iTotalNoPerService = count($aRecords);
                        $iTotalNumberOfRecords+=count($aRecords);
                        printf('            %-25s|  %-20s|  %-20s|    %20d ',($keyPerProduct."-".$sProductAlias), $keyCardStock, $keyShipmentMethod, $iTotalNoPerService);
                        echo"\n";
                    
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

echo "$sDateStamp [$sUser]: Ending Script";

?> 
