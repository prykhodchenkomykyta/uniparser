<?php
function ConfirmationReportErrorCheckPrivacy($input, $inputDir)
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
    global $sProductConfigFile;
    global $iNumberOfIntRecords;
    global $sShipmentReportDir;
    global $sIntShipmentDir;
    global $sTmpDir;



    /*SERIAL NUMBER*/
    $SerialNumber = getSerialNumber($SerialNumberLocal,$SerialNumberOfDigits);
    $SerialNumber = str_pad($SerialNumber,$SerialNumberOfDigits,'0',STR_PAD_LEFT);        
    
    $aFilesWritingStatus = [];
    $aInternationalShipmentOutputData = array();

    $Status = "";
    $ErrorCode = "";
    $ErrorDescription = "";
    $sCustomerName = "";
    $bHasError = false;
    $bIsInternational = false;

    echo "\n$sDateStamp [$sUser]: Error Checking Starts: $inputDir \n";

    //SUPPORT VARIABLES
    $aFilesWritingStatus = [];
    $iNoErrorRecs = 0;
    $iNumberOfRecords = 0;

    $iPanPosition ="";
    $sMaskedPAN = "";
    $sMaskedTrack1 ="";
   
    $iNumberOfRecords = count($input);
    $sFileName =  basename($inputDir);
    if($iNumberOfRecords==0)
    {
        echo "\n$sDateStamp [$sUser]: ERROR: The $sFileName does not contain any data, the file is empty.  \n";
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
        $SHIPPING_ALIAS = "";
       
            $iRecordNo++;
            //VALIDATION DATA
            $sPAN = trim($aRecord['1']);
            $sBIN = substr($sPAN,0,6);
            $sBINExtended = substr($sPAN,0,8);
            $PAN4 =  substr($sPAN,-4);
            $Facility = "";
            //File Format Validation
            if(count($aRecord)!=MAX_CSV_FIELDS_QRAILS)
            {
                $iNoErrorRecs++;
                $sError = "";
                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, the CSV file has more than expected fields. File contains possible unescaped comma. Max expected CSV fields: ".MAX_CSV_FIELDS_QRAILS.", the fields in the record is ".count($aRecord)."\n"; 
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
                $sFileName;
                $CurrentDate = date('Ymd');

    
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
                $Country = (strlen(trim($aRecord['18']))==2) ? trim($aRecord['18']) : convertCountry('alpha3',$aRecord['18'],'alpha2');
                $sEmbName = trim($aRecord['5']);


                $ShipSuffix = "";
                //$sBIN;
                //$Status;
                //$ErrorCode;
                //$ErrorDescription;
                $DateReceived = "N/A";
                //$DateReceived = filemtime($sOriginalFile.str_replace("emb","pgp",$sProcessedFilename));
                $CardType = $ProductID;
                $PAN4 = substr($sPAN, -4);
                $san2 = "";
                $name1 = $sEmbName;
                $name2 = "";
                $Address1 = trim($aRecord['13']);
                $Address2 = trim($aRecord['14']);
                $City = trim($aRecord['15']);
                $State =  trim($aRecord['16']);
                $ZIPCode =  trim($aRecord['17']);
                $Country = (strlen(trim($aRecord['18']))==2) ? trim($aRecord['18']) : convertCountry('alpha3',$aRecord['18'],'alpha2');
                $aConfirmationReportOutputData[] = array($Token,$sFileName,"$ShipSuffix",$sBIN,$Status,"$ErrorCode","$ErrorDescription",$DateReceived,$CardType,$PAN4,$san2,"$name1","$name2","$Address1","$Address2",$City,$State,$ZIPCode,$Country,$Facility);
                $aConfirmationReportHeader = array("Token", "FileName", "ShipSuffix","BIN","Status", "ErrorCode", "ErrorDescription", "DataReceived","CardType", "PAN4","san2","Name1","Name2","Address1","Address2","City","State","ZipCode","Country","Facility");
   
                if($iNumberOfRecords==$iNoErrorRecs)
                {
                    echo "$sDateStamp [$sUser]: ERROR: All the records in  $inputDir contains error, therefor this file cannot be processed.\n";
                    writeReport($aConfirmationReportOutputData, $sFileName, $aConfirmationReportHeader, $sConfirmationReportDir, "CONFIRMATION REPORT",".conf_rep.csv");
                    return false;
                }
                else if($iNumberOfRecords==($iNumberOfIntRecords+$iNoErrorRecs))
                {
                        writeReport($aConfirmationReportOutputData, $sFileName, $aConfirmationReportHeader, $sConfirmationReportDir, "CONFIRMATION REPORT",".conf_rep.csv");
                        echo "$sDateStamp [$sUser]: WARNING: Records in  $inputDir contains error, and international records\n";
                        return false;
                }


                if($bHasError)
                {
                        //DO NOT WRITE RECORD TO REST OF THE FILE
                        unset($input[$iRecID]);
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
            $Country = (strlen(trim($aRecord['18']))==2) ? trim($aRecord['18']) : convertCountry('alpha3',$aRecord['18'],'alpha2');

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
                        $Status = "OK";
                        $ErrorCode = "N/A";
                        $ErrorDescription = "N/A";
                        $bHasError= false;
                        $sCustomerName = $aBINs[$sBIN]['Customer'];
                  

                        $iErrorsPerRecord = 0;
                        if($SHIPPING_METHOD=="DTC")
                        {
                            if(!isset($ProductProp['ShippingMethods'][$SHIPPING_SERVICE]))
                            {
                                $iErrorsPerRecord++;
                                $sError = "";
                                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir , in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , the shipping method $SHIPPING_METHOD and it's shipping service $SHIPPING_SERVICE is invalid. The valid shipping service for product ".$ProductProp['Product']." are configured in ".basename($sProductConfigFile)."  \n";
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
                            else
                            {
                                $SHIPPING_ALIAS = $ProductProp['ShippingMethods'][$SHIPPING_SERVICE];
                            }
                        }
                        else if(preg_match('/BULK/',$SHIPPING_METHOD))
                        {
                            if(!isset($ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE]))
                            {
                                $iErrorsPerRecord++;
                                $sError = "";
                                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir , in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , the shipping method $SHIPPING_METHOD and it's shipping service $SHIPPING_SERVICE is invalid. The valid shipping service for product ".$ProductProp['Product']." are configured in ".basename($sProductConfigFile)."  \n";
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
                            else
                            {
                                $SHIPPING_ALIAS = $ProductProp['ShippingMethodsBulk'][$SHIPPING_SERVICE];
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
                        $iErrorsPerRecord = 0;
                        //TOKEN NO EMPTY
                        if(empty(trim($aRecord['0'])))
                        {
                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." card token is missing \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK - TOKEN";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - Missing Token";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                        }
                        //NAME NO EMPTY
                        if(empty(trim($aRecord['5'])))
                        {
                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." name is missing \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK - NAME";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - Missing Name";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                        }
                        //ADDRESS_1 NO EMPTY
                        if(empty(trim($aRecord['13'])))
                        {
                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." address 1 is missing \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK - ADDRESS_1";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error -Missing Address 1";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                        }
                        //CITY NO EMPTY
                        if(empty(trim($aRecord['15'])))
                        {
                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." city is missing \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK - CITY";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - Missing City";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                        }
                        //ZIP NO EMPTY
                        if(empty(trim($aRecord['17'])))
                        {
                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." ZIP CODE/Postal Code is missing \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK - ZIP";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - Missing ZIP/POSTAL Code";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                        }
                        //COUNTRY NO EMPTY
                        if(empty(trim($aRecord['18'])))
                        {
                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." Country is missing \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK - COUNTRY";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - Missing Country";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                        }
                        //TRACK 1 VALIDATION
                        if(!preg_match('/%?B\d{1,19}\^(?=[A-Za-z0-9 .()\/-]{2,26}\^)[A-Za-z0-9 .()-]*\/[A-Za-z0-9 .()-]*\^([2-9]{1}[0-9]{1})(0[1-9]|10|11|12)(1|2|5|6|7|9)(0|2|4)[1-7]{1}\w*\??/',$aRecord['50']))
                        {
                            
                            $iPanPosition = strpos(trim($aRecord['50']),$sBIN);
                            if($iPanPosition!==false)
                            {
                                $iBINln = 6;
                                $iPANln = strlen($sPAN);
                                $iMaskedCharsln = abs($iPANln-4-$iBINln);                
                                $sMaskedTrack1 = substr_replace(trim($aRecord['50']),"XXXXXX",$iPanPosition+strlen($sBIN),$iMaskedCharsln);
                            }
                            else
                            {
                                $sMaskedTrack1 = "unable to mask the track data - view not allowed";
                            }
                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , Track1 data have incorrect magnetic stripe format, received value: ".$sMaskedTrack1." \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK - TRACK_1";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - Magnetic Stripe Track1 format";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                            
                        }
                        //TRACK 2 VALIDATION
                        if(!preg_match('/;\d{16}=\d{20}\?/',trim($aRecord['51'])))
                        {
                            
                            $iPanPosition = strpos(trim($aRecord['51']),$sBIN);
                            if($iPanPosition!==false)
                            {
                                $iBINln = 6;
                                $iPANln = strlen($sPAN);
                                $iMaskedCharsln = abs($iPANln-4-$iBINln);                
                                $sMaskedTrack2 = substr_replace(trim($aRecord['51']),"XXXXXX",$iPanPosition+strlen($sBIN),$iMaskedCharsln);
                            }
                            else
                            {
                                $sMaskedTrack2 = "unable to mask the track data - view not allowed";
                            }
                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , Track2 data have incorrect magnetic stripe format, received value: ".$sMaskedTrack2." \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK - TRACK_2";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - Magnetic Stripe Track2 format";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                            
                        }
                        //PAN VALIDATION
                        if(!preg_match('/\d{16}/',trim($aRecord['1'])))
                        {
                            
                            $iPanPosition = strpos(trim($aRecord['1']),$sBIN);
                            $iErrorsPerRecord++;
                            //$iNoErrorRecs++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." with PAN ".$sMaskedPAN." , PAN has incorrect format \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK - PAN";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data validation error - PAN format";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                            
                        }

                        if(!preg_match('/[a-zA-Z]{2,3}/',trim($aRecord['18'])))
                        {
                            $iErrorsPerRecord++;
                            $sError = "";
                            $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".$sEmbName." ,the product ID: ".trim($ProductID)." has incorrect country code. The country code is expected to be 2-Alpha nummeric values. Country code from data: ".trim($aRecord['18'])." \n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $Status = "NOK";
                            $ErrorCode = "306";
                            $ErrorDescription = "Data Validation Error";
                            $bHasError= true;
                            $ProductProp['Product'] = "NOK";
                            $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                            $ProductID = trim($ProductID);
                        }
                        // print_r($ProductProp);
                        // echo"\nHERRE\n";
                        // echo("\n PRODUCTPROP".($ProductProp['IntFromFacility']=="TAGPL"));
                        // echo("\n PRODUCTPROP2".($ProductProp['IntFromFacility']));
                        if(!preg_match('/(usa|us|can|ca|united states)/',strtolower(trim($aRecord['18']))) && $ProductProp['IntFromFacility']=="TAGPL")
                        {
                            $Facility = "TAGPL";
                        }
                        else if(preg_match('/(usa|us|can|ca|united states)/',strtolower(trim($aRecord['18']))))
                        {
                            $Facility = "TAGUS";
                        }

                        if($iErrorsPerRecord>0)
                        {
                            $iNoErrorRecs++;
                            $iErrorsPerRecord=0;
                        }else{
                        
                            //SPLIT INTERNATIONAL
                            
                            if(!preg_match('/(USA|US|CAN|CA)/',trim($aRecord['18'])) && $ProductProp['IntFromFacility']=="TAGPL")
                            {
                               
                                $iNumberOfIntRecords++;
                                $bIsInternational = true;
                                $aInternationalShipmentOutputData[] = $aRecord;
                                $Facility = "TAGPL";
                                $Token = trim($aRecord['0']);
                                $sFileName;
                                $ShipSuffix = $sCustomerName."_".$ProductProp['Product']."_".$SHIPPING_ALIAS."_".trim($sEmbName)."_".substr($sPAN, -4);
                                //$sBIN;
                                //$Status;
                                //$ErrorCode;
                                //$ErrorDescription;
                                $DateReceived = "N/A";
                                //$DateReceived = filemtime($sOriginalFile.str_replace("emb","pgp",$sProcessedFilename));
                                $CardType = $ProductID;
                                $PAN4 = substr($sPAN, -4);
                                $san2 = "";
                                $name1 = $sEmbName;
                                $name2 = "";
                                $Address1 = trim($aRecord['13']);
                                $Address2 = trim($aRecord['14']);
                                $City = trim($aRecord['15']);
                                $State =  trim($aRecord['16']);
                                $ZIPCode =  trim($aRecord['17']);
                                $Country =  trim($aRecord['18']);

                                $aConfirmationReportOutputData[] = array($Token,$sFileName,"$ShipSuffix",$sBIN,$Status,"$ErrorCode","$ErrorDescription",$DateReceived,$CardType,$PAN4,$san2,"$name1","$name2","$Address1","$Address2",$City,$State,$ZIPCode,$Country,$Facility);
                                $aConfirmationReportHeader = array("Token", "FileName", "ShipSuffix","BIN","Status", "ErrorCode", "ErrorDescription", "DataReceived","CardType", "PAN4","san2","Name1","Name2","Address1","Address2","City","State","ZipCode","Country","Facility");
                                
                                // echo"iNumberOfRecords $iNumberOfRecords \n";
                                // echo"iNumberOfIntRecords $iNumberOfIntRecords \n";
                                // echo"iNoErrorRecs $iNoErrorRecs \n";
                                if($iNumberOfRecords==$iNumberOfIntRecords)
                                {
                                    
                                    echo "$sDateStamp [$sUser]: Total Number of international shipment for TAGPL records: ".$iNumberOfIntRecords." \n"; 
                                    echo "$sDateStamp [$sUser]: WARNING: All the records in  $inputDir are international, therefor all of them will be passed to TAGPOLAND.\n";
                                                                      
                                    writeReport($aConfirmationReportOutputData, $sFileName, $aConfirmationReportHeader, $sConfirmationReportDir, "CONFIRMATION REPORT",".conf_rep.csv");
                                    writeInternationalRecords($aInternationalShipmentOutputData,$sFileName);                                    
                                    return false;
                                } 
                                unset($input[$iRecID]);
                                continue;               
                            }
                            else
                            {
                                $Facility = "TAGUS";
                            }
                        }

                    }
                    else
                    {
                        $iNoErrorRecs++;
                        $sError = "";
                        $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($sEmbName)." with PAN $sMaskedPAN ,the card stock ID: ".trim($CARD_STOCK_ID)." is not defined in Products_configuration.csv. Please, review products configuration. \n";
                        $aErrors[] = $sError;
                        echo $sError;
                        $Status = "NOK";
                        $ErrorCode = "303";
                        $ErrorDescription = "The card stock ID from the file: ".trim($CARD_STOCK_ID).", is unknown";
                        $bHasError= true;
                        $ProductProp['Product'] = "NOK";
                        $ProductProp["ServiceType"] = "NOK";
                        $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                        $ProductID = trim($ProductID);
                        
                    }
                }
                else
                {
                    $iNoErrorRecs++;
                    $sError = "";
                    $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($sEmbName)." with PAN $sMaskedPAN ,the product ID: ".trim($ProductID)." (column/field 50) is not defined in Products_configuration.csv. Please, review products configuration. \n";
                    $aErrors[] = $sError;
                    echo $sError;
                    $Status = "NOK";
                    $ErrorCode = "302";
                    $ErrorDescription = "The product ID from the file: ".trim($ProductID).", is unknown";
                    $bHasError= true;
                    $ProductProp["ServiceType"] = "NOK";
                    $ProductProp['Product'] = "NOK";
                    $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                    $ProductID = trim($ProductID);
                   
                }
            }
            else
            {
                $iNoErrorRecs++;
                $sError = "";
                $sError = "$sDateStamp [$sUser]: ERROR: In the input file $inputDir ,in record ID: $iRecordNo, for cardholder ".trim($sEmbName)." , with PAN $sMaskedPAN the BIN: ".$sBIN." is not defined in Products_configuration.csv. Please, review products configuration.\n";
                $aErrors[] = $sError;
                echo $sError;
                $Status = "NOK";
                $ErrorCode = "301";
                $ErrorDescription = "The BIN from the file: ".$sBIN.", is unknown";
                $bHasError= true;
                $ProductProp["ServiceType"] = "NOK";
                $ProductProp['Product'] = "NOK";
                $ProductProp['ShippingMethods'][$SHIPPING_METHOD] = "NOK";
                $ProductID = trim($ProductID);
                
     
            }                        


            /*CONFIRMATION REPORT*/
            // $FileDate = date('Ymd',filemtime($inputDir));
            // $sFileName = basename($inputDir);
            // $Token = $Reference3;
            // $PAN4;
            // $sEmbName;
            // $CurrentDate = date('Ymd');
            // if($Status=="NOK")
            // {
            //     $Status="ERROR";
            // };
            // $ErrorCode;
            // $ErrorDescription;
            // $sBIN; 
            // $CardType = $ProductProp['Product'];
            // $Address1;
            // $Address2;
            // $City;
            // $State;
            // $ZIPCode;
            // $ZIPCodeAddOn;
            // $Country;         
            
            // $aConfirmationReportOutputData[]=array($FileDate,$sFileName,$Token,$PAN4,$sEmbName,$CurrentDate,$Status,$ErrorCode,$ErrorDescription,$sBIN,$CardType,$Address1,$Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn,$Country);
             

            /*CONFIRMATION REPORT*/
             //INIT CONFIRMATION REPORT
             $Token = trim($aRecord['0']);
             $sFileName;
             $ShipSuffix = $sCustomerName."_".$ProductProp['Product']."_".$SHIPPING_ALIAS."_".trim($sEmbName)."_".substr($sPAN, -4);
             //$sBIN;
             //$Status;
             //$ErrorCode;
             //$ErrorDescription;
             $DateReceived = "N/A";
             //$DateReceived = filemtime($sOriginalFile.str_replace("emb","pgp",$sProcessedFilename));
             $CardType = $ProductID;
             $PAN4 = substr($sPAN, -4);
             $san2 = "";
             $name1 = $sEmbName;
             $name2 = "";
             $Address1 = trim($aRecord['13']);
             $Address2 = trim($aRecord['14']);
             $City = trim($aRecord['15']);
             $State =  trim($aRecord['16']);
             $ZIPCode =  trim($aRecord['17']);
             $Country = (strlen(trim($aRecord['18']))==2) ? trim($aRecord['18']) : convertCountry('alpha3',$aRecord['18'],'alpha2');
             $ForecastDeliveryDate = "";
             //SHIPMENT REPORT
             $Tracking =  "Not Available";

             $ServiceType = $ProductProp["ServiceType"];
             if(empty($ServiceType)){
                switch($SHIPPING_ALIAS)
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
             if($ServiceType=="US-PM")
                 {
                     $ForecastDeliveryDate =  date('m/d/Y',strtotime(' + 2 days'));
                 }
                 else if($ServiceType=="US-FC")
                 {
                     $ForecastDeliveryDate =   date('m/d/Y',strtotime(' + 4 days'));
                 }
                 else if($ServiceType=="US-FCI")
                 {
                     $ForecastDeliveryDate =   date('m/d/Y',strtotime(' + 7 days'));
                 } 
                 else if($ServiceType=="US-PMI")
                 {
                     $ForecastDeliveryDate =   date('m/d/Y',strtotime(' + 7 days'));
                 }
             
         
             $aConfirmationReportOutputData[] = array($Token,$sFileName,"$ShipSuffix",$sBIN,$Status,"$ErrorCode","$ErrorDescription",$DateReceived,$CardType,$PAN4,$san2,"$name1","$name2","$Address1","$Address2",$City,$State,$ZIPCode,$Country,$Facility);
             $aConfirmationReportHeader = array("Token", "FileName", "ShipSuffix","BIN","Status", "ErrorCode", "ErrorDescription", "DataReceived","CardType", "PAN4","san2","Name1","Name2","Address1","Address2","City","State","ZipCode","Country","Facility");
             $aShipmentReportOutputData[] = array($Token,$SHIPPING_ALIAS,$Tracking,$name1,$name2,$Address1,$Address2,$City,$State,$ZIPCode,$ForecastDeliveryDate,$ProductProp['Product'],$Status);
             $aShipmentReportOutputDataHeader = array("Token","ShipmentMethod","Tracking","name1","name2","adr1","adr2","city","state","zipcode","expDate","ForecastDeliveryDate","Product","Status");


            if($iNumberOfRecords==$iNoErrorRecs)
            {
                echo "$sDateStamp [$sUser]: ERROR: All the records in  $inputDir contains error, therefor this file cannot be processed.\n";
                writeReport($aConfirmationReportOutputData, $sFileName, $aConfirmationReportHeader, $sConfirmationReportDir, "CONFIRMATION REPORT",".conf_rep.csv");

                return false;

            }
            else if($iNumberOfRecords==($iNumberOfIntRecords+$iNoErrorRecs))
            {
                writeReport($aConfirmationReportOutputData, $sFileName, $aConfirmationReportHeader, $sConfirmationReportDir, "CONFIRMATION REPORT",".conf_rep.csv");
                echo "$sDateStamp [$sUser]: WARNING: Records in  $inputDir contains error, and international records\n";
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

            //print_r($input);
            
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


    if(!preg_match('/take/', strtolower(basename($inputDir))))
    {
            writeReport($aConfirmationReportOutputData, $sFileName, $aConfirmationReportHeader, $sConfirmationReportDir, "CONFIRMATION REPORT",".conf_rep.csv");

            writeReport($aShipmentReportOutputData, $sFileName, $aShipmentReportOutputDataHeader, $sShipmentReportDir, "SHIPMENT REPORT",".ship_rep_not_processed.csv");


                if($bIsInternational)
                {
                    writeInternationalRecords($aInternationalShipmentOutputData,$sFileName);                                    
                }
    }
    else
    {
            echo "$sDateStamp [$sUser]: File is being reprocessed, new confirmation report, shipment report and TAGPL file will not be created. \n";

    }


    //  echo"PRINTINPUT\n";
    //   print_r($input);
 
        return $input;


}

//////END PART 4 VALIDATION DATA THE CUSTOMER DATA - THE CONFIRMATION / SHIPMENT REPORT IS PART COULD BE SPLIT//

?>