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

function mapping_marqeta($aInputFile)
{


     $aInputFile = file($aInputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
     $iRecNo = 0;
     $aRecords = array();
     $aRecords[$iRecNo] = array();
     $aHeader = array();
     foreach ($aInputFile as $iRecID => $aRecord)
     {
        $sBOM = pack('H*','EFBBBF');        
        $aRecord = preg_replace("/^$sBOM/", '', $aRecord);

            switch(strtolower(substr($aRecord,0,2)))
            {
                case "fh":
                        $aHeader['fh'] =$aRecord; 
                    break;
                case "bh":
                        $aHeader['bh'] =$aRecord; 
                    break;
                case "dr":
                        $aHeader['dr'] = $aRecord;
                        $aRecords[$iRecNo] = $aHeader;
                        $iRecNo++;            
                    break;  
                case "bf":                  
                    break;                  
                case "ff":
                    break;
            }
    }

    return $aRecords;
}


function datamap_validation_marqeta($input, $inputDir, $aConfig)
{

    $sDateStamp = date('Y-m-d H:i:s', time());
    $sUser = get_current_user();
    $sProductConfigFile = $aConfig['ProductConfigurationPath'];
    $sDataMapConfiguration = $aConfig['DataMapConfiguration'];
    $sShippingCodeConfiguration = $aConfig['ShippingCodeConfiguration'];
    $sCompositeFieldReference1Dir = $aConfig['CompositeField'];
    $sProcessor = trim($aConfig['Processor']);
    $sSupplementalFile = trim($aConfig['SupplementalFiles']);
    $sSupplementalFileSuffix = trim($aConfig['SupplementalFileSuffix']);


    $aBINs = getProductsList($sProductConfigFile);



    global $sSerialNumberurl;
    global $SerialNumberLocal;
    global $SerialNumberOfDigits;


    echo "\n$sDateStamp [$sUser]: Mapping Data: $inputDir \n";
    $aMappedDataBeforeValidation = array();

    /*SERIAL NUMBER*/
    $SerialNumber = getSerialNumber($SerialNumberLocal);
    $SerialNumber = str_pad($SerialNumber,$SerialNumberOfDigits,'0',STR_PAD_LEFT);   
    //INIT VALUES
    $ProductID = "";
    $CardStockID = "";
    $OrderType = "";
    $ShippingMethod = "";
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
    $NumberOfRecordsTAGPL = 0;
    $Processor = "";
    $LogoFileName = "";
    $LogoIndicator = "";
    $DateReceived = "";
    $CARD_STOCK_ID = "NA";



    $iNumberOfRecords = count($input);
    $sFileName = basename($inputDir);
    if ($iNumberOfRecords == 0) {
        echo "\n$sDateStamp [$sUser]: ERROR: The $sFileName does not contain any data, the file is empty.  \n";
        return false;
    }

    $RecordNo = 0;
    $LastBatch = 0;
    $iNoErrorRecsPerBatch = 0;
    foreach ($input as $iRecID => $aRecord) {
        $aRecord_remapped["h1"] = preg_replace("/^fh/i","h1",$aRecord["fh"]);  // qolo style
        $aRecord_remapped["h2"] = preg_replace("/^bh/i","h2",$aRecord["bh"]);  //
        $aRecord_remapped["d4"] = preg_replace("/^dr/i","d4",$aRecord["dr"]);  //

        $aMappedDataBeforeValidation = mapDataFromConfig($sDataMapConfiguration, $aRecord_remapped, $sProcessor);
        extract($aMappedDataBeforeValidation,EXTR_OVERWRITE);

        $RecordNo++;
        $SerialNumber++;

        if($LastBatch != $BulkID)
        {
            $LastBatch = $BulkID;
            $iNoErrorRecsPerBatch = 0;

        }

        $PANLength =  strlen($PAN);
        $BIN = substr($PAN,0,6);
        $BINExtended = substr($PAN,0,8);
        $PANLast4 = substr($PAN,-4);
        if(isset($aBINs[$BINExtended]))
        {
            $BIN = $BINExtended;
            $bIsExtendedBINused = true;
        }
        $Track1 = trim($Track1);
        $Track2_Chip = trim($Track2);
        $Track2 = trim(substr($Track2,1,strlen($Track2)-2));     // found in older version


        $iPanPosition = strpos($PAN,$BIN);
        $iBINln = 6;
        $iPANln = strlen($PAN);
        $iMaskedCharsln = abs($iPANln-4-$iBINln);
        if($iPanPosition!==false)
        {
            $PANMasked = substr_replace($PAN,"XXXXXX",$iPanPosition+$iBINln,$iMaskedCharsln);
        }
        else
        {
            $PANMasked = substr($PAN, -4);
        }

        # CUSTOMIZATION BASED ON REQUESTED OUTPUT
        $DateReceived = date("Y-m-d",strtotime($DateReceived));
        //$Filename = rtrim($sFileName, ".txt");

        //ERROR CHECK TO CONFIRM BIN
        if (isset($aBINs[$BIN])) {
            //ERROR CHECK TO CONFIRM PRODUCT ID
            if (isset($aBINs[$BIN][trim($ProductID)])) {
                //ERROR CHECK TO CONFIRM CARD STOCK
                if (isset($aBINs[$BIN][trim($ProductID)][trim($CARD_STOCK_ID)])) {
                    $ProductProp = $aBINs[$BIN][trim($ProductID)][trim($CARD_STOCK_ID)];
                    $Status = "SUCCESS";
                    $ErrorCode = "0";
                    $ErrorDescription = "";
                    $bHasError = false;

                    $iErrorsPerRecord = 0;
                    if ($OrderType == "00001") {
                        if (!isset($ProductProp['ShipMethod'.'_'.$ShippingServiceCode])) {
                            $iErrorsPerRecord++;
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BulkID record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " , the shipping method $OrderType and it's shipping service $ShippingServiceCode is invalid. The valid shipping service for product " . $ProductProp['TagProductID'] . " and shipment method 00001 Standard Shipment are 00-" . $ProductProp['ShipMethod_00'] . ", 11-" . $ProductProp['ShipMethod_11'] . ", 12-" . $ProductProp['ShipMethod_12'] . ", 13-" . $ProductProp['ShipMethod_13'] . ", and for 00002 Bulk Shipment are 10-" . $ProductProp['BulkShipMethod_10'] . ", 11-" . $ProductProp['BulkShipMethod_11'] . ", 12-" . $ProductProp['BulkShipMethod_12'] . ", 13-" . $ProductProp['BulkShipMethod_13'] . ", each is configured in products_configuration_Marqeta.csv  \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK";
                            $ErrorCode = "307";
                            $ErrorDescription = "Wrong Shipping Method";
                            $bHasError = true;
                            $ProductProp['TagProductID'] = "NOK";
                            $ProductProp['ShipMethod'.'_'.$ShippingServiceCode] = "NOK";
                        }
                    } else if ($OrderType == "00002") {
                        if (!isset($ProductProp['BulkShipMethod'.'_'.$ShippingServiceCode])) {
                            $iErrorsPerRecord++;
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BulkID  record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " , the shipping method $OrderType and it's shipping service $ShippingServiceCode is invalid. The valid shipping service for product " . $ProductProp['TagProductID'] . " and shipment method 00001 Standard Shipment are 00-" . $ProductProp['ShipMethod_00'] . ", 11-" . $ProductProp['ShipMethod_11'] . ", 12-" . $ProductProp['ShipMethod_12'] . ", 13-" . $ProductProp['ShipMethod_13'] . ", and for 00002 Bulk Shipment are 10-" . $ProductProp['BulkShipMethod_10'] . ", 11-" . $ProductProp['BulkShipMethod_11'] . ", 12-" . $ProductProp['BulkShipMethod_12'] . ", 13-" . $ProductProp['BulkShipMethod_13'] . ", each is configured in products_configuration_Marqeta.csv  \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK";
                            $ErrorCode = "307";
                            $ErrorDescription = "Wrong Shipping Service";
                            $bHasError = true;
                            $ProductProp['TagProductID'] = "NOK";
                            $ProductProp['BulkShipMethod'.'_'.$ShippingServiceCode] = "NOK";

                        }
                    } else {
                        $iErrorsPerRecord++;
                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BulkID  record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " , the shipping method $OrderType is invalid. Valid options are 00001 for Standard Shipment and 00002 for Bulk Shipment. \n";
                        $aErrors[] = $sError;
                        echo $sError;
                        $Status = "NOK";
                        $ErrorCode = "307";
                        $ErrorDescription = "Wrong Shipping Method";
                        $bHasError = true;
                        $ProductProp['TagProductID'] = "NOK";
                        $ProductProp['ShipMethod'.'_'.$ShippingServiceCode] = "NOK";

                    }

                    //DATA VALIDATION
                    if (!preg_match('/%?B\d{1,19}\^(?=[A-Za-z0-9 .()\/-]{2,26}\^)[A-Za-z0-9 .()-]*\/[A-Za-z0-9 .()-]*\^([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\??/', $Track1)) {

                        $iPanPosition = strpos(($Track1), $BIN);
                        if ($iPanPosition !== false) {
                            $Track1Masked = substr_replace($Track1, "XXXXXX", $iPanPosition + strlen($BIN), $iMaskedCharsln);
                        } else {
                            $Track1Masked = "unable to mask the track data - view not allowed";
                        }
                        $iErrorsPerRecord++;
                        $sError = "";
                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BulkID  record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " , Track1 data have incorrect magnetic stripe format, received value: " . $Track1Masked . " \n";
                        $aErrors[] = $sError;
                        echo $sError;
                        $Status = "NOK";
                        $ErrorCode = "306";
                        $ErrorDescription = "Data validation error - Magnetic Stripe Track1 format";
                        $bHasError = true;
                        $ProductProp['ProductID'] = "NOK";
                        $ProductProp['ShipMethod'.'_'.$ShippingServiceCode] = "NOK";
                    }
                } else {
                    $iNoErrorRecs++;
                    $iNoErrorRecsPerBatch++;
                    $sError = "";
                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BulkID  record ID: $RecordNo, for cardholder " . $EmbName . " with PAN $PANMasked ,the card stock ID: " . trim($CARD_STOCK_ID) . " is not defined in Products_configuration.csv. Please, review products configuration. \n";
                    $aErrors[] = $sError;
                    echo $sError;
                    $Status = "NOK";
                    $ErrorCode = "303";
                    $ErrorDescription = "The card stock ID from the file: " . $EmbName . ", is unknown";
                    $bHasError = true;
                    $ProductProp['ProductID'] = "NOK";
                    $ProductProp['ShipMethod'.'_'.$ShippingServiceCode] = "NOK";
                    //$ProductID = trim($PRODUCT_ID);
                }

                if ($iErrorsPerRecord > 0) {
                    $iNoErrorRecs++;
                    $iNoErrorRecsPerBatch++;
                    $iErrorsPerRecord = 0;
                }
            } else {
                $iNoErrorRecs++;
                $iNoErrorRecsPerBatch++;
                $sError = "";
                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BulkID  record ID: $RecordNo, for cardholder " . $EmbName . " with PAN $PANMasked ,the product ID: " . trim($ProductID) . " is not defined in Products_configuration_Marqeta.csv. Please, review products configuration. \n";
                $aErrors[] = $sError;
                echo $sError;
                $Status = "NOK";
                $ErrorCode = "302";
                $ErrorDescription = "The product ID from the file: " . trim($ProductID) . ", is unknown";
                $bHasError = true;
                $ProductProp['ProductID'] = "NOK";
                $ProductProp['ShipMethod'.'_'.$ShippingServiceCode] = "NOK";
                //$ProductID = trim($PRODUCT_ID);
            }
        } else {
            $iNoErrorRecs++;
            $iNoErrorRecsPerBatch++;
            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in Batch ID $BulkID  record ID: $RecordNo, for cardholder " . $EmbName . " , with PAN $PANMasked the BIN: " . $BIN . " is not defined in Products_configuration.csv. Please, review products configuration.\n";
            $aErrors[] = $sError;
            echo $sError;
            $Status = "NOK";
            $ErrorCode = "301";
            $ErrorDescription = "The BIN from the file: " . $BIN . ", is unknown";
            $bHasError = true;
            $ProductProp['ProductID'] = "NOK";
            $ProductProp['ShipMethod'.'_'.$ShippingServiceCode] = "NOK";
            //$ProductID = trim($PRODUCT_ID);
        }

        //$ShippingMethod = ($OrderType == "00002")? "BULK_".$ProductProp['BulkShipMethod_'.$ShippingServiceCode]:$ProductProp['ShipMethod_'.$ShippingServiceCode];
        $ShippingMethod = ($OrderType == "00002")? $ProductProp['BulkShipMethod_'.$ShippingServiceCode]:$ProductProp['ShipMethod_'.$ShippingServiceCode];

        if(!isset($ShippingMethod))
        {
            $iErrorsPerRecord++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir , in record ID: $RecordNo, for cardholder ".$EmbName." with PAN ". $PANMasked ." , the shipping method $ShippingMethod and it's shipping service $ShippingServiceCode is invalid. The valid shipping service for product ".$ProductName." are configured in ".basename($sProductConfigFile)."  \n";
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
            // $ShippingServiceName= $ProductProp[$ShippingMethod.'_'.$ShippingServiceCode];
            $aTagShippingCodes = getProductsList($sShippingCodeConfiguration);
            foreach($aTagShippingCodes as $aTagShippingCode)
            {
                    if(empty($ServiceType))
                        if($aTagShippingCode['TagShippingcode'] == $ShippingMethod)
                            $ServiceType = $aTagShippingCode['ServiceType']; 
                        else
                            $aErrors[]= "$sDateStamp [$sUser]: ERROR: ServiceType for \'$ShippingMethod\' is not defined in \'$sShippingCodeConfiguration\' .\n";

                    if(empty($PackageType))
                            if($aTagShippingCode['TagShippingcode'] == $ShippingMethod)
                                $PackageType = $aTagShippingCode['PackageType']; 
                            else
                                $aErrors[]= "$sDateStamp [$sUser]: ERROR: PackageType for \'$ShippingMethod\' is not defined in \'$sShippingCodeConfiguration\' .\n";

                    if(empty($ShipDate))
                            if($aTagShippingCode['TagShippingcode'] == $ShippingMethod)
                                $ShipDate = $aTagShippingCode['ShipDate']; 
                            else
                                $aErrors[]= "$sDateStamp [$sUser]: ERROR: ShipDate for \'$ShippingMethod\' is not defined in \'$sShippingCodeConfiguration\' .\n";

                    if(empty($WeightOz))
                        if($aTagShippingCode['TagShippingcode'] == $ShippingMethod)
                            $WeightOz = $aTagShippingCode['WeightOz']; 
                        else
                            $aErrors[]= "$sDateStamp [$sUser]: ERROR: WeightOz for \'$ShippingMethod\' is not defined in \'$sShippingCodeConfiguration\' .\n";
            }
        }

        $DayIncrement = intval($ShipDate);
        $ShippingDate =  date('Y-m-d', strtotime($DateReceived. ' + '. $DayIncrement . ' days'));

        //BULK DATA VALIDATION
        if (preg_match('/bulk/', strtolower($ShippingMethod))) {
            $UniqueInternalBulkId = hash('adler32', $BulkID . $BulkCompany . $BulkFullName . $BulkAddress1 . $BulkAddress2 . $BulkCity . $BulkState . $BulkPostalCode . $BulkCountry, false);
            //ADDRESS_1 NO EMPTY
            if (empty($BulkAddress1)) {
                $iErrorsPerRecord++;
                //$iNoErrorRecs++;
                $ErrorMessage = "";
                $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " bulk address 1 is missing \n";
                $aErrors[] = $ErrorMessage;
                echo $ErrorMessage;
                $Status = "NOK - BULK ADDRESS_1";
                $ErrorCode = "306";
                $ErrorDescription = "Data validation error -Missing bulk Address 1";
                $HasError = true;
                $ProductName = "NOK";
                $ShippingServiceName = "NOK";

            }
            //CITY NO EMPTY
            if (empty($BulkCity)) {
                $iErrorsPerRecord++;
                //$iNoErrorRecs++;
                $ErrorMessage = "";
                $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " bulk city is missing \n";
                $aErrors[] = $ErrorMessage;
                echo $ErrorMessage;
                $Status = "NOK - BULK CITY";
                $ErrorCode = "306";
                $ErrorDescription = "Data validation error - Missing bulk City";
                $HasError = true;
                $ProductName = "NOK";
                $ShippingServiceName = "NOK";

            }
            //ZIP NO EMPTY
            if (empty($BulkPostalCode)) {
                $iErrorsPerRecord++;
                //$iNoErrorRecs++;
                $ErrorMessage = "";
                $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " bulk ZIP CODE/Postal Code is missing \n";
                $aErrors[] = $ErrorMessage;
                echo $ErrorMessage;
                $Status = "NOK - BULK ZIP";
                $ErrorCode = "306";
                $ErrorDescription = "Data validation error - Missing bulk ZIP/POSTAL Code";
                $HasError = true;
                $ProductName = "NOK";
                $ShippingServiceName = "NOK";

            }
            //COUNTRY NO EMPTY
            if (empty($BulkCountry)) {
                $iErrorsPerRecord++;
                //$iNoErrorRecs++;
                $ErrorMessage = "";
                $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " bulk Country is missing \n";
                $aErrors[] = $ErrorMessage;
                echo $ErrorMessage;
                $Status = "NOK - BULK COUNTRY";
                $ErrorCode = "306";
                $ErrorDescription = "Data validation error - Missing bulk Country";
                $HasError = true;
                $ProductName = "NOK";
                $ShippingServiceName = "NOK";

            }

        }

#################################


        //TOKEN NO EMPTY
        if (empty($Token)) {
            $iErrorsPerRecord++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " card token is missing \n";
            $aErrors[] = $ErrorMessage;
            echo $ErrorMessage;
            $Status = "NOK - TOKEN";
            $ErrorCode = "306";
            $ErrorDescription = "Data validation error - Missing Token";
            $HasError = true;
            $ProductName = "NOK";
            $ShippingServiceName = "NOK";

        }
        //NAME NO EMPTY
        if (empty($EmbName)) {
            $iErrorsPerRecord++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " name is missing \n";
            $aErrors[] = $ErrorMessage;
            echo $ErrorMessage;
            $Status = "NOK - NAME";
            $ErrorCode = "306";
            $ErrorDescription = "Data validation error - Missing Name";
            $HasError = true;
            $ProductName = "NOK";
            $ShippingServiceName = "NOK";

        }
        //ADDRESS_1 NO EMPTY
        if (empty($Address1)) {
            $iErrorsPerRecord++;
            //$iNoErrorRecs++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " address 1 is missing \n";
            $aErrors[] = $ErrorMessage;
            echo $ErrorMessage;
            $Status = "NOK - ADDRESS_1";
            $ErrorCode = "306";
            $ErrorDescription = "Data validation error -Missing Address 1";
            $HasError = true;
            $ProductName = "NOK";
            $ShippingServiceName = "NOK";

        }
        //CITY NO EMPTY
        if (empty($City)) {
            $iErrorsPerRecord++;
            //$iNoErrorRecs++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " city is missing \n";
            $aErrors[] = $ErrorMessage;
            echo $ErrorMessage;
            $Status = "NOK - CITY";
            $ErrorCode = "306";
            $ErrorDescription = "Data validation error - Missing City";
            $HasError = true;
            $ProductName = "NOK";
            $ShippingServiceName = "NOK";

        }
        //ZIP NO EMPTY
        if (empty($PostalCode)) {
            $iErrorsPerRecord++;
            //$iNoErrorRecs++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " ZIP CODE/Postal Code is missing \n";
            $aErrors[] = $ErrorMessage;
            echo $ErrorMessage;
            $Status = "NOK - ZIP";
            $ErrorCode = "306";
            $ErrorDescription = "Data validation error - Missing ZIP/POSTAL Code";
            $HasError = true;
            $ProductName = "NOK";
            $ShippingServiceName = "NOK";

        }
        //COUNTRY NO EMPTY
        if (empty($Country)) {
            $iErrorsPerRecord++;
            //$iNoErrorRecs++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " Country is missing \n";
            $aErrors[] = $ErrorMessage;
            echo $ErrorMessage;
            $Status = "NOK - COUNTRY";
            $ErrorCode = "306";
            $ErrorDescription = "Data validation error - Missing Country";
            $HasError = true;
            $ProductName = "NOK";
            $ShippingServiceName = "NOK";

        }
        //TRACK 1 VALIDATION
        //%?B\d{1,19}\^[\\[\w\s.()\-$\/\]]{2,26}\^([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\??
        if (!preg_match('/%?B\d{1,19}\^(?=[A-Za-z0-9 .()\/-]{2,26}\^)[A-Za-z0-9 .()-]*\/[A-Za-z0-9 .()-]*\^([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\??/', $Track1)) {

            $iErrorsPerRecord++;
            //$iNoErrorRecs++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " , Track1 data have incorrect magnetic stripe format, received value: " . $Track1Masked . " \n";
            $aErrors[] = $ErrorMessage;
            echo $ErrorMessage;
            $Status = "NOK - TRACK_1";
            $ErrorCode = "306";
            $ErrorDescription = "Data validation error - Magnetic Stripe Track1 format";
            $HasError = true;
            $ProductName = "NOK";
            $ShippingServiceName = "NOK";


        }
        //TRACK 2 VALIDATION
        if (!preg_match('/;?\d{0,19}=([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\\??/', $Track2)) {


            $iErrorsPerRecord++;
            //$iNoErrorRecs++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " , Track2 data have incorrect magnetic stripe format, received value: " . $Track2Masked . " \n";
            $aErrors[] = $ErrorMessage;
            echo $ErrorMessage;
            $Status = "NOK - TRACK_2";
            $ErrorCode = "306";
            $ErrorDescription = "Data validation error - Magnetic Stripe Track2 format";
            $HasError = true;
            $ProductName = "NOK";
            $ShippingServiceName = "NOK";


        }
        //PAN VALIDATION
        if (!preg_match('/\d{1,19}/', $PAN)) {

            $iErrorsPerRecord++;
            //$iNoErrorRecs++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " with PAN " . $PANMasked . " , PAN has incorrect format \n";
            $aErrors[] = $ErrorMessage;
            echo $ErrorMessage;
            $Status = "NOK - PAN";
            $ErrorCode = "306";
            $ErrorDescription = "Data validation error - PAN format";
            $HasError = true;
            $ProductName = "NOK";
            $ShippingServiceName = "NOK";


        }

        if (!preg_match('/[a-zA-Z]{2,3}/', $Country)) {
            $iErrorsPerRecord++;
            $ErrorMessage = "";
            $ErrorMessage = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $RecordNo, for cardholder " . $EmbName . " ,the product ID: " . trim($ProductID) . " has incorrect country code. The country code is expected to be 2-Alpha nummeric values. Country code from data: " . trim($aRecord['18']) . " \n";
            $aErrors[] = $ErrorMessage;
            echo $ErrorMessage;
            $Status = "NOK";
            $ErrorCode = "306";
            $ErrorDescription = "Data Validation Error";
            $HasError = true;
            $ProductName = "NOK";
            $ShippingServiceName = "NOK";

        }


        $TimeForFilename = date('m:d:Y-H:i:s', time());
        $ReceivedDate = date('Y:m:d', time());
        $ReceivedTime = date('H:i:s', time());
        $ReceivedDate = str_replace(":","",$ReceivedDate);
        $ReceivedTime = str_replace(":","",$ReceivedTime);
        $TimeForFilename = str_replace(":","",$TimeForFilename);
        $sFilenameBase = $sFileName . '-' . $TimeForFilename;
        $WorkOrderName = "not provided";
        $Rank = "not provided";
        $TrackingNo = "not provided";
        $JobID = "not provided";
        $RankInJob = "not provided";
        $SequenceNumber = "not provided";
        $Account = "not provided";
        $ProductionProfile = "not provided";

        if ($OrderType == "00001"){
            $RecipientAddress1 = $Address1;
            $RecipientAddress2 = $Address2;
            $RecipientCity = $City;
            $RecipientState = $State;
            $RecipientZip = $PostalCode;
            $RecipientCountry = $Country;
        }
        else{
            $RecipientAddress1 = $BulkAddress1;
            $RecipientAddress2 = $BulkAddress2;
            $RecipientCity = $BulkCity;
            $RecipientState = $BulkState;
            $RecipientZip = $BulkPostalCode;
            $RecipientCountry = $BulkCountry;
        }
        $Address = $RecipientAddress1.' '.$RecipientAddress2.' '.$RecipientCity.' '.$RecipientState.' '.$RecipientZip.' '.$RecipientCountry;
        #################################
        $aMappedData[] = array_merge($aMappedDataBeforeValidation,compact('RecordNo','ProductID','CardStockID','ShippingMethod','ShippingServiceCode',
        'ShippingServiceType','ShippingServiceName','PAN','PANMasked','BIN','BINExtended','PANLast4','Track1','Track1Masked','DateReceived','sFileName', 'Status',
        'ErrorCode','ErrorDescription', 'ShippingDate', 'sFilenameBase', 'WorkOrderName', 'Rank', 'TrackingNo', 'ShippingMethod', 'PackageType','RecipientAddress1','RecipientAddress2',
        'RecipientCity','RecipientState','RecipientZip','RecipientCountry','JobID','RankInJob','SequenceNumber','Account','ErrorDescription','Address','ProductionProfile','ReceivedDate',
        'ReceivedTime'));

    }



    return $aMappedData;
}
?>