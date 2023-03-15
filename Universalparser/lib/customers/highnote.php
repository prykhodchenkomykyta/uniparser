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

function datamap_validation_highnote($input, $inputDir, $aConfig)
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
    $MAX_CSV_FIELDS = 55;



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
                if(count($aRecord)!=$MAX_CSV_FIELDS)
                {
                    $iNoErrorRecs++;
                    $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, the CSV file has more than expected fields. File contains possible unescaped comma. Max expected CSV fields: ".$MAX_CSV_FIELDS.", the fields in the record is ".count($aRecord)."\n";
                    $aErrors[] = $ErrorMessage;
                    echo $ErrorMessage;
                    $Status = "NOK";
                    $ErrorCode = "305";
                    $ErrorDescription = "Data Format Error";
                    $HasError= true;
                    $ProductName = "NOK";
                    $ShippingServiceName = "NOK";
                }


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
        $ShippingMethod  = (empty($BulkID)? "ShipMethod": "BulkShipMethod"); //BULK or DTC

        //CALCULATED
        $ShippingServiceType;
        $ShippingServiceName;
        //DATAPREP
        //$PAN = trim($aRecord['1']);
        $PANLength =  strlen($PAN);
        $BIN = substr($PAN,0,6);
        $BINExtended = substr($PAN,0,8);
        $PANLast4 = substr($PAN,-4);
        $Track1EndSentinelPos = strpos($Track1, "?");

        $Track1 = substr($Track1, 0, $Track1EndSentinelPos);
        $Track2EndSentinelPos = strpos($Track2, "?");
        $Track2 =  substr(str_replace(";","",$Track2),0,$Track2EndSentinelPos);
        //$Track2Chip = trim($aRecord['53']);
        $PANMasked = MaskPANData($PAN,$BIN,$PANLength);
        $Track1Masked = (empty($Track1)) ? "" : MaskPANData($Track1,$BIN,$PANLength);
        $Track2Masked = (empty($Track2)) ? "" : MaskPANData($Track2,$BIN,$PANLength);
        $Track2ChipMasked = (empty($Track2Chip)) ? "" : MaskPANData($Track2Chip,$BIN,$PANLength);
        //$PSN = trim($aRecord['10']);
        //$sPINBlock =  trim($aRecord['39']);
        //$CVC2 = trim($aRecord['8']);
        //$iCVV = trim($aRecord['7']);
        //$EmbName = trim($aRecord['5']);
       
        $FirstNameT1 = "";
        $LastNameT1 = "";
        $ExpDate_YYMM = "";
        $ChipData = "$Track1#$Track2#$ICVV#$PSN#$CVC2#\"$EmbName\"#\"$EmbossLine4\"";
        $PANHashed_SHA1 = sha1($PAN);
        $PANHashed_SHA256 = strtoupper(hash("sha256",$PAN, false));
        $DataPrepProfile; ////////////////////////////////////////////#
        //SHIPPING
        // $BulkID = trim($aRecord['20']);
        // $BulkCompany = trim($aRecord['21']);
        // $BulkFullName = trim($aRecord['22']);
        // $BulkAddress1 = trim($aRecord['24']);
        // $BulkAddress2 = trim($aRecord['25']);
        // $BulkCity = trim($aRecord['26']);
        // $BulkState =  trim($aRecord['27']);
        // $BulkPostalCode = trim($aRecord['28']);
        $BulkZIPCode = substr($BulkPostalCode, 0,5);
        $BulkZIPCodeAddOn = empty(substr($BulkPostalCode,5)) ? "" : preg_replace("/-/","",substr(trim($BulkPostalCode),5));
        $BulkCountry =  (strlen($BulkCountry)==2) ? trim($BulkCountry) : convertCountry('alpha3',$BulkCountry,'alpha2');
        // $FullName = trim($aRecord['5']);
        // $Address1 = trim($aRecord['13']);
        // $Address2 = trim($aRecord['14']);
        // $City = trim($aRecord['15']);
        // $State =  trim($aRecord['16']);
        // $PostalCode =  trim($aRecord['17']);
        $ZIPCode = substr($PostalCode ,0,5);
        $ZIPCodeAddOn = empty(substr($PostalCode ,5)) ? "" : preg_replace("/-/","",substr($PostalCode ,5)); //ADDED ZIPAdd-On
        //$Country= trim($aRecord['18']);
        //$CountryAlpha2 = (strlen($Country)==2) ? $Country : convertCountry('alpha3',$Country,'alpha2');
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
        //$CardType = $ProductID;
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
            //ERROR CHECK TO CONFIRM BIN
            if(isset($aBINs[$BIN]))
            {
                //ERROR CHECK TO CONFIRM PRODUCT ID
                if(isset($aBINs[$BIN][trim($ProductID)]))
                {
                    //ERROR CHECK TO CONFIRM CARD STOCK
                    if(isset($aBINs[$BIN][trim($ProductID)][trim($CardStockID)]))
                    {
                        $ProductProp = $aBINs[$BIN][trim($ProductID)][trim($CardStockID)];
                        $Status = "OK";
                        $ErrorCode = "N/A";
                        $ErrorDescription = "";
                        $HasError= false;
                        $Customer = $ProductProp['Customer'];
                        $ProductName = $ProductProp['TagProductID'];
                        $DataPrepProfile = $ProductProp['DPProfileName'];
                        $CardStockIDName = $ProductProp['CardStockID'];
                        $FromFullName =   !empty($ProductProp["FromFullName"]) ? $ProductProp["FromFullName"] : $FromFullName;
                        $FromAddress1 =   !empty($ProductProp["FromAddress1"]) ? $ProductProp["FromAddress1"] : $FromAddress1;
                        $FromAddress2 =   !empty($ProductProp["FromAddress2"]) ? $ProductProp["FromAddress2"] : $FromAddress2;
                        $FromCity =  !empty($ProductProp["FromCity"]) ?  $ProductProp["FromCity"] : $FromCity;
                        $FromState=   !empty($ProductProp["FromState"]) ? $ProductProp["FromState"] : $FromState;
                        $FromCountry =   !empty($ProductProp["FromCountry"]) ?  $ProductProp["FromCountry"] : $FromCountry;
                        $FromZIPCode =   !empty($ProductProp["FromZIPCode"]) ?  $ProductProp["FromZIPCode"] : $FromZIPCode;
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
                    
                        if(preg_match('/\d{1,3}/',$Country))
                            $Country = convertCountry('id',$Country,'alpha2');
                        else if(preg_match('/[A-Za-z]{3}/',$Country))
                            $Country = convertCountry('alpha3',$Country,'alpha2');
                        else if(preg_match('/[A-Za-z]{3,}/',$Country))
                            $Country = convertCountry('name',$Country,'alpha2');
                
                
                        if(preg_match('/\d{1,3}/',$BulkCountry))
                            $BulkCountry = convertCountry('id',$BulkCountry,'alpha2');
                        else if(preg_match('/[A-Za-z]{3}/',$BulkCountry))
                            $BulkCountry = convertCountry('alpha3',$BulkCountry,'alpha2');
                        else if(preg_match('/[A-Za-z]{3,}/',$Country))
                       $BulkCountry = convertCountry('name',$BulkCountry,'alpha2');

                       $PostalCode = str_replace([' ','-'], '', $PostalCode);
                       $BulkPostalCode = str_replace([' ','-'], '', $BulkPostalCode);



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
                        //TRACK 1 VALIDATION
                        //%?B\d{1,19}\^[\\[\w\s.()\-$\/\]]{2,26}\^([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\??
                        if(!preg_match('/%?B\d{1,19}\^(?=[A-Za-z0-9 .()\/-]{2,26}\^)[A-Za-z0-9 .()-]*\/[A-Za-z0-9 .()-]*\^([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\??/',$Track1))
                        {

                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $ErrorMessage = "";
                            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." , Track1 data have incorrect magnetic stripe format, received value: ".$Track1Masked." \n";
                            $aErrors[] = $ErrorMessage;
                            echo $ErrorMessage;
                            $Status = "NOK - TRACK_1";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - Magnetic Stripe Track1 format";
                            $HasError= true;
                            $ProductName = "NOK";
                            $ShippingServiceName = "NOK";


                        }
                        //TRACK 2 VALIDATION
                        if(!preg_match('/;?\d{0,19}=([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\\??/',$Track2))
                        {


                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $ErrorMessage = "";
                            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ".$PANMasked." , Track2 data have incorrect magnetic stripe format, received value: ".$Track2Masked." \n";
                            $aErrors[] = $ErrorMessage;
                            echo $ErrorMessage;
                            $Status = "NOK - TRACK_2";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - Magnetic Stripe Track2 format";
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