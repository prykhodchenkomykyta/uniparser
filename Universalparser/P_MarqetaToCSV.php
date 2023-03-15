<?php 
/******************************
Author: Jean-Eric Pierre-Louis
Company: Pierre & Rady LLC
Date: 10/20/2020
Revision: 01/29/2022
Name: Radovan Jakus
Version: 3.5
Notes:  Handle UTF-8 encoding
******************************/

//Production Environment
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/marqeta/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sBulkOutputDir = "/var/TSSS/Files/USPS/BULK/";
$sBulkFedexOutputDir =  "/var/TSSS/Files/FEDEX/BULK/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sMailMergeOutputDir = "/var/TSSS/Files/USPS/";
$sFedexOutputDir = "/var/TSSS/Files/FEDEX/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/marqeta/";
$sProductConfigFile = "/home/erutberg/Radovan/Products_Configuration_Marqeta.csv";
$sConfirmationReportDir = "/var/TSSS/Files/Reports/";
$sShipmentReportDir = "/var/TSSS/Files/Reports/galileo/waiting/";
$SerialNumberLocal ="/home/erutberg/Radovan/SerialNumberCounter.csv";
$sSerialNumberurl = "https://atlas.tagsystems.net/barcode/serial/";
$sCompositeFieldReference1Dir = "/home/erutberg/Radovan/Reference1.php";

//Test Environment
// $sInputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\in\\";
// $sOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\out\\";
// $sBulkOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\out\\USPS\\BULK\\";
// $sBulkFedexOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\out\\FEDEX\\BULK\\";
// $sMailOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\out\\USPS\\";
// $sMailMergeOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\out\\MAILMERGE\\";
// $sFedexOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\out\\FEDEX\\";
// $sProcessedDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\in\\";
// $sProductConfigFile = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\Products_Configuration_Marqeta.csv";
// $sConfirmationReportDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\out\\REPORTS\\";
// $sShipmentReportDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\out\\REPORTS\\";
// $SerialNumberLocal ="D:\\Workspace\\TagSystem\\Parser_Plugin\\Marqeta_COT\\SerialNumberCounter.csv";
// $sSerialNumberurl = "https://atlas.tagsystems.net/barcode/serial/";
// $sCompositeFieldReference1Dir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Reference1.php";


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
                        "ShippingMethods" => array ('00'=> $aProducts[7],
                                                    11=> $aProducts[8],
                                                    12=> $aProducts[9],
                                                    13=> $aProducts[10]),
                        "ShippingMethodsBulk" =>  array (10=> $aProducts[32],
                                                         11=> $aProducts[33],
                                                         12=> $aProducts[34],
                                                         13=> $aProducts[35],)
                    );
                }
        }   
        return $aBINs;
}

//  echo"aBINs:\n";
//  print_r($aBINs);


ob_start();
date_default_timezone_set ("America/New_York");
$sDateStamp = date('Y-m-d H:i:s', time());
$sUser = get_current_user();

echo "$sDateStamp [$sUser]: Starting Script \n";

$aOptions  = getopt("p::n::");
$sInputFilePath;

// $bDataProcessed = false;
// $bMailProcessed = false;


