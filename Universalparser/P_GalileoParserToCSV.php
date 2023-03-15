<?php

/******************************
Author: Jean-Eric Pierre-Louis
Company: Pierre & Rady LLC
Date: 10/20/2020
Revision: 07/19/2022
Name: Radovan Jakus
Version: 3.20
Notes: Adding Reference1 Composite Field
******************************/

//Production Environment
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/galileo/";
$sOutputDir = "/var/TSSS/DataPrep/in/";
$sBulkOutputDir = "/var/TSSS/Files/USPS/BULK/";
$sBulkFedexOutputDir =  "/var/TSSS/Files/FEDEX/BULK/";
$sMailOutputDir = "/var/TSSS/Files/USPS/";
$sMailMergeOutputDir = "/var/TSSS/Files/MAILMERGE/";
$sFedexOutputDir = "/var/TSSS/Files/FEDEX/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processsed/galileo/";
$sProductConfigFile = "/home/erutberg/Radovan/Products_Configuration.csv";
$sConfirmationReportDir = "/var/TSSS/Files/Reports/galileo/";
$sShipmentReportDir = "/var/TSSS/Files/Reports/galileo/waiting/";
$sMeshImagesDir = "/var/TSSS/Files/Logos/";
$sMachineImagesDir = "/var/TSSS/Files/Logos/";
$sSerialNumberurl = "https://atlas.tagsystems.net/barcode/serial/";
$SerialNumberLocal ="/home/erutberg/Radovan/SerialNumberCounter.csv";
$sCompositeFieldReference1Dir = "/home/erutberg/Radovan/Reference1.php";


// $sInputDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\in\\";
// $sOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\out\\";
// $sBulkOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\out\\MAIL\\BULK\\";
// $sBulkFedexOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\out\\FEDEX\\BULK\\";
// $sMailOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\out\\MAIL\\";
// $sMailMergeOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\out\\MAILMERGE\\";
// //$sMailOutputDir = "D:\\Production Data\\stamps\\indicium\\";
// $sFedexOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\out\\FEDEX\\";
// //$sShipsiOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\out\\";/$sShipsiOutputDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\out\\";
// $sShipsiOutputDir = "D:\\Production Data\\stamps\\indicium\\";
// $sProcessedDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\in\\"; 
// $sProductConfigFile = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Galileo_SAVE\\Products_Configuration.csv";
// $sConfirmationReportDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_Save\out\REPORT\\";
// $sShipmentReportDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_Save\out\REPORT\\SHIPMENT_REPORT\\";
// //$sOriginalFile = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\encrypted\\";
// $sMeshImagesDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_SAVE\in\\logos\\";
// $sMachineImagesDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\TagSystem_DataPrep_Output_MB_MachineInput\\Logos\\";
// $SerialNumberLocal ="D:\\Workspace\\TagSystem\\Parser_Plugin\\Galileo_SAVE\\SerialNumberCounter.csv";
// $sSerialNumberurl = "https://atlas.tagsystems.net/barcode/serial/";
// $sCompositeFieldReference1Dir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Reference1.php";


 //Mailer Information
 $BarcodeID = "00";
 $ServiceTypeID = "270";
 $MailerID = "902695246";
 $maxRec = 1000;
 $SerialNumberOfDigits = (strlen($MailerID)==9)? 6 : 9;


$sDataPrepProfile;
$sCustomerName;
$iNumberOfRecords;
$sBIN;
$aBINs = getProductsList($sProductConfigFile);


$aErrors;

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
                        "ShippingMethods" => array (1=> $aProducts[7],
                                                    2=> $aProducts[8],
                                                    3=> $aProducts[9],
                                                    4=> $aProducts[10]),
                        "ShippingMethodsBulk" =>  array (1=> $aProducts[32],
                                                         2=> $aProducts[33],
                                                         3=> $aProducts[34],)
                    );
                }
        }   
        return $aBINs;
}
// echo"aBINs:\n";
// print_r($aBINs);

