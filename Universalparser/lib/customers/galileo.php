<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 09/27/2022
Revision: 09/27/2022
Name: Radovan Jakus
Version: 1.0
Notes: Mapping Definition
******************************/

    function mapping_galileo($aInputFile)
        {
        $aDETAIL_RECORDS = array (
            "CARD_PROOF_ID" => 8,
            "BATCH_LOG_ID"=>6,
            "PRODUCTION_TYPE"=>2,
            "CARD_CARRIER_STOCK" => 3,
            "PIN_MAILER_STOCK" => 2,
            "WELCOME_PACK_STOCK"=>10,
            "SHIPPING_METHOD"=>1,
            "CARD_STOCK_CARD_1" => 3,
            "PAN_CARD_1" => 19,
            "PIN_CARD_1" =>4,
            "EXPIRATION_DATE_CARD_1" => 4,
            "CARD_HOLDER_NAME_CARD_1" => 28,
            "CARD_LINE_2_CARD_1" => 28,
            "FIRST_NAME_CARD_1" => 20,
            "LAST_NAME_CARD_1" => 20,
            "CARD_STOCK_CARD_2" => 3,
            "PAN_CARD_2"=>19,
            "PIN_CARD_2"=>4,
            "EXPIRATION_DATE_CARD_2"=>4,
            "CARD_HOLDER_NAME_CARD_2"=>28,
            "CARD_LINE_2_CARD_2"=>28,
            "FIRST_NAME_CARD_2"=>20,
            "LAST_NAME_CARD_2"=>20,
            "SHIPNAME_PAN_PRN"=>40,
            "SHIPADDRESS_LINE_1"=>40,
            "SHIPADDRESS_LINE_2"=>40,
            "SHIP_CITY"=>30,
            "SHIP_STATE"=>2,
            "SHIP_ZIP"=>10,
            "PAYMENT_REFERENCE_NUMBER"=>12,
            "CUSTOM_DATA" =>442
        );
        $RecordNo = 0;
            foreach($aInputFile as $sRecord){ 
                $iPos = 0;
                foreach($aDETAIL_RECORDS as $sKey => $iLength)
                {
                    
                    $aFileRecords["DETAIL_RECORD"][$RecordNo][$sKey] = mb_substr($sRecord, $iPos, $iLength,"UTF-8");
                    $iPos+=$iLength;
                }                    
                $RecordNo++;

            }
            return $aFileRecords;

    }



    function getFlexiaToken($Token,$FileName,$sSupplementalFile,$sSupplementalFileSetup,$sSupplementalFileSuffix)
    {
        global $sDateStamp;
        global $sUser;
        global $sSupplementalFileName;
        global $sFlexiaNoOfFiles;
        global $iCheckedFiles;
      
    
        if(empty($sSupplementalFileName))
        {
            $aFlexiaInputFiles = glob($sSupplementalFile."*".$sSupplementalFileSuffix);
            if($aFlexiaInputFiles)
            {
                $iFlexiaNoOfFiles = count($aFlexiaInputFiles);
              
               
                if($iFlexiaNoOfFiles!=0 && $iFlexiaNoOfFiles!=$iCheckedFiles)
                {
                   
                    echo "$sDateStamp [$sUser]: Flexia input files in $sSupplementalFile \n";
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
                            foreach($aInputFile as $sRecord)
                            {
                                    $aRecordData = str_getcsv($sRecord,"\t");
                                    $Token_ToMatch = trim($aRecordData[0]);
                                    $TRACK1_TOKEN = trim($aRecordData[1]);
                                    if($Token == $Token_ToMatch)
                                    {
                                        echo "\n$sDateStamp [$sUser]: Flexia matching file found: ".$sFlexiaInputFile."\n for Galileo Matching file: $FileName \n";
                                        $sSupplementalFileName = $sFlexiaInputFile;
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
            $aInputFile = file($sSupplementalFileName, FILE_SKIP_EMPTY_LINES);
            if(count($aInputFile)==0)
            {
                echo "\n$sDateStamp [$sUser]: ERROR: The ".$sSupplementalFileName." does not contain any data, the file is empty.  \n";
                return false;
            }
            else
            {
                // echo"Input File to read\n";
                // print_r($aInputFile);
                foreach($aInputFile as $sRecord)
                {
                        $aRecordData = str_getcsv($sRecord,"\t");
                        $Token_ToMatch = trim($aRecordData[0]);
                        $TRACK1_TOKEN = trim($aRecordData[1]);
                        if($Token == $Token_ToMatch)
                        {
                            return $TRACK1_TOKEN;
                        }
                }
                echo "\n$sDateStamp [$sUser]: ERROR:  The ".$sSupplementalFileName." does not contain any matching PRNs data to assign Track 1 Token.  \n";
                return false;
    
            }
        }
    
    
    }
    
    
    function datamap_validation_galileo($input, $inputDir, $aConfig)
    {

    $sDateStamp = date('Y-m-d H:i:s', time());
    $sUser = get_current_user();
    $sProductConfigFile = $aConfig['ProductConfigurationPath'];
    $sDataMapConfiguration = $aConfig['DataMapConfiguration'];
    $sShippingCodeConfiguration = $aConfig['ShippingCodeConfiguration'];
    $sCompositeFieldReference1Dir = $aConfig['CompositeField'];
    $sProcessor = trim($aConfig['Processor']);
    $sSupplementalFile = trim($aConfig['SupplementalFiles']);
    $sSupplementalFileSuffix= trim($aConfig['SupplementalFileSuffix']);


    $aBINs = getProductsList($sProductConfigFile);



    global $sSerialNumberurl;
    global $SerialNumberLocal;
    global $SerialNumberOfDigits;


    echo "\n$sDateStamp [$sUser]: Mapping Data: $inputDir \n";
    $aMappedDataBeforeValidation = array();

    /*SERIAL NUMBER*/
     $SerialNumber = getSerialNumber($SerialNumberLocal);
    
    //INIT VALUES
        $ProductID = "";
        $CardStockID = "";
        $ShippingMethod  = "";
        $ShippingServiceCode = "";
        $ShippingServiceType = "";
        $ShippingServiceName = "";
        $PAN = "";
        $PANMasked = "";
        $BIN = "";
        $BINExtended = "";
        $PANLast4 = "";
        $Track1 = "";
        $Track1Masked = "";
        $Track2 = "";
        $Track2Masked = "";
        $Track2_Chip = "";
        $PSN = "";
        $PINBlock = "";
        $CVC2 = "";
        $ICVV = "";
        $EmbName = "";
        $CompanyName = "";
        $FirstNameT1 = "";
        $LastNameT1 = "";
        $ExpDate_YYMM = "";
        $ChipData = "";
        $PANHashed_SHA1 = "";
        $PANHashed_SHA256 = "";
        $DataPrepProfile = "";
        $BulkID = "";
        $UniqueInternalBulkId = "";
        $BulkCompany = "";
        $BulkFullName = "";
        $BulkAddress1 = "";
        $BulkAddress2 = "";
        $BulkCity = "";
        $BulkState = "";
        $BulkZIPCode = "";
        $BulkZIPCodeAddOn = "";
        $BulkPostalCode = "";
        $BulkCountry = "";
        $FullName = "";
        $Address1 = "";
        $Address2 = "";
        $City = "";
        $State = "";
        $ZIPCode = "";
        $ZIPCodeAddon = "";
        $Country = "";
        $FromFullName = "";
        $FromAddress1 = "";
        $FromAddress2 = "";
        $FromCity = "";
        $FromState = "";
        $FromCountry = "";
        $FromZIPCode = "";
        $ServiceType = "";
        $PackageType = "";
        $WeightOz = "";
        $ShipDate = "";
        $ImageType = "";
        $Reference1 = "";
        $Reference2 = "";
        $Reference3 = "";
        $Reference4 = "";
        $DataMatching = "";
        $ValidFrom = "";
        $MemberSince = "";
        $DDAAccount = "";
        $Currency = "";
        $ImageIDFront = "";
        $ImageIDBack = "";
        $ExternalCardID = "";
        $ExternalCHID = "";
        $AdditionalField1 = "";
        $AdditionalField2 = "";
        $AdditionalField3 = "";
        $AdditionalField4 = "";
        $AdditionalField5 = "";
        $AdditionalField6 = "";
        $AdditionalField7 = "";
        $AdditionalField8 = "";
        $RecipientEmailAddress = "";
        $RecipientPhoneNumber = "";
        $FEDEXAccountNumber = "";
        $Token = "";
        $FileDate = "";
        $FileName = "";
        $CurrentDate = "";
        $CardType = "";
        $PostalCode = "";
        $EmbName2 = "";
        $TrackingNumber = "";
        $NumberOfRecordsInFile = "";
        $NumberOfRecordsPerGrouping = "";
        $NoErrorRecs = "";
        $Status = "";
        $iNoErrorRecs = 0;
        $ErrorMessage = "";
        $ErrorCode = "";
        $ErrorDescription = "";
        $Facility = "";
        $HasError = false;
        $ProductName = "";
        $Customer = "";
        $Barcode = "";
        $NumberOfGoodRecordsInFile = 0; 
        $NumberOfBadRecordsInFile = 0;
        $NumberOfRecordsTAGUS = 0;
        $NumberOfRecordsTAGPL= 0;
        $Processor = "";
        $LogoFileName = "";
        $LogoIndicator = "";
	$JobTicketNumber = "";




    $iNumberOfRecords = count($input);
    $sFileName =  basename($inputDir);
    if($iNumberOfRecords==0)
    {
        echo "\n$sDateStamp [$sUser]: ERROR: The $sFileName does not contain any data, the file is empty.  \n";
        return false;
    }

    $RecordNo = 0;
    foreach($input as $iRecID => $aRecord)
    {
        
   
        $aMappedDataBeforeValidation = mapDataFromConfig($sDataMapConfiguration,$aRecord,$sProcessor);
        extract($aMappedDataBeforeValidation,EXTR_OVERWRITE);
        $RecordNo++;
        $SerialNumber++;
        $SerialNumber = str_pad($SerialNumber++,$SerialNumberOfDigits,'0',STR_PAD_LEFT);
        $JobTicketNumber = $SerialNumber;
        //Grouping
        //$ProductID = trim($aRecord['49']);
        //$ShippingServiceCode = trim($aRecord['29']);

        $CardStockID = "NA";
        $ShippingMethod  = $ShippingServiceCode != 4 ? "ShipMethod": "BulkShipMethod"; //BULK or DTC
        //$ShippingServiceCode = trim($aRecord['SHIPPING_METHOD']); 
        $ShippingServiceType;
        $ShippingServiceName;
        //DATAPREP
        $PANemb = $PAN;
        $PAN = preg_replace('/\s+/', "",$PAN);
        $PANLength =  strlen($PAN);
        $BIN = substr($PAN,0,6);
        $BINExtended = substr($PAN,0,8);
        $PANLast4 = substr($PAN,-4);
        // $Track1 = "";
        // $Track2 = "";
        // $Track2Chip = "";
        $PANMasked = MaskPANData($PAN,$BIN,$PANLength);  // unable to maskg
        $Track1Masked = (empty($Track1)) ? "" : MaskPANData($Track1,$BIN,$PANLength);
        $Track2Masked = (empty($Track2)) ? "" : MaskPANData($Track2,$BIN,$PANLength);
        $Track2ChipMasked = (empty($Track2Chip)) ? "" : MaskPANData($Track2Chip,$BIN,$PANLength);
        $PSN;
        $PINBlock; // =  trim($aRecord['39']);
        $CVC2;// = trim($aRecord['8']);
        $ICVV;// = trim($aRecord['7']);
        $EmbName;// = trim($aRecord['5']);
        $CompanyName;// = "\"".trim($aRecord['6'])."\"";
        #$FirstNameT1 = "";
       // $FirstNameT1 = trim($aRecord['FIRST_NAME_CARD_1']);
        #$LastNameT1 = "";
        //$LastNameT1 = trim($aRecord['LAST_NAME_CARD_1']);
        //$ExpDate_MMYY = substr($ExpDate_MMYY, 2,2).substr($ExpDate_MMYY, 0,2);
        $PANHashed_SHA1 = sha1($PAN);
        $PANHashed_SHA256 = strtoupper(hash("sha256",$PAN, false));
        $DataPrepProfile = ""; ////////////////////////////////////////////#
        //SHIPPING
        // $BulkID;// = trim($aRecord['20']);
        // $BulkCompany;// = trim($aRecord['21']);
        // $BulkFullName;// = trim($aRecord['22']);
        // $BulkAddress1;// = trim($aRecord['24']);
        // $BulkAddress2;// = trim($aRecord['25']);
        // $BulkCity;// = trim($aRecord['26']);
        // $BulkState;// =  trim($aRecord['27']);
        // $BulkPostalCode;// = trim($aRecord['28']);
        // $BulkZIPCode = empty($BulkPostalCode) ? "" : substr($BulkPostalCode, 0,5);
        // $BulkZIPCodeAddOn = empty($BulkPostalCode) ? "" : preg_replace("/-/","",substr(trim($BulkPostalCode),5));

  
        //$BulkCountry =  (strlen($BulkPostalCode)==2) ? trim($BulkPostalCode) : convertCountry('alpha3',$BulkPostalCode,'alpha2');
        //$FullName = trim($aRecord['5']);
        $FullName = $FirstNameT1 . " " .  $LastNameT1;
        //$Address1 = trim($aRecord['13']);
        //$Address1 = trim($aRecord['SHIPADDRESS_LINE_1']);
        //$Address2 = trim($aRecord['14']);
        //$Address2 = trim($aRecord['SHIPADDRESS_LINE_2']);
        //$City = trim($aRecord['15']);
        //$City = trim($aRecord['SHIP_CITY']);
        //$State =  trim($aRecord['16']);
        //State =  trim($aRecord['SHIP_STATE']);
        $PostalCode;// =  trim($aRecord['17']);
        #$ZIPCode = substr($PostalCode ,0,5);
        //ZIPCode = trim($aRecord['SHIP_ZIP']);
        $ZIPCodeAddOn = empty(substr($PostalCode ,5)) ? "" : preg_replace("/-/","",substr($PostalCode ,5)); //ADDED ZIPAdd-On
        #$Country= trim($aRecord['18']);
        $Country;
        $FromFullName; ////////////////////////////////////////////////////////#
        $FromAddress1; ////////////////////////////////////////////////////#
        $FromAddress2;////////////////////////////////////////////////////#
        $FromCity;////////////////////////////////////////////////////#
        $FromState;////////////////////////////////////////////////////#
        $FromCountry;////////////////////////////////////////////////////#
        $FromZIPCode;////////////////////////////////////////////////////#
        $ServiceType;////////////////////////////////////////////////////#
        $PackageType;////////////////////////////////////////////////////#
        $WeightOz;////////////////////////////////////////////////////#
        $ShipDate;////////////////////////////////////////////////////#
        $ImageType = "Pdf";
        $Reference1;/////////////////////////////
        $Reference2 = $PANHashed_SHA256;
        //$Reference3 = trim($aRecord['0']);
        $Reference4 = "";
        $DataMatching = $Track2Masked;
        // $ValidFrom =  trim($aRecord['2']);
        // $MemberSince =  trim($aRecord['3']);
        // $DDAAccount =  trim($aRecord['11']);
        // $Currency = trim($aRecord['19']);
        // $ImageIDFront =  trim($aRecord['33']);
        // $ImageIDBack =  trim($aRecord['34']);
        // $ExternalCardID =  trim($aRecord['37']);
        // $ExteralCHID = trim($aRecord['38']);
        // $AdditionalField1 = trim($aRecord['40']);
        // $AdditionalField2 = trim($aRecord['41']);
        // $AdditionalField3 = trim($aRecord['42']);
        // $AdditionalField4 = trim($aRecord['43']);
        // $AdditionalField5 = trim($aRecord['44']);
        // $AdditionalField6 = trim($aRecord['45']);
        // $AdditionalField7 = trim($aRecord['46']);
        // $AdditionalField8 = trim($aRecord['47']);
        $RecipientEmail = $AdditionalField3;
        $RecipientPhone = $AdditionalField2;
        $FedexAccountNumber = "";
        //REPORTING
        $Reference3 = $Token;
        $FileDate = date('Ymd',filemtime($inputDir));
        $FileName =  pathinfo($inputDir)['filename']; 
        $FileNameWithExtension = basename($inputDir);
        $CurrentDate = date('Ymd');
        $CardType = $ProductID;
        $EmbName2 = "";
        $TrackingNumber = "Not Available";
        //INTERNAL
        $NumberOfRecordsInFile = count($input);
        $NumberOfGoodRecordsInFile;
        $NumberOfBadRecordsInFile;
        $NumberOfRecordsTAGUS;
        $NumberOfRecordsTAGPL;
        $NumberOfRecordsPerGrouping;
        $NoErrorRecs;
        $Status;
        $iNoErrorRecs;
        $ErrorMessage;
        $ErrorCode;
        $ErrorDescription;
        $Facility;
        $HasError;
        $ProductName;
        $Customer;
        $Processor = $sProcessor;
        if(isset($aBINs[$BINExtended]))
        {
            $BIN = $BINExtended;
            $bIsExtendedBINused = true;
        }




        $bHasCountryCode = false;
        
            //ERROR CHECK TO CONFIRM BIN
            if(isset($aBINs[$BIN]))
            {
                //ERROR CHECK TO CONFIRM PRODUCT ID
                if(isset($aBINs[$BIN][trim($ProductID)]))
                {                 

                    foreach(($aBINs[$BIN][trim($ProductID)]) as $sConfigCardStock => $aConfiguration)
                    {
                        if($sConfigCardStock!="NA")
                        { 
                                $iCardStockPos = strpos(substr($aRecord, 457, 30),$sConfigCardStock);
                                if($iCardStockPos)
                                {
                                    $bHasCountryCode = true;
                                    $CardStockID= substr($aRecord, $iCardStockPos+457, strlen($sConfigCardStock));
                                    $Barcode = trim(substr($aRecord, $iCardStockPos+457+strlen($CardStockID)+1));
                                }                             
                        }
                        else
                        {
                            $CardStockID= "NA";
                        }
                    }

                    //ERROR CHECK TO CONFIRM CARD STOCK
                    if(isset($aBINs[$BIN][trim($ProductID)][trim($CardStockID)]))
                    {
                        $ProductProp = $aBINs[$BIN][trim($ProductID)][trim($CardStockID)];
                        $sCustomDataPerProduct = $ProductProp['CustomDataMap'];
                        if(isset($sCustomDataPerProduct) && !empty($sCustomDataPerProduct)) 
                        {
                            $aMappedDataBeforeValidation = mapDataFromConfig($sDataMapConfiguration,$aRecord,$sCustomDataPerProduct);
                            extract($aMappedDataBeforeValidation,EXTR_OVERWRITE);
                        }
                        $sSupplementalFileSetup = $ProductProp['SupplementalFile'];
                        if(isset($sSupplementalFileSetup) && !empty($sSupplementalFileSetup)) 
                        {   
                            $LastNameT1 = getFlexiaToken($Token, $FileName, $sSupplementalFile,$sSupplementalFileSetup,$sSupplementalFileSuffix);
                            $Barcode = $LastNameT1;
                        }

                        $ExpDate_YYMM = substr($ExpDate_MMYY, 2,2).substr($ExpDate_MMYY, 0,2);
                        $PAN = preg_replace('/\s+/', "",$PAN);
                        $ChipData = "$FirstNameT1#$LastNameT1#$PAN#$ExpDate_YYMM#$EmbName";
                        
                        $PostalCode = str_replace([' ','-'], '', $PostalCode);
                        $BulkPostalCode = str_replace([' ','-'], '', $BulkPostalCode);
                        if(!empty($BulkData))
                        {
                            $aBulkData = explode("_",$BulkData);
                            $LogoIndicator = $aBulkData[0];
                            $ShippingServiceCode = $aBulkData[1];
                            $BulkCompany = $aBulkData[2];
                            $BulkFullName = $aBulkData[3];
                            $BulkZIPCode = empty($BulkPostalCode) ? "" : substr($BulkPostalCode, 0,5);
                            $BulkZIPCodeAddOn = empty($BulkPostalCode) ? "" : preg_replace("/-/","",substr(trim($BulkPostalCode),5));
                        }

                        
                      
    
                        $Status = "OK";
                        $ErrorCode = "N/A";
                        $ErrorDescription = "";
                        $HasError= false;
                        $Customer = $ProductProp['Customer'];
                        $ProductName = $ProductProp['TagProductID'];
                        $DataPrepProfile = $ProductProp['DPProfileName'];
                        $CardStockIDName = $ProductProp['CardStockID'];
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
                        $BulkCompany = !empty($ProductProp["BulkCompany"]) ? $ProductProp["BulkCompany"]: $BulkCompany;
                        $BulkFullName = !empty($ProductProp["BulkFullName"]) ? $ProductProp["BulkFullName"]: $BulkFullName;
                        $BulkAddress1 = !empty($ProductProp["BulkAddress1"]) ? $ProductProp["BulkAddress1"]: $BulkAddress1;
                        $BulkAddress2 = !empty($ProductProp["BulkAddress2"]) ? $ProductProp["BulkAddress2"]: $BulkAddress2;
                        $BulkCity = !empty($ProductProp["BulkCity"]) ? $ProductProp["BulkCity"]: $BulkCity;
                        $BulkState =  !empty($ProductProp["BulkState"]) ? $ProductProp["BulkState"]: $BulkState;
                        $BulkPostalCode = !empty($ProductProp["BulkZIPCode"]) ? $ProductProp["BulkZIPCode"]: $BulkPostalCode;
                        $BulkZIPCode = !empty($ProductProp["BulkZIPCode"]) ? substr($ProductProp["BulkZIPCode"],0,5): $BulkZIPCode;
                        $BulkZIPCodeAddOn = !empty($ProductProp["BulkZIPCode"]) ? substr($ProductProp["BulkZIPCode"],5): $BulkZIPCodeAddOn;
                        $BulkCountry =  !empty($ProductProp["BulkCountry"]) ? $ProductProp["BulkCountry"]: $BulkCountry;
                        $RecipientPhone = !empty($ProductProp["FEDEXPhoneNumber"]) ? $ProductProp["FEDEXPhoneNumber"]: $RecipientPhone;
                        $FedexAccountNumber = $ProductProp["FEDEXAccount"];
                        $Reference1 = include($sCompositeFieldReference1Dir);
                        $CustomerImagesDir =$ProductProp["CustomerImagesDir"];
                        $MachineImagesDir = $ProductProp["MachineImagesDir"];
                        if(empty($Country)&&$BIN=='546994')
                             $Country = "CA";     
                        else if(empty($Country) || $bHasCountryCode)
                            $Country = "US";
                        else if(preg_match('/\d{1,3}/',$Country))
                            $Country = convertCountry('id',$Country,'alpha2');
                        else if(preg_match('/[A-Za-z]{3}/',$Country))
                            $Country = convertCountry('alpha3',$Country,'alpha2');
                        else if(preg_match('/[A-Za-z]{3,}/',$Country))
                            $Country = convertCountry('name',$Country,'alpha2');
                
                
                        if(empty($BulkCountry)&&$ShippingMethod=="BulkShipMethod")
                            $BulkCountry = "US";
                        else if(preg_match('/\d{1,3}/',$BulkCountry))
                            $BulkCountry = convertCountry('id',$BulkCountry,'alpha2');
                        else if(preg_match('/[A-Za-z]{3}/',$BulkCountry))
                            $BulkCountry = convertCountry('alpha3',$BulkCountry,'alpha2');
                        else if(preg_match('/[A-Za-z]{3,}/',$Country))
                            $BulkCountry = convertCountry('name',$BulkCountry,'alpha2');


                        if($Customer=='MESH')
                            $ChipData = "$FirstNameT1#$LastNameT1#$PAN#$ExpDate_YYMM#$EmbName#$CID|$LogoIndicator";
                        if ($Customer == 'FLEXIA') 
                        {
                            $EmbName =  (empty($EmbName)) ? "CARDHOLDER NAME" : $EmbName;
                            $ChipData = "$FirstNameT1#$LastNameT1#$PAN#$ExpDate_YYMM#$EmbName#$Token";
                        }
                        if($Customer=='C2FO')
                        {
                            $ChipData = "$FirstNameT1#$LastNameT1#$PAN#$ExpDate_YYMM#$EmbName#$CompanyName";
                        }

                        //DATA VALIDATION
                        $iErrorsPerRecord = 0;

                        if(!isset($ProductProp[$ShippingMethod.'_'.$ShippingServiceCode]))
                        {
                            $iErrorsPerRecord++;
                            $ErrorMessage = "";
                            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir , in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." , the shipping method $ShippingMethod and it's shipping service $ShippingServiceCode is invalid. The valid shipping service for product ".$ProductName." are configured in ".basename($sProductConfigFile)."  \n";
                            $aErrors[] = $ErrorMessage;
                            echo $ErrorMessage;
                            $Status = "NOK";
                            $ErrorCode = "307";
                            $ErrorDescription = "Wrong Shipping Method";
                            $HasError= true;
                            $ProductName = "NOK";
                            $ShippingServiceName = "NOK";
                        }
                        else
                        {
                            $ShippingServiceName= $ProductProp[$ShippingMethod.'_'.$ShippingServiceCode];
                            $aTagShippingCodes = getProductsList($sShippingCodeConfiguration);
                            foreach($aTagShippingCodes as $aTagShippingCode)
                            {
                                    if(empty($ServiceType))
                                        if($aTagShippingCode['TagShippingcode'] == $ShippingServiceName)
                                            $ServiceType = $aTagShippingCode['ServiceType']; 
                                        else
                                            $aErrors[]= "$sDateStamp [$sUser]: ERROR: ServiceType for \'$ShippingServiceName\' is not defined in \'$sShippingCodeConfiguration\' .\n";

                                    if(empty($PackageType))
                                            if($aTagShippingCode['TagShippingcode'] == $ShippingServiceName)
                                                $PackageType = $aTagShippingCode['PackageType']; 
                                            else
                                                $aErrors[]= "$sDateStamp [$sUser]: ERROR: PackageType for \'$ShippingServiceName\' is not defined in \'$sShippingCodeConfiguration\' .\n";
            
                                    if(empty($ShipDate))
                                            if($aTagShippingCode['TagShippingcode'] == $ShippingServiceName)
                                                $ShipDate = $aTagShippingCode['ShipDate']; 
                                            else
                                                $aErrors[]= "$sDateStamp [$sUser]: ERROR: ShipDate for \'$ShippingServiceName\' is not defined in \'$sShippingCodeConfiguration\' .\n";
            
                                    if(empty($WeightOz))
                                        if($aTagShippingCode['TagShippingcode'] == $ShippingServiceName)
                                            $WeightOz = $aTagShippingCode['WeightOz']; 
                                        else
                                            $aErrors[]= "$sDateStamp [$sUser]: ERROR: WeightOz for \'$ShippingServiceName\' is not defined in \'$sShippingCodeConfiguration\' .\n";
                            }
                        }

                                                             
                             

                        //BULK DATA VALIDATION
                        if(preg_match('/bulk/', strtolower($ShippingMethod)))
                        {
                                $UniqueInternalBulkId = hash('adler32',$BulkID . $BulkCompany . $BulkFullName . $BulkAddress1 . $BulkAddress2 . $BulkCity . $BulkState . $BulkPostalCode . $BulkCountry,false);
                                //ADDRESS_1 NO EMPTY
                                if(empty($BulkAddress1))
                                {
                                    $iErrorsPerRecord++;
                                    //$iNoErrorRecs++;
                                    $ErrorMessage = "";
                                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." bulk address 1 is missing \n";
                                    $aErrors[] = $ErrorMessage;
                                    echo $ErrorMessage;
                                    $Status = "NOK - BULK ADDRESS_1";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error -Missing bulk Address 1";
                                    $HasError= true;
                                    $ProductName = "NOK";
                                    $ShippingServiceName = "NOK";

                                }
                                //CITY NO EMPTY
                                if(empty($BulkCity))
                                {
                                    $iErrorsPerRecord++;
                                    //$iNoErrorRecs++;
                                    $ErrorMessage = "";
                                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." bulk city is missing \n";
                                    $aErrors[] = $ErrorMessage;
                                    echo $ErrorMessage;
                                    $Status = "NOK - BULK CITY";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error - Missing bulk City";
                                    $HasError= true;
                                    $ProductName = "NOK";
                                    $ShippingServiceName = "NOK";

                                }
                                //ZIP NO EMPTY
                                if(empty($BulkPostalCode))
                                {
                                    $iErrorsPerRecord++;
                                    //$iNoErrorRecs++;
                                    $ErrorMessage = "";
                                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." bulk ZIP CODE/Postal Code is missing \n";
                                    $aErrors[] = $ErrorMessage;
                                    echo $ErrorMessage;
                                    $Status = "NOK - BULK ZIP";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error - Missing bulk ZIP/POSTAL Code";
                                    $HasError= true;
                                    $ProductName = "NOK";
                                    $ShippingServiceName = "NOK";

                                }
                                //COUNTRY NO EMPTY
                                if(empty($BulkCountry))
                                {
                                    $iErrorsPerRecord++;
                                    //$iNoErrorRecs++;
                                    $ErrorMessage = "";
                                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." bulk Country is missing \n";
                                    $aErrors[] = $ErrorMessage;
                                    echo $ErrorMessage;
                                    $Status = "NOK - BULK COUNTRY";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error - Missing bulk Country";
                                    $HasError= true;
                                    $ProductName = "NOK";
                                    $ShippingServiceName = "NOK";

                                }
                                if ($Customer == "MESH") {

                                    if($LogoIndicator==null)
                                            {
                                                $iErrorsPerRecord++;
                                                $sError = "";
                                                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." ,the Logo Usage Indicator is empty. \n";
                                                $aErrors[] = $sError;
                                                $Status = "NOK";
                                                $ErrorCode = "306";
                                                $ErrorDescription = "Data validation error";
                                                $bHasError= true;
                                                $ProductName = "NOK";
                                                $ShippingServiceName = "NOK";
                                            }
                                    else if($LogoIndicator == "1") {

                                        if ($LogoFileName != $ImageID) {
                                            $LogoFileName = $ImageID;
                                            $aLogoFiles = glob("$CustomerImagesDir*" . $ImageID . "*");
                                            if ($aLogoFiles) {
                                                echo "$sDateStamp [$sUser]: Logo file with ID: " . $ImageID . " is found in: " . $aLogoFiles[0] . "\n";
                                                $bFileMoved = rename($aLogoFiles[0], $MachineImagesDir . basename($aLogoFiles[0]));
                                                if (!$bFileMoved) {
                                                    echo "$sDateStamp [$sUser]: Logo file with ID: " . $ImageID . " is found in: " . $aLogoFiles[0] . "unable to move to $MachineImagesDir location \n";
                                                } else {
                                                    echo "$sDateStamp [$sUser]: Logo file with ID: " . $ImageID . " is succesfully moved " . $MachineImagesDir . basename($aLogoFiles[0]) . " location \n";
                                                }
                                            } else {
                                                $aLogoFiles = glob("$MachineImagesDir*" . $ImageID . "*");
                                                if ($aLogoFiles) {
                                                    echo "$sDateStamp [$sUser]: Logo file with ID: " . $ImageID . " is found already in folder for Machine: " . $aLogoFiles[0] . "\n";
                                                } else {
                                                    $sError = "";
                                                    $sError = "$sDateStamp [$sUser]: WARNING: In the input file $inputDir ,in record ID: $iRecID, for cardholder " . $EmbName . " ,the logo image with ID: $ImageID is missing. \n";
                                                    $aErrors[] = $sError;
                                                    $Status = "WARNING";
                                                    $ErrorCode = "306";
                                                    $ErrorDescription = "Warning Logo not found";
                                                }

                                            }
                                        }
                                    }
                                }
                                if($Customer == "POMELO")
                                {
                                    if(!isset($Barcode)||empty(trim($Barcode)))
                                    {
                                        $iErrorsPerRecord++;
                                        $sError = "";
                                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." ,the Barcode for BULK Shipment is missing".$Barcode." \n";
                                        $aErrors[] = $sError;
                                        $Status = "NOK";
                                        $ErrorCode = "306";
                                        $ErrorDescription = "Data validation error - Missing Barcode";
                                        $bHasError= true;
                                        $ProductName = "NOK";
                                        $ShippingServiceName = "NOK";
                                    }
                                }

                          


                        }
                        else
                        {
                            if($Customer=="MESH")
                            {
                                if(!(preg_match('/[0-9]{6}-[0-9]{4}/', $CID)))//CID check
                                {
                                    $iErrorsPerRecord++;
                                    //$iNoErrorRecs++;
                                    $sError = "";
                                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." ,the CID is not in correct format (6 digits - 4 digits: 123456-1234), received CID ".$CID." \n";
                                    $aErrors[] = $sError;
                                    echo $sError;
                                    $Status = "NOK";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error - Incorrect CID";
                                    $bHasError= true;
                                    $ProductName = "NOK";
                                    $ShippingServiceName = "NOK";

                                    
                                }
                            }
                                //NAME NO EMPTY
                                if(empty($EmbName))
                                {
                                    $iErrorsPerRecord++;
                                    $ErrorMessage = "";
                                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." name is missing \n";
                                    $aErrors[] = $ErrorMessage;
                                    echo $ErrorMessage;
                                    $Status = "NOK - NAME";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error - Missing Name";
                                    $HasError= true;
                                    $ProductName = "NOK";
                                    $ShippingServiceName = "NOK";

                                }
                                //ADDRESS_1 NO EMPTY
                                if(empty($Address1))
                                {
                                    $iErrorsPerRecord++;
                                    //$iNoErrorRecs++;
                                    $ErrorMessage = "";
                                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." address 1 is missing \n";
                                    $aErrors[] = $ErrorMessage;
                                    echo $ErrorMessage;
                                    $Status = "NOK - ADDRESS_1";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error -Missing Address 1";
                                    $HasError= true;
                                    $ProductName = "NOK";
                                    $ShippingServiceName = "NOK";

                                }
                                //CITY NO EMPTY
                                if(empty($City))
                                {
                                    $iErrorsPerRecord++;
                                    //$iNoErrorRecs++;
                                    $ErrorMessage = "";
                                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." city is missing \n";
                                    $aErrors[] = $ErrorMessage;
                                    echo $ErrorMessage;
                                    $Status = "NOK - CITY";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error - Missing City";
                                    $HasError= true;
                                    $ProductName = "NOK";
                                    $ShippingServiceName = "NOK";

                                }
                                //ZIP NO EMPTY
                                if(empty($PostalCode))
                                {
                                    $iErrorsPerRecord++;
                                    //$iNoErrorRecs++;
                                    $ErrorMessage = "";
                                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." ZIP CODE/Postal Code is missing \n";
                                    $aErrors[] = $ErrorMessage;
                                    echo $ErrorMessage;
                                    $Status = "NOK - ZIP";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error - Missing ZIP/POSTAL Code";
                                    $HasError= true;
                                    $ProductName = "NOK";
                                    $ShippingServiceName = "NOK";

                                }
                                //COUNTRY NO EMPTY
                                if(empty($Country))
                                {
                                    $iErrorsPerRecord++;
                                    //$iNoErrorRecs++;
                                    $ErrorMessage = "";
                                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." Country is missing \n";
                                    $aErrors[] = $ErrorMessage;
                                    echo $ErrorMessage;
                                    $Status = "NOK - COUNTRY";
                                    $ErrorCode = "306";
                                    $ErrorDescription = "Data validation error - Missing Country";
                                    $HasError= true;
                                    $ProductName = "NOK";
                                    $ShippingServiceName = "NOK";

                                }
                         }

                           
                            //TOKEN NO EMPTY
                            if(empty($Token))
                            {
                                $iErrorsPerRecord++;
                                $ErrorMessage = "";
                                $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." card token is missing \n";
                                $aErrors[] = $ErrorMessage;
                                echo $ErrorMessage;
                                $Status = "NOK - TOKEN";
                                $ErrorCode = "306";
                                $ErrorDescription = "Data validation error - Missing Token";
                                $HasError= true;
                                $ProductName = "NOK";
                                $ShippingServiceName = "NOK";
    
                            }
                
                        //PAN VALIDATION
                        if(!preg_match('/\d{1,19}/',$PAN))
                        {

                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $ErrorMessage = "";
                            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." , PAN has incorrect format \n";
                            $aErrors[] = $ErrorMessage;
                            echo $ErrorMessage;
                            $Status = "NOK - PAN";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - PAN format";
                            $HasError= true;
                            $ProductName = "NOK";
                            $ShippingServiceName = "NOK";


                        }

                        if(!preg_match('/[a-zA-Z]{2,3}/',$Country))
                        {
                            $iErrorsPerRecord++;
                            $ErrorMessage = "";
                            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." ,the product ID: ".trim($ProductID)." has incorrect country code. The country code is expected to be 2-Alpha nummeric values. Country code from data: ".trim($aRecord['18'])." \n";
                            $aErrors[] = $ErrorMessage;
                            echo $ErrorMessage;
                            $Status = "NOK";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data Validation Error - Incorrect Country Format";
                            $HasError= true;
                            $ProductName = "NOK";
                            $ShippingServiceName = "NOK";

                        }

                    


                        if(!preg_match('/(usa|us|can|ca|united states)/',strtolower($Country)) && $ProductProp['IntFromFacility']=="TAGPL")
                        {
                            $Facility = "TAGPL";
                            $NumberOfRecordsTAGPL++;
                        }
                        else if(preg_match('/(usa|us|can|ca|united states)/',strtolower($Country)))
                        {
                            $Facility = "TAGUS";
                            $NumberOfRecordsTAGUS++;
                        }

                        if($iErrorsPerRecord>0)
                        {
                            $iNoErrorRecs++;
                            $iErrorsPerRecord=0;
                        }
                        else
                        {
                            $NumberOfGoodRecordsInFile++;
                        }
                    }
                    else
                    {
                        $iNoErrorRecs++;
                        $ErrorMessage = "";
                        $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".trim($EmbName)." with PAN $PANMasked ,the card stock ID: ".trim($CardStockID)." is not defined in Products_configuration.csv. Please, review products configuration. \n";
                        $aErrors[] = $ErrorMessage;
                        echo $ErrorMessage;
                        $Status = "NOK";
                        $ErrorCode = "303";
                        $ErrorDescription = "The card stock ID from the file: ".trim($CardStockID).", is unknown";
                        $HasError= true;
                        $ProductName = "NOK";
                        $ShippingServiceType = "NOK";
                        $ShippingServiceName = "NOK";
                    }
                }
                else
                {
                    $iNoErrorRecs++;
                    $ErrorMessage = "";
                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".trim($EmbName)." with PAN $PANMasked ,the product ID: ".trim($ProductID)." is not defined in Products_configuration.csv. Please, review products configuration. \n";
                    $aErrors[] = $ErrorMessage;
                    echo $ErrorMessage;
                    $Status = "NOK";
                    $ErrorCode = "302";
                    $ErrorDescription = "The product ID from the file: ".trim($ProductID).", is unknown";
                    $HasError= true;
                    $ShippingServiceType = "NOK";
                    $ProductName = "NOK";
                    $ShippingServiceName = "NOK";


                }
            }
            else
            {
                $iNoErrorRecs++;
                $ErrorMessage = "";
                $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".trim($EmbName)." , with PAN $PANMasked the BIN: ".$BIN." is not defined in Products_configuration.csv. Please, review products configuration.\n";
                $aErrors[] = $ErrorMessage;
                echo $ErrorMessage;
                $Status = "NOK";
                $ErrorCode = "301";
                $ErrorDescription = "The BIN from the file: ".$BIN.", is unknown";
                $HasError= true;
                $ShippingServiceType = "NOK";
                $ProductName = "NOK";
                $ShippingServiceName = "NOK";



            }

        $NumberOfBadRecordsInFile = $iNoErrorRecs;
            

         $aMappedData[] = array_merge($aMappedDataBeforeValidation,compact('RecordNo','ProductID','CardStockID','ShippingMethod','ShippingServiceCode',
         'ShippingServiceType','ShippingServiceName','PAN','PANMasked','BIN','BINExtended','PANLast4','Track1','Track1Masked','Track2','Track2Masked',
         'Track2Chip','PSN','CVC2','EmbName','CompanyName','FirstNameT1','LastNameT1','ExpDate_YYMM','ChipData','PANHashed_SHA1','PANHashed_SHA256',
         'DataPrepProfile','BulkID','UniqueInternalBulkId','BulkCompany','BulkFullName','BulkAddress1','BulkAddress2','BulkCity','BulkState','BulkPostalCode','BulkZIPCode',
         'BulkZIPCodeAddOn','BulkCountry','FullName','Address1','Address2','City','State','PostalCode','ZIPCode','ZIPCodeAddOn','Country','Country',
         'FromFullName','FromAddress1','FromAddress2','FromCity','FromState','FromCountry','FromZIPCode','ServiceType','PackageType','WeightOz','ShipDate',
         'ImageType','Reference1','Reference2','Reference3','Reference4','DataMatching','ValidFrom','MemberSince','DDAAccount','Currency','ImageIDFront',
         'ImageIDBack','ExternalCardID','AdditionalField1','AdditionalField2','AdditionalField3','AdditionalField4','AdditionalField5','AdditionalField6',
         'AdditionalField7','AdditionalField8','RecipientEmail','RecipientPhone','FedexAccountNumber','Token','FileDate','FileName','CurrentDate',
         'EmbName2','TrackingNumber','NumberOfRecordsInFile','NumberOfRecordsPerGrouping','NoErrorRecs','Status','ErrorMessage','ErrorCode','ErrorDescription',
         'Facility','HasError','ProductName','Customer','Processor','Barcode','JobTicketNumber'
         ));

    }
        foreach($aMappedData as $aRecords)
        {
            $aMappedDataWithStatistics[] = array_merge($aRecords,compact('NumberOfRecordsTAGUS','NumberOfRecordsTAGPL','NumberOfGoodRecordsInFile','NumberOfBadRecordsInFile'));
}
        setSerialNumber($SerialNumberLocal,$SerialNumberOfDigits,$SerialNumber);

    return $aMappedDataWithStatistics;
}
?>