if(!empty($aOptions ['p'])){
    $sInputFilePath = $aOptions ['p'];
    echo "$sDateStamp [$sUser]: Using full path option \n";
    if(file_exists($sInputFilePath))
        {
            $ParsedData = MarqetaParser($sInputFilePath);
            $CleansedData = MarqetaConfirmationReportErrorCheck($ParsedData,$sInputFilePath);
            if($CleansedData)
            {
                $bDataProcessed = MarqetaDataPrepInput($CleansedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
                $bMailProcessed = MarqetaMailingInput($CleansedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
            }
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
        $ParsedData = MarqetaParser($sInputFilePath);
        $CleansedData = MarqetaConfirmationReportErrorCheck($ParsedData,$sInputFilePath);
        if($CleansedData)
        {
            $bDataProcessed = MarqetaDataPrepInput($CleansedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
            $bMailProcessed = MarqetaMailingInput($CleansedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
        }

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

               // echo "$sDateStamp [$sUser]: Batch of the file: ".GalileoParser($sInputFilePath)[0]['BATCH_LOG_ID']." \n";
                $ParsedData = MarqetaParser($sInputFilePath);
                $CleansedData = MarqetaConfirmationReportErrorCheck($ParsedData,$sInputFilePath);
                if($CleansedData)
                {
                    $bDataProcessed = MarqetaDataPrepInput($CleansedData, $sInputFilePath, $sOutputDir, $sProcessedDir);
                    $bMailProcessed = MarqetaMailingInput($CleansedData, $sInputFilePath, $sMailOutputDir, $sProcessedDir);
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

function MarqetaParser($inputDir)
{
    global $sDateStamp;
    global $sUser;
    $sProcessedFilename = basename($inputDir);
    $sFileName = basename($inputDir)."csv";
    $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);
   
    /********************************************************
     *  LENGTHS OF DATA
     ********************************************************/

    $aFILE_HEADER_RECORD = array(
        "RECORD_TYPE" => 2,
        "CLIENT_ID" => 10,
        "FILE_CREATION_DATE" => 8,
        //"FILLER" => 2128,
       // "RECORD TERMINATION" => 2,
    );

    $aBATCH_HEADER_RECORD = array(
        "RECORD_TYPE" => 2,
        "INSTITUION_ID" => 10,
        "PACKAGE_ID" => 10,
        "BATCH_ID" => 10,
        "SERVICE_LEVEL" => 1,
        "FILLER_1" => 139,
        "SHIPPING_METHOD" => 5,
        "SHIPPING_SERVICE" => 2,
        "BULK_SHIP_NAME_1" => 100,
        "BULK_SHIP_NAME_2" => 100,
        "BULK_SHIP_ADDRESS_1" => 100,
        "BULK_SHIP_ADDRESS_2" => 100,
        "BULK_SHIP_CITY" => 50,
        "BULK_SHIP_STATE" => 20,
        "BULK_SHIP_ZIP" => 9,
        "BULK_SHIP_COUNTRY" => 50,
        "RETURN_NAME_1" => 100,
        "RETURN_NAME_2" => 100,
        "RETURN_ADDRESS_1" => 100,
        "RETURN_ADDRESS_2" => 100,
        "RETURN_CITY" => 50,
        "RETURN_STATE" => 20,
        "RETURN_ZIP" => 9,
        "RETURN_COUNTRY" => 50,
        "REQUEST_TYPE" => 5,
        "NETWORK" => 5,
        "BIN" => 10,
        "CARD_PROFILE" => 5,
        "MAX_CARDS_CARRIER" => 10,
        "TIPPING" => 10,
        "FRONT_PERSO_TYPE" => 5,
        "BACK_PERSO_TYPE" => 5,
        "BACK_THERMAL_CUSTOMER_SERVICE" => 50,
        "THERMAL_COLOR" => 25,
        "CARD_CARRIER_REFERENCE" => 10,
        "CARRIER_STOCK" => 10,
        "INSERT_ID_1" => 10,
        "INSERT_ID_2" => 10,
        "INSERT_ID_3" => 10,
        "INSERT_ID_4" => 10,
        "ENVELOPE_ID" => 10,
        "ENVELOPE_SEAL" => 10,
        "PIN_MAILER_REQUIRED" => 5,
        "PIN_MAILER_REFERENCE" => 10,
        "PIN_MAILER_TEMPLATE" => 10,
        "3RD_PARTY_FEDEX_ACCOUNT_NO" => 20,
        //"FILLER_2" => 890,
        //"RECORD_TERMINATION" => 2,
    );
    
    $aDETAIL_RECORDS = array (
        "RECORD_TYPE" => 2,
        "MAIL_TO_NAME_1" =>100,
        "MAIL_TO_NAME_2" => 100,
        "MAIL_TO_ADDRESS_1" => 100,
        "MAIL_TO_ADDRESS_2" => 100,
        "MAIL_TO_CITY" => 50,
        "MAIL_TO_STATE" => 20,
        "MAIL_TO_ZIP" => 9,
        "MAIL_TO_COUNTRY" => 50,
        "PAN" => 19,
        "CARDHOLDER_NAME" => 21,
        "MEMBER_SINCE" => 4,
        "EXPIRATION_DATE" => 9,
        "CVV2" => 3,
        "ADDITIONAL_EMBOSS_LINE_A" => 21,
        "TRACK_1" => 78,
        "TRACK_2" => 39,
        "CARD_REFERENCE_ID" => 20,
        "DENOMINATION" => 10,
        "UPC_BARCODE" => 20,
        "CARRIER_MESSAGE_1" => 60,
        "CARRIER_MESSAGE_2" => 60,
        "CARRIER_MESSAGE_3" => 250,
        "CARRIER_MESSAGE_4" => 250,
        "RETURN_NAME_1" => 100, // Pierre Change from 100
        "RETURN_NAME_2" => 100, // Pierre Change from 100
        "RETURN_ADDRESS_1" => 100,
        "RETURN_ADDRESS_2" => 100,
        "RETURN_CITY" => 50,
        "RETURN_STATE" => 20,
        "RETURN_ZIP" => 9,
        "RETURN_COUNTRY"=> 50,
        "CARD_IMAGE_NAME" => 50,
        "CARRIER_IMAGE_NAME" => 50,
        "CARRIER_MESSAGE_5" => 150,
        "CARRIER_MESSAGE_6" => 350,
        "CARRIER_MESSAGE_7" => 50,
        "MERCHANT_1_LOGO"=> 50,
        "MERCHANT_1_NAME" => 60,
        "MERCHANT_1_AMOUNT" => 10,
        "MERCHANT_2_LOGO"=> 50,
        "MERCHANT_2_NAME" => 60,
        "MERCHANT_2_AMOUNT" => 10,
        "MERCHANT_3_LOGO"=> 50,
        "MERCHANT_3_NAME" => 60,
        "MERCHANT_3_AMOUNT" => 10,
        "PACKAGE_TYPE" => 4,
        "ADDITIONAL_EMBOSS_LINE_B" => 21,
        "CVV1/ICVV" => 3,
        "CARD_TOKEN" => 36,
        "LASER_IMAGE_NAME" => 28,
        "PIN_BLOCK" => 20,
        "TITLE" => 10,
        "FREE_TEXT_1" => 100,
        "FREE_TEXT_2" => 100,
        "FREE_TEXT_3" => 100,
        "FREE_TEXT_4" => 100,
        "FREE_TEXT_5" => 100,
        "FREE_TEXT_6" => 100,
        "FREE_TEXT_7" => 100,
        "FREE_TEXT_8" => 100,
        "FREE_TEXT_9" => 100,
        "FREE_TEXT_10" => 100,
        //"RECORD_TERMINATION" => 2,     
    );

    // echo "****************{$aDETAIL_RECORDS["RETURN_NAME_1"][100]}*******************";

    $aBATCH_FOOTER_RECORD = array(
        "RECORD_TYPE" => 2,
        "TOTAL_RECORDS_IN_BATCH" => 7,
        //"FILLER" => 2139,
       // "RECORD_TERMINATION" => 2,  
    );

    $aFILE_FOOTER_RECORD = array(
        "RECORD_TYPE" => 2,
        "TOTAL_BATCHES_IN_FILE" => 7,
        "TOTAL_RECORDS_IN_FILE" => 7,
        //"FILLER" => 2132,
        //"RECORD_TERMINATION" => 2,
    );


    /********************************************************
     *  PARSING 
     ********************************************************/
    echo "$sDateStamp [$sUser]: Starting Parsing Data\n";

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
                case "FH":
                    $iFileHeaderNo++;
                    $iPos = 0;
                    foreach($aFILE_HEADER_RECORD as $sKey => $iLength)
                    {
                       $aFileRecords["FILE_HEADER"][$sKey] =  mb_substr($sRecord, $iPos, $iLength,"UTF-8");
                        $iPos+=$iLength;
                    } 
                    break;
              case "BH":
                    $iBatchHeaderNo++; 
                    $aBatchesIndex[] =$iIndex;
                    $iPos = 0;
                    $iBatchId = $iBatchHeaderNo;
                    //trim(substr($sRecord,22,10));
                    foreach($aBATCH_HEADER_RECORD as $sKey => $iLength)
                    {
                        $aFileRecords["FILE_HEADER"]["BATCH_HEADER"][$iBatchId][$sKey] = mb_substr($sRecord, $iPos, $iLength,"UTF-8");
                         $iPos+=$iLength;

                    } 
                    break;
                case "DR":
                    $iDetailRecordNo++;
                    $iPos = 0;
                    foreach($aDETAIL_RECORDS as $sKey => $iLength)
                    {
                        
                        $aFileRecords["FILE_HEADER"]["BATCH_HEADER"][$iBatchId]["DETAIL_RECORD"][$iRecordNo][$sKey] = mb_substr($sRecord, $iPos, $iLength,"UTF-8");
                        $iPos+=$iLength;
                    }                    
                    $iRecordNo++;
                    break;
                case "BF":  
                    $iBatchFooterNo++;
                    $iPos = 0;
                    foreach($aBATCH_FOOTER_RECORD as $sKey => $iLength)
                    {
                        $aFileRecords["FILE_HEADER"]["BATCH_FOOTER"][$sKey] = mb_substr($sRecord, $iPos, $iLength,"UTF-8");
                        $iPos+=$iLength;
                    } 
                    
                    $iRecordsPerBatch = $aFileRecords["FILE_HEADER"]["BATCH_FOOTER"]["TOTAL_RECORDS_IN_BATCH"];
                    if($iRecordNo == $iRecordsPerBatch)
                    {
                        echo "$sDateStamp [$sUser]: The number of total Records in Batch #: $iBatchFooterNo is $iRecordNo. It is matched against file record\n";
                    }
                    else
                    {
                        die("\n$sDateStamp [$sUser]: ERROR: The number of total Records per Batch in file is not matching number of total records that has been counted.
                        Records number per batch according to the file: $iRecordsPerBatch
                        Records counted per Batch #: $iBatchFooterNo is $iRecordNo");  
                    }
                    $iRecordNo = 0;
                    break;
                case "FF":
                    $iFileFooterNo++;
                    $iPos = 0;
                    foreach($aFILE_FOOTER_RECORD as $sKey => $iLength)
                    {
                     
                        $aFileRecords["FILE_FOOTER"][$sKey] = mb_substr($sRecord, $iPos, $iLength,"UTF-8");
                        $iPos+=$iLength;
                    } 
                    break;
                }
            }

    if($iFileHeaderNo != $iFileFooterNo)
    {
        die("\n$sDateStamp [$sUser]: ERROR: The File Header is not closed by File Footer or vice versa. File Footer or File Header is missing in input file. File Header #: $iFileHeaderNo  File Footer #: $iFileFooterNo");
    }

    if($iBatchHeaderNo != $iBatchFooterNo)
    {
        die("\n$sDateStamp [$sUser]: ERROR: The Batch Header is not closed by Batch Footer or vice versa. Batch Footer or Batch Header is missing in input file");  
    }
    

    if($iBatchHeaderNo == $aFileRecords["FILE_FOOTER"]["TOTAL_BATCHES_IN_FILE"]){
            echo "$sDateStamp [$sUser]: The number of total Batches in File: $iBatchHeaderNo. It is matched against file record\n";
        }
        else
        {
            die("\n$sDateStamp [$sUser]: ERROR: The number of total Batches per Bile is not matching number of total batches that has been counted. 
            Batches number according the file: {$aFileRecords['FILE_FOOTER']['TOTAL_BATCHES_IN_FILE']}
            Batches counted: $iBatchHeaderNo");  
        }
    if($iDetailRecordNo == $aFileRecords["FILE_FOOTER"]["TOTAL_RECORDS_IN_FILE"] ){
            echo "$sDateStamp [$sUser]: The number of total Records in File: $iDetailRecordNo. It is matched against file record\n";
        }
        else
        {
            die("\n$sDateStamp [$sUser]: ERROR: The number of total Becords in File is not matching number of total records that has been counted. 
            Records number according the file: {$aFileRecords["FILE_FOOTER"]["TOTAL_RECORDS_IN_FILE"]}
            Total records per file counted: $iDetailRecordNo");  
        }

    //echo("\nFile Records\n");
    //print_r($aFileRecords);
    return $aFileRecords;
    
}

function MarqetaConfirmationReportErrorCheck($input, $inputDir)
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
        $PRODUCT_ID = trim($aBatch['PACKAGE_ID']);
        $CARD_STOCK_ID = "NA";
        $BATCH_LOG_ID = $BatchID;        
        $SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD']).'_'.trim($aBatch['SHIPPING_SERVICE']);
        $iNumberOfRecords +=count($aBatch['DETAIL_RECORD']);
        $iNumberOfRecordsPerBatch = count($aBatch['DETAIL_RECORD']);
        $SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD']);
        $SHIPPING_SERVICE = trim($aBatch['SHIPPING_SERVICE']);

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
            $iCVV = trim($aRecord['CVV1/ICVV']);
            $CVV2 = trim($aRecord['CVV2']);
            $sEmbName = trim($aRecord['CARDHOLDER_NAME']);
            $sCompanyName = "\"".trim($aRecord['ADDITIONAL_EMBOSS_LINE_A'])."\"";
            $FullName = trim($aRecord['MAIL_TO_NAME_1']);
            $Company = "";
            $Address1 = trim($aRecord['MAIL_TO_ADDRESS_1']);
            $Address2 = trim($aRecord['MAIL_TO_ADDRESS_2']);
            $City = trim($aRecord['MAIL_TO_CITY']);
            $State =  trim($aRecord['MAIL_TO_STATE']); 
            $SHIP_ZIP = trim($aRecord['MAIL_TO_ZIP']);
            $ZIPCode = substr(trim($SHIP_ZIP), 0,5);
            $ZIPCodeAddOn = empty(substr($SHIP_ZIP,5)) ? "" : preg_replace("/-/","",substr(trim($SHIP_ZIP),5));
             $Country = trim($aRecord['MAIL_TO_COUNTRY']);
             if($Country == "United States"){
                 $Country = "US"; 
             }
             else if($Country == "USA")
             {
                $Country = "US";
             }
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
                        if($SHIPPING_METHOD=="00001")
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
                        else if($SHIPPING_METHOD=="00002")
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
                        if(!preg_match('/%?B\d{1,19}\^(?=[A-Za-z0-9 .()\/-]{2,26}\^)[A-Za-z0-9 .()-]*\/[A-Za-z0-9 .()-]*\^([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\??/',$sTrack1))
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

function MarqetaDataPrepInput($input, $inputDir, $outputDir)
{
    global $aBINs;
    global $maxRec;

    $sDateStamp = date('Y-m-d H:i:s', time());
    $sUser = get_current_user(); 
    /*DATAPREP*/
    $aDataPrepOutputData = array();
    $sFileName = basename($inputDir,"txt")."csv";
    $aFilesWritingStatus = [];
    $ProductID = "";
    // echo "InputArray";
    // print_r($input);
    foreach($input['FILE_HEADER']['BATCH_HEADER'] as $BatchID => $aBatch)
    {
        $iRecordNo = 0;
        $ProductID = trim($aBatch['PACKAGE_ID']);
        $CARD_STOCK_ID = "NA";
        $BATCH_LOG_ID = $BatchID;        
        $SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD']);
        $SHIPPING_SERVICE = trim($aBatch['SHIPPING_SERVICE']);

        foreach($aBatch['DETAIL_RECORD'] as $aRecord){

            $sPAN = trim($aRecord['PAN']);
            $sBIN = substr($sPAN,0,6);
            $sBINExtended = substr($sPAN,0,8);
            if(isset($aBINs[$sBINExtended]))
            {
                $sBIN = $sBINExtended;
            }
            $ProductProp = $aBINs[$sBIN][trim($ProductID)][trim($CARD_STOCK_ID)];
            $sCustomerName = $aBINs[$sBIN]['Customer'];
            if($SHIPPING_METHOD=="00001")
            {
                $SHIPPING_METHOD = $SHIPPING_SERVICE."-".$ProductProp['ShippingMethods'][$SHIPPING_SERVICE];
            }
            else if($SHIPPING_METHOD=="00002")
            {
                $SHIPPING_METHOD = $SHIPPING_SERVICE."-".$ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
            }

            $iRecordNo++;
            $sTrack1 = trim($aRecord['TRACK_1']);
            $sTrack2 = trim(substr($aRecord['TRACK_2'],1,strlen($aRecord['TRACK_2'])-2));
            $sTrack2Chip = trim($aRecord['TRACK_2']);
            $iCVV = trim($aRecord['CVV1/ICVV']);
            $CVV2 = trim($aRecord['CVV2']);
            $sEmbName = trim($aRecord['CARDHOLDER_NAME']);
            $sCompanyName = "\"".trim($aRecord['ADDITIONAL_EMBOSS_LINE_A'])."\"";
            $BATCH_LOG_ID = $BatchID;
            $sBatchID = $BATCH_LOG_ID."/".$iRecordNo;
  	        $sUniqueNumber= sha1(substr($sTrack2,0,16));        
            $sNotUsed1 = "0000";
            $sNotUsed2 = "00";
            $sNotUsed3 = "000";
            $sDataPrepProfile = $ProductProp['Profile'];
            $sNotUsed4 = "0000000";
            $sChipData = "$sTrack1#$sTrack2#$iCVV#$CVV2#$sEmbName#$sCompanyName";
            $aDataPrepOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][]=array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData);

            //$aDataPrepOutputData[$sCustomerName][] = array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData); 
        }
        // echo "DataPrepArray";
        // print_r($aDataPrepOutputData);

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

function MarqetaMailingInput($input, $inputDir, $outputDir)
{

    global $aBINs;
    global $maxRec;
    global $sBulkFedexOutputDir;
    global $sFedexOutputDir;
    global $sMailOutputDir;
    global $sBulkOutputDir;
    global $sCompositeFieldReference1Dir;

    $sDateStamp = date('Y-m-d H:i:s', time());
    $sUser = get_current_user();
    $aMailShippingOutputData = array();

    $sFileName = basename($inputDir,"txt")."csv";
    $bBulk = false;
    $aFilesWritingStatus = [];
    $ProductID = "";
    foreach($input['FILE_HEADER']['BATCH_HEADER'] as $BatchID => $aBatch)
    {
            $iRecordNo = 0;
            $ProductID = trim($aBatch['PACKAGE_ID']);
            $CARD_STOCK_ID = "NA";
            $BATCH_LOG_ID = $BatchID;        
            $SHIPPING_METHOD = trim($aBatch['SHIPPING_METHOD']);
            $SHIPPING_SERVICE = trim($aBatch['SHIPPING_SERVICE']);


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

                if($SHIPPING_METHOD=="00001")
                {
                    $SHIPPING_METHOD = $SHIPPING_SERVICE."-".$ProductProp['ShippingMethods'][$SHIPPING_SERVICE];
                    $SHIPPING_METHOD_PROD = $ProductProp['ShippingMethods'][$SHIPPING_SERVICE];
                }
                else if($SHIPPING_METHOD=="00002")
                {
                    $SHIPPING_METHOD = $SHIPPING_SERVICE."-".$ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
                    $SHIPPING_METHOD_PROD = $ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
                }
                
                //Used by Reference1.php
                $sCustomerName3 = substr($sCustomerName,0,3);
                $sProductName = $ProductProp['Product'];
                $sProductName3 = substr($sProductName,0,3);
                $sSerialNumber = $aRecord['SerialNumber'];
                $sPAN4 = substr($sPAN, -4);

                    ++$iRecordNo;
                $BATCH_LOG_ID = $BatchID;
	            $SHIP_ZIP = trim($aRecord['MAIL_TO_ZIP']);
                    $FullName = trim($aRecord['MAIL_TO_NAME_1']);
                    $Company = "";
                    $Address1 = trim($aRecord['MAIL_TO_ADDRESS_1']);
                    $Address2 = trim($aRecord['MAIL_TO_ADDRESS_2']);
                    $City = trim($aRecord['MAIL_TO_CITY']);
                    $State =  trim($aRecord['MAIL_TO_STATE']); 
       		    $ZIPCode = substr(trim($SHIP_ZIP), 0,5);
           	    $ZIPCodeAddOn = empty(substr($SHIP_ZIP,5)) ? "" : preg_replace("/-/","",substr(trim($SHIP_ZIP),5));
                    $Country = trim($aRecord['MAIL_TO_COUNTRY']);
                    if($Country == "United States"){
                        $Country = "US"; 
                    }
                    else if($Country == "USA")
                    {
                       $Country = "US";
                    }
                    $FromFullName =  $ProductProp["FromFullName"];
                    $FromAddress1 =  $ProductProp["FromAddress1"]; 
                    $FromAddress2 =  $ProductProp["FromAddress2"]; 
                    $FromCity = $ProductProp["FromCity"];
                    $FromState=  $ProductProp["FromState"];
                    $FromCountry =  $ProductProp["FromCountry"];
                    $FromZIPCode =  $ProductProp["FromZIPCode"];
                    //$Amount;
                    $ServiceType = $ProductProp["ServiceType"];
                    $PackageType = $ProductProp["PackageType"];
                    $WeightOz = $ProductProp["WeightOz"];
                    $ShipDate = $ProductProp["ShipDate"];
                    $ImageType = "Pdf";
                    $Reference1 = include($sCompositeFieldReference1Dir);
                    //$Reference1 = substr($sCustomerName,0,3)."_".$aRecord['SerialNumber']."_".preg_replace("/\s+/","_",trim($FullName))."_".substr($sPAN, -4);
                    $Reference2 = strtoupper(hash("sha256",trim($aRecord['PAN']), false));
                    $Reference3 = $aRecord['CARD_TOKEN'];
                    $Reference4 = "";

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
        
                    //MAILING RESULT
                    $aMailShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4);
                    $aMailShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4"."\r\n"));

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
                return $aMailShippingOutputData;

}




function findCustomer($sBIN, $aBINs)
{
    $bCustomerFound = false;
    $i = 0;
    while(!$bCustomerFound)
    {  
        if(isset($aBINs[$sBIN]))
        {
            $bCustomerFound = true;
            return $sBIN;
        }
        else
        {
            return false;
        }
        
    }
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









if($aErrors!=null)
{
   echo "\n$sDateStamp [$sUser]: ERRORS during processing, to take action is needed: \n";
   foreach($aErrors as $sErrorMessage)
   {
       echo  $sErrorMessage;
   }
}



echo "$sDateStamp [$sUser]: Ending Script";

?> 