ob_start();
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
            GalileoParserToCSV ($sInputFilePath, $sOutputDir, $sProcessedDir, $sBulkOutputDir, $sMailOutputDir);
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
        GalileoParserToCSV ($sInputFilePath, $sOutputDir, $sProcessedDir, $sBulkOutputDir, $sMailOutputDir);
    }
    else
    {
        die("\nERROR: The file does not exist. Check the name or path of the file. FILE:".$sInputFilePath);  
    }
}
else{
 echo "$sDateStamp [$sUser]: Using option to process files from predefined directory automatically. Directory: $sInputDir \n";
 $aInputFiles = glob("$sInputDir*.emb");
    if($aInputFiles){
            foreach($aInputFiles as $sInputFilePath){
                echo "\t".basename($sInputFilePath)." \n";
            }
            $file = 0;
            foreach($aInputFiles as $sInputFilePath){
                progressBar(++$file,count($aInputFiles));
                $bFileProcessed = GalileoParserToCSV($sInputFilePath, $sOutputDir, $sProcessedDir, $sBulkOutputDir, $sMailOutputDir); 
                if($bFileProcessed)
                {
                    $sProcessedFilename = basename($sInputFilePath);
                    $bFileMoved = rename($sInputFilePath ,$sProcessedDir.$sProcessedFilename);
                    if($sBIN == "544292")
                    {
                        $bFileMoved = rename($sFlexiaFileName , $sProcessedDir.basename($sFlexiaFileName));
                    }
                    if($bFileMoved)
                    {
                        echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sProcessedFilename \n";
                        if($sBIN == "544292")
                        {
                            echo "$sDateStamp [$sUser]: Processed Flexia File succesfully moved to: ".$sProcessedDir.basename($sFlexiaFileName)."\n";
                        }
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

function GalileoParserToCSV  ($inputDir, $outputDir, $processedDir, $bulkOutputDir, $mailOutputDir) {
    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
    global $sDataPrepProfile;
    global $aBINs;
    global $sBIN;
    global $aShippingMethods;
    global $sBulkOutputDir;
    global $sBulkFedexOutputDir;
    global $sMailOutputDir;
    global $sFedexOutputDir;
    global $sMailMergeOutputDir;
    global $iNumberOfRecords;
    //global $sOriginalFile;
    global $SerialNumberOfDigits;
    global $sConfirmationReportDir;
    global $sShipmentReportDir;
    global $aErrors;
    global $iNoErrorRecs;
    global $sFlexiaFileName;
    global $sMeshImagesDir;
    global $sMachineImagesDir;
    global $BarcodeID;
    global $ServiceTypeID;
    global $MailerID;
    global $sSerialNumberurl;
    global $SerialNumberLocal;
    global $sCompositeFieldReference1Dir;

    /*SERIAL NUMBER*/
    $SerialNumber = getSerialNumber($SerialNumberLocal,$SerialNumberOfDigits);
    $SerialNumber = str_pad($SerialNumber,$SerialNumberOfDigits,'0',STR_PAD_LEFT);        
    

    
    $Status = "";
    $ErrorCode = "";
    $ErrorDescription = "";
    $bHasError = false;
    $sLogoName = "";

    echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";
   
    $iNoErrorRecs = 0;
    $sProcessedFilename = basename($inputDir);
    $sFileName = basename($inputDir, "emb")."csv";
    $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);
    $iNoOfRecords = count($aInputFile);
    if($iNoOfRecords==0)
    {
        echo "\n$sDateStamp [$sUser]: ERROR: The $sProcessedFilename does not contain any data, the file is empty.  \n";
        return false;
    }
    $sBIN = findCustomer($aInputFile, $aBINs);
    //SERIAL NUMBER ASSIGN
  

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
    $aDataPrepOutputData = array();
    $aDataPrepBulkOutputData = array();
    
    /*MAILING*/
    $aBulkSingleShippingOutputData = array();
    $aBulkMultiShippingOutputData = array();
    $aMailBulkShippingOutputData = array();
    $aMailShippingOutputData = array();
    $aMailMergeShippingOutputData = array();

    /*REPORTS*/
    $aConfirmationReportOutputData = array();
    $aShipmentReportOutputData = array();

    /*FLEXIA*/
    $sFlexiaFileName = "";
    $iFlexiaNoOfFiles = -1;
    $iCheckedFiles= 0;

    /*PARSING*/
   
    echo "$sDateStamp [$sUser]: Starting Parsing Data\n";
    $iRecordNo =0;


    $previousSingleGroupID = "";
    $previousSingleCompany = "";
    $previousSingleBulkFullName = "";
    $previousSingleBulkAddress1 = "";
    $previousSingleBulkAddress2 = "";
    $previousSingleBulkCity = "";
    $previousSingleBulkState = "";
    $previousSingleBulkZIPCode = ""; 
    $previousSingleBulkZIPCodeAddOn = ""; 
    $previousSingleBulkCountry = "";

    $previousShipmentID = "";
    $previousGroupID = "";
    $previousCompany = "";
    $previousBulkFullName = "";
    $previousBulkAddress1 = "";
    $previousBulkAddress2 = "";
    $previousBulkCity = "";
    $previousBulkState = "";
    $previousBulkZIPCode = ""; 
    $previousBulkZIPCodeAddOn = ""; 
    $previousBulkCountry = "";
    $iNoOfSingleBulkCards = 0;

    foreach($aInputFile as $sRecord) { 
        
            if(strlen($SerialNumber)>$SerialNumberOfDigits)
            {
                $SerialNumber = 1;
            }
            $SerialNumber++;
            $SerialNumber =  str_pad($SerialNumber++,$SerialNumberOfDigits,'0',STR_PAD_LEFT); 
    
          

            ++$iRecordNo;
            $CARD_PROOF_ID = substr($sRecord, 0, 8);
            $BATCH_LOG_ID = substr($sRecord, 8 ,6);
            $PRODUCTION_TYPE = substr($sRecord, 14, 2);
            $CARD_CARRIER_STOCK = substr($sRecord, 16, 3);
            $PIN_MAILER_STOCK = substr($sRecord, 19, 2);
            $WELCOME_PACK_STOCK = substr($sRecord, 21, 10);
            $SHIPPING_METHOD = substr($sRecord, 31, 1);
            $CARD_STOCK_CARD_1 = substr($sRecord, 32, 3);
            $PAN_CARD_1 = substr($sRecord, 35, 19);
            $PIN_CARD_1 = substr($sRecord, 54, 4);
            $EXPIRATION_DATE_CARD_1 = substr($sRecord, 58, 4);
            $CARD_HOLDER_NAME_CARD_1 = substr($sRecord, 62, 28);
            $CARD_LINE_2_CARD_1 = substr($sRecord, 90, 28);
            $FIRST_NAME_CARD_1 = substr($sRecord, 118, 20);
            $LAST_NAME_CARD_1 = substr($sRecord, 138, 20);
            $CARD_STOCK_CARD_2 = substr($sRecord, 158, 3);
            $PAN_CARD_2 = substr($sRecord, 161, 19);
            $PIN_CARD_2 = substr($sRecord, 180, 4);
            $EXPIRATION_DATE_CARD_2 = substr($sRecord, 184, 4);
            $CARD_HOLDER_NAME_CARD_2 = substr($sRecord, 188, 28);
            $CARD_LINE_2_CARD_2= substr($sRecord, 216, 28);
            $FIRST_NAME_CARD_2= substr($sRecord, 244, 20);
            $LAST_NAME_CARD_2= substr($sRecord, 264, 20);
            $SHIPNAME_PAN_PRN = substr($sRecord, 284, 40);
            $SHIPADDRESS_LINE_1 = substr($sRecord, 324, 40);
            $SHIPADDRESS_LINE_2 = substr($sRecord, 364, 40);
            $SHIP_CITY = substr($sRecord, 404, 30);
            $SHIP_STATE = substr($sRecord, 434, 2);
            $SHIP_ZIP = substr($sRecord, 436, 10);
            $PAYMENT_REFERENCE_NUMBER = substr($sRecord, 446, 12);
                $CARD_STOCK_ID =  trim(substr($sRecord, 458, 30));
                //$CARD_STOCK_ID = "NA";  
                $COUNTRY_CODE = "840";
                $COUNTRY_ALPHA2 = "US";
            $FILLER_DATA = substr($sRecord, 458, 442); 
            $sBIN=substr(preg_replace('/\s+/', "", $PAN_CARD_1),0,6);

            $BULK_COMPANY_NAME = "";
            $BULK_ADDR1 = "";
            $BULK_ADDR2 = "";
            $BULK_CITY = "";
            $BULK_STATE = "";
            $BULK_ZIP = "";
            $BULK_COUNTRY_CODE = "";
            $BULK_COUNTRY_ALPHA2 = "";
            $BULK_COMPANY_PERSON_NAME = "";

            $CUSTOM_DATA =  "";
            $GROUP_ID =  "";   
            $INDICATORS = "";
            $LOGO_USAGE_INDICATOR = "";
            $BULK_SHIPPING_METHOD = "";
            $BULK_COMPANY_PERSON_NAME = "";
            $BULK_COMPANY_NAME = "";
   
            
            //CARDSTOCK ID FOR ANY CUSTOMER
            
            // if(empty(trim($CARD_STOCK_ID)))
            // { 
            //     $CARD_STOCK_ID = "NA";
            // }
            // else if($sBIN == "412407")
            // {
            //     $CARD_STOCK_ID = "NA";
            // }


            if(isset($aBINs[$sBIN][trim($WELCOME_PACK_STOCK)]))
                {
                    foreach(($aBINs[$sBIN][trim($WELCOME_PACK_STOCK)]) as $sConfigCardStock => $aConfiguration)
                    {
                        if($sConfigCardStock!="NA")
                        { 
                                $iCardStockPos = strpos(substr($sRecord, 457, 30),$sConfigCardStock);
                                if($iCardStockPos)
                                {
                                    $CARD_STOCK_ID = substr($sRecord, $iCardStockPos+457, strlen($sConfigCardStock));
                                }                             
                        }
                        else
                        {
                            $CARD_STOCK_ID = "NA";
                        }
                    }
                }

            //EQ Country code
            if($sBIN=="546994")
            {
                $COUNTRY_ALPHA2 = "CA";
            }

         

            //POMELO CUSTOM DATA
            if($sBIN=="546854")
            {
                if(isset($aBINs[$sBIN][trim($WELCOME_PACK_STOCK)]))
                {
                    foreach(($aBINs[$sBIN][trim($WELCOME_PACK_STOCK)]) as $sConfigCardStock => $aConfiguration)
                    {
                        $iCardStockPos = strpos($sRecord,$sConfigCardStock);
                        if($iCardStockPos == 461)
                        {
                            $COUNTRY_CODE = substr($sRecord, 458, 3);
                            $COUNTRY_ALPHA2 = convertCountry('id',$COUNTRY_CODE,'alpha2');
                            $CUSTOM_DATA =  substr($sRecord, 461, 30); 
                            if(preg_match('/\|/',$CUSTOM_DATA))
                            {
                                $aCustomFillerData  = explode("|",$CUSTOM_DATA);
                                $CARD_STOCK_ID = $aCustomFillerData[0];
                                $BARCODE =  $aCustomFillerData[1];
                                
                            }
                            else
                            {
                                $CARD_STOCK_ID = trim($CUSTOM_DATA);
                                $BARCODE = "";
                            }
                        }
                        else if($iCardStockPos == 458)
                        {
                            $COUNTRY_CODE = "840";
                            $COUNTRY_ALPHA2 = "US";
                            $CUSTOM_DATA =  substr($sRecord, 458, 30); 
                            if(preg_match('/\|/',$CUSTOM_DATA))
                            {
                                $aCustomFillerData  = explode("|",$CUSTOM_DATA);
                                $CARD_STOCK_ID = $aCustomFillerData[0];
                                $BARCODE =  $aCustomFillerData[1];
                                
                            }
                            else
                            {
                                $CARD_STOCK_ID = trim($CUSTOM_DATA);
                                $BARCODE = "";
                            }
                        }
                      
                    }  
              }
            } 

            //MESH CUSTOM DATA
            if($sBIN=="412407")
            {
                $CUSTOM_DATA =  substr($sRecord, 458, 11); 
                $GROUP_ID =  substr($sRecord, 469, 7); 
                
                $INDICATORS =  explode('_',substr($sRecord, 476, 80));
                    $LOGO_USAGE_INDICATOR = strval($INDICATORS[0]);
                    $BULK_SHIPPING_METHOD = strval($INDICATORS [1]);
                    $BULK_COMPANY_PERSON_NAME = $INDICATORS[3];
                    $BULK_COMPANY_NAME = $INDICATORS[2];
                $BULK_ADDR1 = substr($sRecord, 556, 20);
                $BULK_ADDR2 = substr($sRecord, 576, 20);
                $BULK_CITY = substr($sRecord, 596, 20);
                $BULK_STATE = substr($sRecord, 616, 2);
                $BULK_ZIP = substr($sRecord, 618, 9);
                $BULK_COUNTRY_CODE = substr($sRecord, 627, 3);
                $BULK_COUNTRY_ALPHA2 = convertCountry('id',$BULK_COUNTRY_CODE,'alpha2');
                //$BULK_COUNTRY_ALPHA3 = convertCountry('id',$BULK_COUNTRY_CODE,'alpha3');
                //$BULK_COUNTRY_NAME = convertCountry('id',$BULK_COUNTRY_CODE,'name');
                //$COUNTRY_CODE = substr($sRecord, 627, 3);
                //$COUNTRY_CODE_ALPHA2 = (empty($COUNTRY_CODE)) ? "US" : convertCountry('id',$COUNTRY_CODE,'alpha2');
            } 
            

            

            //ERROR CHECK TO CONFIRM BIN
            if(isset($aBINs[$sBIN]))
            {   
                //ERROR CHECK TO CONFIRM PRODUCT ID
                if(isset($aBINs[$sBIN][trim($WELCOME_PACK_STOCK)]))
                {
                    //ERROR CHECK TO CONFIRM CARD STOCK
                    if(isset($aBINs[$sBIN][trim($WELCOME_PACK_STOCK)][trim($CARD_STOCK_ID)]))
                    {
                            $ProductProp = $aBINs[$sBIN][trim($WELCOME_PACK_STOCK)][trim($CARD_STOCK_ID)];
                            $ProductID = trim($WELCOME_PACK_STOCK);
                            $Status = "OK";
                            $ErrorCode = "N/A";
                            $ErrorDescription = "N/A";
                            $bHasError= false;

                            //DATA VALIDATION

                            //ERROR CHECK FLEXIA BIN CHECK FOR FILE
                            if($sBIN == "544292")
                            {
                                $sTrack1Token = getFlexiaToken(trim($PAYMENT_REFERENCE_NUMBER));
                                if($sTrack1Token == FALSE)
                                {
                                    $iNoErrorRecs++;
                                    $sError = "";
                                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the card stock ID: ".trim($CARD_STOCK_ID)." with PRN: ".trim($PAYMENT_REFERENCE_NUMBER)." is missing matching PRN in Flexia file, therefor we are unable to create Track1 and this record is considered as bad record. \n";
                                    $aErrors[] = $sError;
                                    echo $sError;
                                    $Status = "NOK";
                                    $ErrorCode = "305";
                                    $ErrorDescription = "The Flexia file is missing";
                                    $bHasError= true;
                                    $ProductProp['Product'] = "NOK";
                                    $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                    $ProductID = trim($WELCOME_PACK_STOCK);
                                }
                                
                            }
                      

                            //ERROR CHECK FOR MESH CID, COMPANY NAME
                            if($sBIN == "412407")
                            {
                                $iErrorsPerRecord = 0;
                                if(!(preg_match('/[0-9]{6}-[0-9]{4}/', trim($CUSTOM_DATA))))//CID check
                                {
                                    $iErrorsPerRecord++;
                                    //$iNoErrorRecs++;
                                    $sError = "";
                                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the CID is not in correct format (6 digits - 4 digits: 123456-1234), received CID ".trim($CUSTOM_DATA)." \n";
                                    $aErrors[] = $sError;
                                    echo $sError;
                                    $Status = "NOK";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error";
                                    $bHasError= true;
                                    $ProductProp['Product'] = "NOK";
                                    $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                    $ProductID = trim($WELCOME_PACK_STOCK);
                                    
                                }
                                else if(empty(trim($CARD_LINE_2_CARD_1)))//Company nick name
                                {
                                    $iErrorsPerRecord++;
                                    $sError = "";
                                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the Company nick name is empty. \n";
                                    $aErrors[] = $sError;
                                    echo $sError;
                                    $Status = "NOK";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error";
                                    $bHasError= true;
                                    $ProductProp['Product'] = "NOK";
                                    $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                    $ProductID = trim($WELCOME_PACK_STOCK);
                                }

                                if(strtoupper($ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)])=="BULK")
                                {
                                    if(empty($GROUP_ID))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the Group ID is empty. \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }
                                    if($LOGO_USAGE_INDICATOR==null)
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the Logo Usage Indicator is empty. \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }
                                    else if($LOGO_USAGE_INDICATOR=="1")
                                    {
                                        
                                        $CARD_LINE_2_CARD_1= trim($CARD_LINE_2_CARD_1);
                                       
                                        if($sLogoName!=$CARD_LINE_2_CARD_1)
                                        {
                                            $sLogoName = $CARD_LINE_2_CARD_1;
                                            $aLogoFiles = glob("$sMeshImagesDir*".$CARD_LINE_2_CARD_1."*");
                                            if($aLogoFiles)
                                            {
                                                echo "$sDateStamp [$sUser]: Logo file with ID: ".$CARD_LINE_2_CARD_1." is found in: ".$aLogoFiles[0]."\n";
                                                $bFileMoved = rename($aLogoFiles[0] ,$sMachineImagesDir.basename($aLogoFiles[0]));
                                                if(!$bFileMoved)
                                                {
                                                    echo "$sDateStamp [$sUser]: Logo file with ID: ".$CARD_LINE_2_CARD_1." is found in: ".$aLogoFiles[0]."unable to move to $sMachineImagesDir location \n";
                                                }
                                                else
                                                {
                                                    echo "$sDateStamp [$sUser]: Logo file with ID: ".$CARD_LINE_2_CARD_1." is succesfully moved ".$sMachineImagesDir.basename($aLogoFiles[0])." location \n";
                                                }
                                            }
                                            else 
                                            {
                                                $aLogoFiles = glob("$sMachineImagesDir*".$CARD_LINE_2_CARD_1."*");
                                                if($aLogoFiles)
                                                {
                                                    echo "$sDateStamp [$sUser]: Logo file with ID: ".$CARD_LINE_2_CARD_1." is found already in folder for Machine: ".$aLogoFiles[0]."\n";
                                                }
                                                else
                                                {
                                                    //$iErrorsPerRecord++;
                                                    $sError = "";
                                                    $sError = "$sDateStamp [$sUser]: WARNING: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the logo image with ID: $sLogoName is missing. \n";
                                                    $aErrors[] = $sError;
                                                    echo $sError;
                                                    $Status = "WARNING";
                                                    $ErrorCode = "306";
                                                    $ErrorDescription = "Warning Logo not found";
                                                    //$bHasError= true;
                                                    //$ProductProp['Product'] = "WARNING";
                                                    //$ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "WARNING";
                                                    $ProductID = trim($WELCOME_PACK_STOCK);
                                                }
                                               
                                            }
                                        }

                                    }

                                    if(empty($BULK_SHIPPING_METHOD))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the BULK Shipping Method is empty. \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }

                                    if(empty($BULK_COMPANY_PERSON_NAME))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the BULK person company name is empty. \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }

                                    if(empty($BULK_COMPANY_NAME))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the BULK Company name is empty. \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }

                                    if(empty($BULK_ADDR1))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the BULK Address 1 is empty. \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }

                                    if(empty($BULK_CITY))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the BULK City is empty. \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }

                                    if(empty($BULK_STATE))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the BULK State is empty. \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }

                                    if(empty($BULK_ZIP))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the BULK Postal Code is empty. \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }

                                    if(empty($BULK_COUNTRY_CODE))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the BULK Country Code is empty. \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }
                                    if($BULK_COUNTRY_ALPHA2==false)
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the Country Code does not exists. Received Country Code: ".trim($BULK_COUNTRY_ALPHA2)." \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }

                                }
                                if($iErrorsPerRecord>0)
                                {
                                    $iNoErrorRecs++;
                                    $iErrorsPerRecord=0;
                                }

                                
                            }

                            //ERROR CHECK FOR POMELO
                            if($sBIN=="546854")
                            {
                                $iErrorsPerRecord = 0;
                                if(strtoupper($ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)])=="BULK")
                                {

                                    if(!isset($BARCODE)||empty(trim($BARCODE)))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the Barcode for BULK Shipment is missing".trim($BARCODE)." \n";
                                        $aErrors[] = $sError;
                                        echo $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error";
                                        $bHasError= true;
                                        $ProductProp['Product'] = "NOK";
                                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                        $ProductID = trim($WELCOME_PACK_STOCK);
                                    }
                                    
                                }
                                if($COUNTRY_ALPHA2==false)
                                {
                                    $iErrorsPerRecord++;
                                    $sError = "";
                                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the Country Code does not exists. Received Country Code ".trim($COUNTRY_CODE)." \n";
                                    $aErrors[] = $sError;
                                    echo $sError;
                                    $Status = "NOK";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error";
                                    $bHasError= true;
                                    $ProductProp['Product'] = "NOK";
                                    $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                                    $ProductID = trim($WELCOME_PACK_STOCK);
                                }
                                if($iErrorsPerRecord>0)
                                {
                                    $iNoErrorRecs++;
                                    $iErrorsPerRecord=0;
                                }
   
                            }
                    }
                    else
                    {
                        $iNoErrorRecs++;
                        $sError = "";
                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the card stock ID: ".trim($CARD_STOCK_ID)." is not defined in Products_configuration.csv. Please, review products configuration. \n";
                        $aErrors[] = $sError;
                        echo $sError;
                        $Status = "NOK";
                        $ErrorCode = "303";
                        $ErrorDescription = "The card stock ID from the file: ".trim($CARD_STOCK_ID).", is unknown";
                        $bHasError= true;
                        $ProductProp['Product'] = "NOK";
                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                        $ProductID = trim($WELCOME_PACK_STOCK);
                        
                    }
                }
                else
                {
                    $iNoErrorRecs++;
                    $sError = "";
                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." ,the product ID: ".trim($WELCOME_PACK_STOCK)." is not defined in Products_configuration.csv. Please, review products configuration. \n";
                    $aErrors[] = $sError;
                    echo $sError;
                    $Status = "NOK";
                    $ErrorCode = "302";
                    $ErrorDescription = "The product ID from the file: ".trim($WELCOME_PACK_STOCK).", is unknown";
                    $bHasError= true;
                    $ProductProp['Product'] = "NOK";
                    $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                    $ProductID = trim($WELCOME_PACK_STOCK);
                   
                }
            }
            else
            {
                $iNoErrorRecs++;
                $sError = "";
                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($CARD_HOLDER_NAME_CARD_1)." , the BIN: ".$sBIN." is not defined in Products_configuration.csv. Please, review products configuration.\n";
                $aErrors[] = $sError;
                echo $sError;
                $Status = "NOK";
                $ErrorCode = "301";
                $ErrorDescription = "The BIN from the file: ".$sBIN.", is unknown";
                $bHasError= true;
                $ProductProp['Product'] = "NOK";
                $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                $ProductID = trim($WELCOME_PACK_STOCK);
                
     
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
             $CardType = trim($WELCOME_PACK_STOCK);
             $PAN4 = substr($PAN_CARD_1, -4);
             $san2 = "";
             $name1 = trim($CARD_HOLDER_NAME_CARD_1);
             $name2 = trim($CARD_HOLDER_NAME_CARD_2);
             $Address1 = trim($SHIPADDRESS_LINE_1);
             $Address2 = trim($SHIPADDRESS_LINE_2);
             $City = trim($SHIP_CITY);
             $State =  trim($SHIP_STATE); 
             $ZIPCode = trim($SHIP_ZIP);
             $expDate = trim($EXPIRATION_DATE_CARD_1);

             $aConfirmationReportOutputData[] = array($Token,$FileName,"$ShipSuffix",$sBIN,$Status,"$ErrorCode","$ErrorDescription",$DateReceived,$CardType,$PAN4,$san2,"$name1","$name2","$Address1","$Address2",$City,$State,$ZIPCode,$expDate);

            if($iNoOfRecords==$iNoErrorRecs)
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
            $sFirstName = trim($FIRST_NAME_CARD_1);
            $sLastName = trim($LAST_NAME_CARD_1);
            $sPAN = preg_replace('/\s+/', "", $PAN_CARD_1);
            $sExpDateYYMM = substr(trim($EXPIRATION_DATE_CARD_1), 2,2).substr(trim($EXPIRATION_DATE_CARD_1), 0,2);
            $sEmbName = trim($CARD_HOLDER_NAME_CARD_1);
           
            $sBatchID = $BATCH_LOG_ID."/".$iRecordNo;
            $sUniqueNumber= sha1($sPAN);
            $sNotUsed1 = "0000";
            $sNotUsed2 = "00";
            $sNotUsed3 = "000";
            $sDataPrepProfile =  $ProductProp['Profile'];
            $sNotUsed4 = "0000000";
            $sChipData = "$sFirstName#$sLastName#$sPAN#$sExpDateYYMM#$sEmbName";
            if($sBIN == "412407")
           {
                $sEmbName=trim($CARD_LINE_2_CARD_1);
                $sCID = trim($CUSTOM_DATA);
                $sChipData = "$sFirstName#$sLastName#$sPAN#$sExpDateYYMM#$sEmbName#$sCID";
                if(!empty($LOGO_USAGE_INDICATOR))
                {
                    $sChipData = "$sFirstName#$sLastName#$sPAN#$sExpDateYYMM#$sEmbName#$sCID|$LOGO_USAGE_INDICATOR";
                }
           } 
           if($sBIN == "544292")
           {
                $sFirstName =  "";
                $sLastName =  (empty(trim($LAST_NAME_CARD_1))) ? "CARDHOLDER" : trim($LAST_NAME_CARD_1);
                $sEmbName =  (empty(trim($CARD_HOLDER_NAME_CARD_1))) ? "CARDHOLDER NAME" : trim($CARD_HOLDER_NAME_CARD_1);
                $sPRN = trim($PAYMENT_REFERENCE_NUMBER);
                $sTrack1Token;
                $sChipData = "$sFirstName#$sTrack1Token#$sPAN#$sExpDateYYMM#$sEmbName#$sPRN";
           }
            //DATAPREP RESULT
            //$aDataPrepOutputData[] = array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData); 
            
              //Used by Reference1.php
              $sCustomerName3 = substr($sCustomerName,0,3);
              $sProductName = $ProductProp['Product'];
              $sProductName3 = substr($sProductName,0,3);
              $sSerialNumber = $SerialNumber;
              $sPAN4 = substr($sPAN, -4);

            /*MAILING*/
            $FullName = trim($SHIPNAME_PAN_PRN);
            $Company = ""; //Not listed in specifications
            $Address1 = trim($SHIPADDRESS_LINE_1);
            $Address2 = trim($SHIPADDRESS_LINE_2);
            $City = trim($SHIP_CITY);
            $State =  trim($SHIP_STATE); 
            $ZIPCode = trim($SHIP_ZIP);
            $ZIPCodeAddOn = "";
            $Country = $COUNTRY_ALPHA2;
            if($Country == "US")
            {
                $ZIPCode = substr(trim($SHIP_ZIP), 0,5);
                $ZIPCodeAddOn = empty(substr($SHIP_ZIP,5)) ? "" : preg_replace("/-/","",substr(trim($SHIP_ZIP),5));
            }
    
            //MESH
            if($sBIN == "412407")
            {
                if(strtoupper($ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)])=="BULK")
                {
                    $FullName = trim($BULK_COMPANY_PERSON_NAME);
                    $Company = trim($BULK_COMPANY_NAME);
                    $Address1 = trim($BULK_ADDR1);
                    $Address2 = trim($BULK_ADDR2);
                    $City = trim($BULK_CITY);
                    $State =  trim($BULK_STATE); 
                    $ZIPCode = trim($BULK_ZIP);
                    $ZIPCodeAddOn = "";
                    $Country = $COUNTRY_CODE;
                    if($Country == "US")
                    {
                        $ZIPCode = substr(trim($BULK_ZIP), 0,5);
                        $ZIPCodeAddOn = empty(substr($BULK_ZIP,5)) ? "" : preg_replace("/-/","",substr(trim($BULK_ZIP),5));
                    }

                }
            } 
            //$EmailAddress;
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
            //$Reference1 = substr($sCustomerName,0,3)."_".$SerialNumber."_".substr($PAN_CARD_1, -4);
            // $Reference1 = $sCustomerName."_".$BATCH_LOG_ID."_".$ProductProp['Product']."_".$ProductProp['ShippingMethods'][$SHIPPING_METHOD]."_".preg_replace("/\s+/","_",trim($SHIPNAME_PAN_PRN))."_".substr($PAN_CARD_1, -4);
           
            $Reference2 = strtoupper(hash("sha256",$sPAN, false));
            $Reference3 = trim($PAYMENT_REFERENCE_NUMBER);
            $sFedexAccount =(empty($ProductProp["FEDxAccount"])) ? "" : "AccNo:".$ProductProp["FEDxAccount"]."|";
            $sFedexPhoneNumber = (empty($ProductProp["FEDEXPhoneNumber"])) ? "" : "@".$ProductProp["FEDEXPhoneNumber"]."|";;
            $Reference4 = $sFedexAccount.$sFedexPhoneNumber;

            

            
            if(empty($ServiceType)){
                switch(strtoupper($ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)]))
                {
                    case "USPS":
                        $ServiceType = "US-FC";
                        break;
                    case "USPS_TR":
                        $ServiceType = "US-FC";
                        break;   
                    case "USPS_PM":
                        $ServiceType = "US-PM";//FEDEX
                        break; 
                    case "BULK": 
                        $ServiceType = "US-FC";
                        break;
                    default:
                        $ServiceType = "US-FC";
                    break;
                }
            }

            if(empty($PackageType)){
                switch(strtoupper($ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)]))
                {
                    case "USPS":              
                        $PackageType = "Letter";
                        break;      
                    case "USPS_TR":
                        $PackageType = "Package";
                        break;           
                    case "USPS_PM":
                        $PackageType = "Flat Rate Envelope";
                        break;
                    case "BULK": 
                        $PackageType = "Package";
                        break;
                    default:
                        $PackageType = "Letter";
                        break;
                }
            }

            //BULK PACKAGE
            if(strtoupper($ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)])=="BULK")
            {
                $iNoOfSingleBulkCards++;
                $BulkCompany = empty($ProductProp['BulkCompanyName']) ? $BULK_COMPANY_NAME : $ProductProp['BulkCompanyName'];
                $BulkFullName = empty($ProductProp['BulkFullName']) ? $BULK_COMPANY_PERSON_NAME : $ProductProp['BulkFullName'];
                $BulkAddress1 =  empty($ProductProp['BulkAddress1']) ? $BULK_ADDR1 : $ProductProp['BulkAddress1'];
                if(!isset($BULK_ADDR2))
                {
                    $BULK_ADDR2 = "";
                }
                $BulkAddress2 = empty($ProductProp['BulkAddress2']) ?  $BULK_ADDR2 : $ProductProp['BulkAddress2'];
                $BulkCity = empty($ProductProp['BulkCity']) ? $BULK_CITY : $ProductProp['BulkCity'];
                $BulkState = empty($ProductProp['BulkState']) ? $BULK_STATE : $ProductProp['BulkState'];
                $BulkCountry = empty($ProductProp['BulkCountry']) ? $BULK_COUNTRY_ALPHA2 : $ProductProp['BulkCountry'];
                $BulkZIPCode = empty($ProductProp['BulkZIPCode']) ? $BULK_ZIP : $ProductProp['BulkZIPCode'];
                $BulkZIPCodeAddOn = "";
                if($BulkCountry == "US")
                {
                    $BulkZIPCodeAddOn = substr($BulkZIPCode,5);
                    $BulkZIPCode = substr($BulkZIPCode,0,5);
                }
                $BulkGroupId = (!isset($GROUP_ID)) ? "" : $GROUP_ID;
                
                if(
                           ($previousSingleGroupID != $BulkGroupId ||
                           $previousSingleCompany != $BulkCompany ||
                           $previousSingleBulkFullName != $BulkFullName||
                           $previousSingleBulkAddress1 != $BulkAddress1 ||
                           $previousSingleBulkAddress2 != $BulkAddress2 ||
                           $previousSingleBulkCity != $BulkCity ||
                           $previousSingleBulkState != $BulkState ||
                           $previousSingleBulkZIPCode != $BulkZIPCode || 
                           $previousSingleBulkZIPCodeAddOn != $BulkZIPCodeAddOn || 
                           $previousSingleBulkCountry != $BulkCountry) && ($sBIN!="412407") )
                            {
                               $previousSingleGroupID = $BulkGroupId;
                               $previousSingleCompany = $BulkCompany;
                               $previousSingleBulkFullName = $BulkFullName;
                               $previousSingleBulkAddress1 = $BulkAddress1;
                               $previousSingleBulkAddress2 = $BulkAddress2;
                               $previousSingleBulkCity = $BulkCity;
                               $previousSingleBulkState = $BulkState;
                               $previousSingleBulkZIPCode = $BulkZIPCode; 
                               $previousSingleBulkZIPCodeAddOn = $BulkZIPCodeAddOn; 
                               $previousSingleBulkCountry = $BulkCountry;
                               $aBulkSingleShippingOutputData[] = array($BulkCompany, $BulkFullName, $BulkAddress1, $BulkAddress2,$BulkCity,$BulkState,$BulkZIPCode,$BulkZIPCodeAddOn, $BulkCountry, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);
                            }
            }


            //MAILMERGE
            $Barcode = (empty($BARCODE))? "" : $BARCODE;
            


          

            //SHIPMENT REPORT
            $Token =   trim($PAYMENT_REFERENCE_NUMBER);
           // $ShipmentMethod = $aBINs[$sBIN]['ShippingMethods'][trim($SHIPPING_METHOD)]."|".$ServiceType."|".$ProductProp["PackageType"];
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
            $expDate = trim($EXPIRATION_DATE_CARD_1);
            $ForecastDeliveryDate = "N/A";
            if($ServiceType=="US-PM")
                {
                    $ForecastDeliveryDate =  date('m/d/Y',strtotime(' + 2 days'));
                }
                else if($ServiceType=="US-FC")
                {
                    $ForecastDeliveryDate =   date('m/d/Y',strtotime(' + 4 days'));
                }
            $Product = $ProductProp["Product"];
           
            
            $aShipmentReportOutputData[] = array($Token,$ShipmentMethod,$Tracking,$name1,$name2,$adr1,$adr2,$city,$state,$zipcode,$expDate,$ForecastDeliveryDate,$Product,$Status);
            $aMailShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);

            
            if($sBIN == "412407")
            {
                if(strtoupper($ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)])=="BULK")
                {
                    $aMailBulkShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BULK_SHIPPING_METHOD][$GROUP_ID][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);
                    $aDataPrepBulkOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BULK_SHIPPING_METHOD][$GROUP_ID][]=array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData);
                    //$aMailBulk1ShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BULK_SHIPPING_METHOD][$GROUP_ID]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);
                    $aBulkMultiShippingOutputData[$BULK_SHIPPING_METHOD][$GROUP_ID][] = array($Company, $BulkFullName, $BulkAddress1, $BulkAddress2,$BulkCity,$BulkState,$BulkZIPCode,$BulkZIPCodeAddOn, $BulkCountry, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);

                }
                else
                {

                    $aDataPrepOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData);
                    $aMailShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);
                    $aMailMergeShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4, $Barcode);
        
                }
            }
            else
            {
                $aDataPrepOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData);
                //$aMailShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4);
                $aMailMergeShippingOutputData[$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][]=array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1, $Reference2, $Reference3, $Reference4, $Barcode);   
            }

            $aMailShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4"."\r\n"));
            $aMailMergeShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4","Barcode"."\r\n"));
            $aConfirmationReportHeader = implode(",",array("Token", "FileName", "ShipSuffix","BIN","Status", "ErrorCode", "ErrorDescription", "DataReceived","CardType", "PAN4","san2","Name1","Name2","Address1","Address2","City","State","ZipCode","ExpDate"."\r\n"));
            $aShipmentReportHeader = implode(",",array("Token","ShipmentMethod","Tracking","name1","name2","adr1","adr2","city","state","zipcode","expDate","ForecastDeliveryDate","Product","Status"."\r\n"));
            setSerialNumber($SerialNumberLocal,$SerialNumberOfDigits,$SerialNumber);


    }

    $iNumberOfRecords = $iRecordNo;
    $iGoodRecords = ($iNumberOfRecords-$iNoErrorRecs);
    echo "$sDateStamp [$sUser]: Batch of the file: $BATCH_LOG_ID \n";
    echo "$sDateStamp [$sUser]: Parsing Data Finished\n";
    echo "$sDateStamp [$sUser]: Total Number of records: $iNumberOfRecords \n"; 
    echo "$sDateStamp [$sUser]: Total Number of good records: ".$iGoodRecords." \n"; 
    echo "$sDateStamp [$sUser]: Total Number of records that errored out: $iNoErrorRecs \n\n"; 
    $sBIN = findCustomer($aInputFile, $aBINs);
    
    //echo "aDataPrepOutputData\n";
    //print_r($aDataPrepOutputData);
    //echo "aMailShippingOutputData\n";
    //print_r($aMailShippingOutputData);

    if(isset($aDataPrepOutputData) && !empty($aDataPrepOutputData))
    {
        echo "$sDateStamp [$sUser]: \n\n DATAPREP START \n\n";
        foreach($aDataPrepOutputData as $keyShipment => $aShippingRecord)     
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
                    
                        
                        $bFileWriting1; 
                        $aExistingFile = null;
                        $bExistingFile = false;
                        $sDataToWrite = null;
                        $maxRec = 500;
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
                                $sDataPrepOutputFile = $outputDir."MS_CHIPREP_".$sShippingName."_".$sProductName."_";
                                if($neededSplits > 0)
                                    $sDataPrepOutputFile = $sDataPrepOutputFile.$numSplits."_of_".$neededSplits."_";
                                $sDataPrepOutputFile = $sDataPrepOutputFile.$sFileName;

                                //CHECK IF FILE EXISTS:
                                $bExistingFile =file_exists($sDataPrepOutputFile);
                                if($bExistingFile)
                                {
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

    if(isset($aMailShippingOutputData) && !empty($aMailShippingOutputData))
    {
        if($sBIN != "412407")
        {
            if(strtoupper($ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)])!="BULK")
            {

                echo "$sDateStamp [$sUser]: \n\n MAILING START \n\n";
            
                foreach($aMailShippingOutputData as $keyShipment => $aShippingRecord)     
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
                                $maxRec = 500;
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
                                        $sDataPrepOutputFile =  $mailOutputDir."MAIL_".$sShippingName."_".$sProductName."_";
                                        if($neededSplits > 0)
                                            $sDataPrepOutputFile = $sDataPrepOutputFile.$numSplits."_of_".$neededSplits."_";
                                        $sDataPrepOutputFile = $sDataPrepOutputFile.$sFileName;
                                    
                                        //CHECK IF FILE EXISTS:
                                        $bExistingFile=file_exists($sDataPrepOutputFile);
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
    }

    if(isset($aMailMergeShippingOutputData) && !empty($aMailMergeShippingOutputData))
    {

        echo "$sDateStamp [$sUser]: \n\n MAILMERGE START \n\n";
        foreach($aMailMergeShippingOutputData as $keyShipment => $aShippingRecord)     
        {
            
            // $sShippingName = $ProductProp['ShippingMethods'][$keyShipment];
            
                
            foreach($aShippingRecord as $keyProduct => $aProductRecord)
            {
                foreach($aProductRecord as $keyCardStock => $aCardStockRecord)
                {
                        $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                        $sShippingName = $sProductProp['ShippingMethods'][$keyShipment];
                        $sProductName =  $sProductProp['Product'];
                        
                        $mailOutputDir = $sMailMergeOutputDir;
                        

                    echo "$sDateStamp [$sUser]: Mailing: Records per Shipment $sShippingName and per product $sProductName: ".count($aCardStockRecord)."\n";
                        $bFileWriting1; 
                        $aExistingFile = null;
                        $bExistingFile = false;
                        $sDataToWrite = null;
                        $maxRec = 500;
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
                                $sDataPrepOutputFile =  $mailOutputDir."MAILMERGE_".$sShippingName."_".$sProductName."_";
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
                                                fwrite($fp, $aMailMergeShippingOutputDataHeader);
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

    if(isset($aBulkSingleShippingOutputData) && !empty($aBulkSingleShippingOutputData))
    {
        //BULK SINGLE PACKAGE
        echo "$sDateStamp [$sUser]: \n\n BULK SINGLE PACKAGE START \n";
        if($sBIN=="546854")
        {
            $sBulkOutputDir = $sBulkFedexOutputDir;
        }
        $sBulkSingleOutputFile = $sBulkOutputDir."BULK_PKG_CARDS_".$iNoOfSingleBulkCards."_".(preg_replace("/(\.).*/","",$FileName)).".csv";
        $fp = fopen($sBulkSingleOutputFile,"w");
        fwrite($fp, $aMailShippingOutputDataHeader);
        //$aBulkSingleShippingOutputData = array_unique($aBulkSingleShippingOutputData);
        foreach($aBulkSingleShippingOutputData as $row)
        {
                // echo"unique\n";
                // print_r(array_unique($row));
                    $sDataToWrite =  implode("\t",$row)."\r\n";
                    $bFileWriting1 =fwrite($fp, $sDataToWrite);
                    $aFilesWritingStatus[] = $bFileWriting1;          
        }
            if($bFileWriting1)
            {
                echo "$sDateStamp [$sUser]: Bulk Single Package File for batch #: $BATCH_LOG_ID succesfully written as: $sBulkSingleOutputFile\n";
                fclose($fp);

            }
            else 
            {
                echo "$sDateStamp [$sUser]: Writing Bulk Single Package file for batch $BATCH_LOG_ID failed\n";
                fclose($fp);
            
            }
    }

    
   if($sBIN == "412407")
   {
       if(strtoupper($ProductProp['ShippingMethods'][trim($SHIPPING_METHOD)])=="BULK")
        {
            foreach($aDataPrepBulkOutputData as $keyShipment => $aShippingRecord)     
            {
            
                // echo "aShippingRecord\n";
                // print_r($aShippingRecord);
        
               // $sShippingName = $ProductProp['ShippingMethods'][$keyShipment];
                //echo"\nSHIPPING NAME $sShippingName\n";
                foreach($aShippingRecord as $keyProduct => $aProductRecord)
                {
                    foreach($aProductRecord as $keyCardStock => $aCardStockRecord)
                    {
                        foreach($aCardStockRecord as $keyBulkShippingMethod => $aBulkShippingMethod)
                        {
                            foreach($aBulkShippingMethod as $keyGroupID => $aGroupID)
                            {
                                    
                                    $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                                    $sShippingName = $sProductProp['ShippingMethods'][$keyShipment];
                                    $sBulkShippingName =  $sProductProp['ShippingMethodsBulk'][$keyBulkShippingMethod];
                                    $sProductName =  $sProductProp['Product'];
                                    $sGroupID = $keyGroupID;
                                    echo "$sDateStamp [$sUser]: DataPrep: Records Shipment $sShippingName and per product $sProductName: ".count($aCardStockRecord)."\n";
                                

                                    
                                    $bFileWriting1; 
                                    $aExistingFile = null;
                                    $bExistingFile = false;
                                    $sDataToWrite = null;
                                    $maxRec = 500;
                                    $numSplits = 0;
                                    $recordsDone = 0;
                                    $fp = null;
                                    $neededSplits = 0;
                                    if(count($aGroupID)>$maxRec)
                                    {
                                        $neededSplits = ceil(count($aGroupID) / $maxRec);
                                    }
                                    foreach($aGroupID as $row) 
                                    { 
                
                                        if($recordsDone == $maxRec)
                                            $recordsDone = 0;
                                        if($recordsDone == 0)
                                        {
                                            if($numSplits > 0)
                                                fclose($fp);
                                            ++$numSplits;
                                            $sDataPrepOutputFile = $outputDir."MS_CHIPREP_".$sShippingName."_".$sBulkShippingName."_".$sProductName."_".$sGroupID."_";
                                            if($neededSplits > 0)
                                                $sDataPrepOutputFile = $sDataPrepOutputFile.$numSplits."_of_".$neededSplits."_";
                                            $sDataPrepOutputFile = $sDataPrepOutputFile.$sFileName;
                
                                            //CHECK IF FILE EXISTS:
                                            $bExistingFile =file_exists($sDataPrepOutputFile);
                                            if($bExistingFile)
                                            {
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

            echo "$sDateStamp [$sUser]: \n\n MAIL BULK START \n\n";
            foreach($aMailBulkShippingOutputData as $keyShipment => $aShippingRecord)     
            {
                
               // $sShippingName = $ProductProp['ShippingMethods'][$keyShipment];
                foreach($aShippingRecord as $keyProduct => $aProductRecord)
                {
                    foreach($aProductRecord as $keyCardStock => $aCardStockRecord)
                    {
                        foreach($aCardStockRecord as $keyBulkShippingMethod => $aBulkShippingMethod)
                        {       
                            foreach($aBulkShippingMethod as $keyGroupID => $aGroupID)
                            {
                                    $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                                    $sShippingName = $sProductProp['ShippingMethods'][$keyShipment];
                                    $sBulkShippingName =  $sProductProp['ShippingMethodsBulk'][$keyBulkShippingMethod];
                                    $sProductName =  $sProductProp['Product'];
                                    $sGroupID = $keyGroupID;
                                    $mailOutputDir = $sMailOutputDir;
                                    //print_r($aGroupID);
                
                                echo "$sDateStamp [$sUser]: Mailing: Records per Shipment $sShippingName and per product $sProductName: ".count($aCardStockRecord)."\n";
                                    $bFileWriting1; 
                                    $aExistingFile = null;
                                    $bExistingFile = false;
                                    $sDataToWrite = null;
                                    $maxRec = 500;
                                    $numSplits = 0;
                                    $recordsDone = 0;
                                    $fp = null;
                                    $neededSplits = 0;
                
                                    if(count($aGroupID)>$maxRec)
                                    {
                                        $neededSplits = ceil(count($aGroupID) / $maxRec);
                                    }
                            
                                    foreach ($aGroupID as $row) 
                                    { 
                                        
                                        if($recordsDone == $maxRec)
                                            $recordsDone = 0;
                                        if($recordsDone == 0)
                                        {
                                            if($numSplits > 0)
                                                fclose($fp);
                                            ++$numSplits;
                                            $sDataPrepOutputFile =  $mailOutputDir."MAIL_".$sShippingName."_".$sBulkShippingName."_".$sProductName."_".$sGroupID."_";
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
                                                            fwrite($fp, $aMailMergeShippingOutputDataHeader);
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
                                                //getBulkDetailOverview($aMailBulkShippingOutputData);
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
            getBulkDetailOverview($aMailBulkShippingOutputData);


             echo "$sDateStamp [$sUser]: \n\n BULK MULTIPLE PACKAGES START \n\n";
            foreach($aBulkMultiShippingOutputData as $keyBulkShippingMethod => $aBulkShippingData)     
            {
                foreach($aBulkShippingData as $keyGroupID => $aGroupID)  
                {   

                        $iNoOfCardsPerGroupID = count($aGroupID);
                        $sProductProp =  $aBINs[$sBIN][$keyProduct][$keyCardStock];
                        $sBulkShippingName = $sProductProp['ShippingMethodsBulk'][$keyBulkShippingMethod];
    
                        if(preg_match('/FEDx/',$sBulkShippingName))
                        {
                            $sBulkOutputDir = $sBulkFedexOutputDir;
                        }
                        else
                        {
                            $sBulkOutputDir = $sBulkOutputDir;
                        }
                        $sBulkMultiOutputFile = $sBulkOutputDir."BULK_PKGS_".$sBulkShippingName."_GROUP_$keyGroupID"."_CARDS_$iNoOfCardsPerGroupID"."_".(preg_replace("/(\.).*/","",$FileName)).".csv";

                            $fp = fopen($sBulkMultiOutputFile, "w");
                            fwrite($fp, $aMailShippingOutputDataHeader);    
                        
                        
                        foreach($aGroupID as $row)
                        {
                            $sDataToWrite =  implode("\t",$row)."\r\n"; 
                            if(     
                                $previousGroupID != $keyGroupID ||
                                $previousShipmentID != $keyBulkShippingMethod ||
                                $previousCompany != $sDataToWrite[0] ||
                                $previousBulkFullName != $sDataToWrite[1]||
                                $previousBulkAddress1 != $sDataToWrite[2] ||
                                $previousBulkAddress2 != $sDataToWrite[3] ||
                                $previousBulkCity != $sDataToWrite[4] ||
                                $previousBulkState != $sDataToWrite[5] ||
                                $previousBulkZIPCode != $sDataToWrite[6] || 
                                $previousBulkZIPCodeAddOn != $sDataToWrite[7] || 
                                $previousBulkCountry != $sDataToWrite[8] )
                                {
                                    $previousGroupID = $keyGroupID;
                                    $previousShipmentID = $keyBulkShippingMethod;
                                    $previousCompany = $sDataToWrite[0];
                                    $previousBulkFullName = $sDataToWrite[1];
                                    $previousBulkAddress1 = $sDataToWrite[2];
                                    $previousBulkAddress2 = $sDataToWrite[3];
                                    $previousBulkCity = $sDataToWrite[4];
                                    $previousBulkState = $sDataToWrite[5];
                                    $previousBulkZIPCode = $sDataToWrite[6]; 
                                    $previousBulkZIPCodeAddOn = $sDataToWrite[7]; 
                                    $previousBulkCountry = $sDataToWrite[8];
                                
                                    $bFileWriting1 = fwrite($fp, $sDataToWrite);
                                    $aFilesWritingStatus[] = $bFileWriting1;
                                }
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
     }

    echo "$sDateStamp [$sUser]: \n\n CONFIRMATION REPORT START \n\n";
    $sConfirmationReportOutputFile = $sConfirmationReportDir.(preg_replace("/(\.).*/","",$FileName)).".conf_rep.csv";
    $fp = fopen($sConfirmationReportOutputFile,"w");
    fwrite($fp, $aConfirmationReportHeader);

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
   
 

        echo "$sDateStamp [$sUser]: \n\n SHIPMENT REPORT START \n\n";
        $sShipmentReportOutputFile = $sShipmentReportDir.(preg_replace("/(\.).*/","",$FileName)).".ship_rep_not_processed.csv";
        $fp = fopen($sShipmentReportOutputFile,"w");
        fwrite($fp, $aShipmentReportHeader);

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

           
                return $aMailShippingOutputData;

            
 }



 

 if($aErrors!=null)
 {
    echo "\n$sDateStamp [$sUser]: ERRORS during processing, to take action is needed: \n";
    foreach($aErrors as $sErrorMessage)
    {
        echo  $sErrorMessage;
    }
 }

// function prepareShipmentReport()

// function prepareConfirmationReport()

function findCustomer($aInputFile, $aBINs)
{
    $bCustomerFound = false;
    $i = 0;
    $maxRec = count($aInputFile)-1;
    while(!$bCustomerFound)
    {  
        $sBIN = substr(preg_replace("/\s+/","",(substr($aInputFile[$i],35,19))),0,6);
        if(isset($aBINs[$sBIN]))
        {
            $bCustomerFound = true;
            return $sBIN;
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
    global $sBIN;
    global $iNoErrorRecs;
    $iTotalNumberOfRecords=0;
    //$aInputData = array_slice($aInputData,0,1);
    //print_r($aInputData);
    foreach($aInputData as $keyShip => $aShipRecords)
    { 
        foreach($aShipRecords as $keyProd => $aProdRecords)
        {
            foreach($aProdRecords as $keyCardID => $aCardStocks)
            {
                foreach($aCardStocks as $keyCardStock => $aRecord)
                {
                    
                    $aCollectShipment[$keyProd][$keyCardID][] = $keyShip;
                }
            }
        }
    }
    //array_flip($aCollectShipment);
    //print_r($aCollectShipment);
    //$aShipmentServicesPerProduct = array_count_values($aCollectShipment);
    
    
    echo "\n\t Detail Summary of records in file Shipment Method and per Product for customer $sCustomerName: \n";
    printf('            %-20s|  %-20s|  %-20s|    %-20s ', 'ProductID-Name', 'CardStockID','ShipmentMethodID-Name', 'Total Number of Records');
    echo"\n";

    foreach($aCollectShipment as $keyShipPerProduct => $aProducts)
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
                printf('            %-20s|  %-20s|  %-20s|    %20d ',($sProductType."-".$sProductAlias), $keyCardStock, ($sShipmentMethodType."-".$sShipmentAlias), $iTotalNoPerService);
                echo"\n";
            }
            printf('           %-87s','.................................................................................................');
            echo"\n";
            printf('           %-70s  %20d', 'Subtotal Records per Product',$iSubTotalNumberOfRecords);
            echo"\n\n";
            
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

function getBulkDetailOverview($aInputData){

    global $sCustomerName;
    global $aBINs;
    global $sBIN;
    global $iNoErrorRecs;
    $iTotalNumberOfRecords=0;
    //$aInputData = array_slice($aInputData,0,1);
    //print_r($aInputData);
    echo "\n\t Bulk Mesh Detail Summary of records in file Shipment Method and per Product for customer $sCustomerName: \n";
    printf('            %-20s|  %-20s|  %-21s|  %-20s|  %-25s|    %-20s ', 'ProductID-Name', 'CardStockID','ShipmentMethodID-Name', 'Bulk Shipment Method', 'Group/Location ID', 'Total Number of Records');
    echo"\n";

    foreach($aInputData as $keyShipmentMethod => $aShipRecords)
    { 
        foreach($aShipRecords as $keyPerProduct => $aProdRecords)
        {
            foreach($aProdRecords as $keyCardStock => $aCardStocks)
            {
                $sProductAlias = $aBINs[$sBIN][$keyPerProduct][$keyCardStock]['Product'];
                $sShipmentAlias= $aBINs[$sBIN][$keyPerProduct][$keyCardStock]['ShippingMethods'][$keyShipmentMethod];

                foreach($aCardStocks as $keyBulkShipment => $aGroupIDs)
                {
                     $sBulkShipmentAlias= $aBINs[$sBIN][$keyPerProduct][$keyCardStock]['ShippingMethodsBulk'][$keyBulkShipment];
                     $iTotalNoPerService = 0;

                     foreach($aGroupIDs as $keyGroupID => $aRecords)
                     {
                        $iTotalNoPerService = count($aRecords);
                        $iTotalNumberOfRecords+=count($aRecords);
                        printf('            %-20s|  %-20s|  %-21s|  %-20s|  %-25s|    %20d ',($keyPerProduct."-".$sProductAlias), $keyCardStock, ($keyShipmentMethod."-".$sShipmentAlias),$keyBulkShipment."-".$sBulkShipmentAlias,$keyGroupID,  $iTotalNoPerService);
                        echo"\n";
                     }
                }
            }
        }
    }
    
        printf('           %-87s','------------------------------------------------------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-44s    %-20s    %72d', 'Total Good Processed Records','',$iTotalNumberOfRecords);
        echo"\n\n";

        printf('           %-87s','------------------------------------------------------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-44s    %-20s    %72d', 'Total Bad/Errored Records in file','',$iNoErrorRecs);
        echo"\n\n";

        printf('           %-87s','------------------------------------------------------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-44s    %-20s    %72d', 'Total Records in file','',$iNoErrorRecs+$iTotalNumberOfRecords);
        echo"\n\n";
    
    //return  $aCollectShipment;

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

  /*
  * @param convertCountry
   * @param string $currentType [Possible types are id alpha2 alpha3 name]
   * @param string $value 248, ax, ala, Aland Islands
   * @param string $newType id, alpha2, alpha3,name
   * @return return ISO format of country that you pass in newType
  */
function convertCountry(string $currentType,string $value,string $newType) {
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
   
   function progressBar($done, $total) {
    
 
    $perc = ceil(($done / $total) * 100);
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


   

echo "\n$sDateStamp [$sUser]: Ending Script\n";


?> 
