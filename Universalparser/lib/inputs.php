<?php

function DataPrepInput($input, $inputDir, $outputDir)
{
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
        $sUniqueNumber= sha1(substr($sTrack2,0,16));
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

            $aDataPrepOutputData[$sBIN][$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID."_".$GroupID."_". $BulkFullName."_".$BulkCompany."_".$BulkAddress1."_".$BulkAddress2."_".$BulkCity."_".$BulkZIPCode."_".$BulkZIPCodeAddOn."_".$BulkCountry][] = array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData); 

        }
        else
        {
            $aDataPrepOutputData[$sBIN][$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][] = array($sBatchID,$sUniqueNumber,$sNotUsed1,$sNotUsed2,$sNotUsed3,$sDataPrepProfile,$sNotUsed4,$sChipData); 
        }
        //echo"PRODUCTNO: $ProductID \n";
       // print_r($aDataPrepOutputData);

    }
   
//////END PART 5 CREATING FILE FOR DATAPREPRATIPN SYSTEM //

//////START PART 6 WRITING FILE FOR DATAPREPRATIPN SYSTEM //

    if(isset($aDataPrepOutputData) && count($aDataPrepOutputData)!=0)
    {
            echo "$sDateStamp [$sUser]: \n\n DATAPREP START \n\n";
            // echo "DataPrepArray";
            // print_r($aDataPrepOutputData);
            foreach($aDataPrepOutputData as $sBIN => $aRecordsPerBIN){    

                foreach($aRecordsPerBIN as $keyShipment => $aShippingRecord)     
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
    }

  

    return $aDataPrepOutputData;

//////END PART 6 WRITING FILE FOR DATAPREPRATIPN SYSTEM //

         
   
}


//////START PART 6 WRITING FILE FOR MAILING SYSTEM //

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
    //print_r($input);
            
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
                // if(!isset($sSerialNumber))
                // {
                //     echo"HERRE";
                //     print_r($aRecord);
                // }
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
                $Country = (strlen(trim($aRecord['18']))==2) ? trim($aRecord['18']) : convertCountry('alpha3',$aRecord['18'],'alpha2');
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
                $iPanPosition = strpos(trim($aRecord['51']),$sBIN);
                if($iPanPosition!==false)
                {
                    $iBINln = 6;
                    $iPANln = strlen($sPAN);
                    $iMaskedCharsln = abs($iPANln-4-$iBINln);                
                    $sMaskedTrack2 = substr_replace(trim($aRecord['51']),"XXXXXX",$iPanPosition+strlen($sBIN),$iMaskedCharsln);
                    $sMaskedTrack2 = str_replace([";","?"],"",$sMaskedTrack2);
                }
                else
                {
                    $sMaskedTrack2 = "unable to mask the track data - view not allowed";
                }
                $DataMatching = $sMaskedTrack2;
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
                        case "INT_STD":
                            $ServiceType = "US-FCI";
                            break;
                        case "INT_STD_TR":
                            $ServiceType = "US-FCI";
                            break;
                        case "INT_PM":
                            $ServiceType = "US-PMI";
                            break;
                        case "FEDx_SON":
                            $ServiceType = "5";
                            break;      
                        case "FEDx_OR":
                            $ServiceType = "2";
                            break;                          
                        case "FEDx_EXP":
                            $ServiceType = "US-PM";
                            break;
                        case "FEDx_INT_ECO":
                            $ServiceType = "103";
                        case "FEDx_PON":
                            $ServiceType = "1";
                            break;
                        case "FEDx_INT_PON":
                            $ServiceType = "101";
                            break;                   break;  
                                                          
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
                        case "INT_STD":
                                $PackageType = "Package";
                            break;
                        case "INT_STD_TR":
                                $PackageType = "Package";
                            break;
                        case "INT_PM":
                                $PackageType = "Package";
                        break;
                        case "FEDx_SON":              
                                $PackageType = "Large Envelope or Flat";
                            break;      
                        case "FEDx_OR":
                                $PackageType = "Large Envelope or Flat";
                            break;           
                        case "FEDx_EXP":
                                $PackageType = "Large Envelope or Flat";
                            break;
                        case "FEDx_INT_ECO":
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
                    $BulkCountry =  (strlen(trim($aRecord['31']))==2) ? trim($aRecord['31']) : convertCountry('alpha3',$aRecord['31'],'alpha2');
                    //TODO Grouping
                    $Reference1Bulk = "GROUP_ID_".$GroupID;
                    $Reference2Bulk = "";
                    $Reference3Bulk = "";
                    $Reference4Bulk = "";
                    $aBulkMultiShippingOutputData[$SHIPPING_METHOD_PROD][$BatchID."_".$GroupID."_". $BulkFullName."_".$BulkCompany."_".$BulkAddress1."_".$BulkAddress2."_".$BulkCity."_".$BulkZIPCode."_".$BulkZIPCodeAddOn."_".$BulkCountry][] = array($Company, $BulkFullName, $BulkAddress1, $BulkAddress2,$BulkCity,$BulkState,$BulkZIPCode,$BulkZIPCodeAddOn, $BulkCountry, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1Bulk, $Reference2Bulk, $Reference3Bulk, $Reference4Bulk);
                    //MAILING RESULT
                    $aMailMergeShippingOutputData[$sBIN][$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID."_".$GroupID."_". $BulkFullName."_".$BulkCompany."_".$BulkAddress1."_".$BulkAddress2."_".$BulkCity."_".$BulkZIPCode."_".$BulkZIPCodeAddOn."_".$BulkCountry][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4,$DataMatching,$ValidFrom,$MemberSince,$DDAAccount,$Currency,$ImageIDFront,$ImageIDBack,$ExternalCardID,$ExteralCHID,$AdditionalField1,$AdditionalField2,$AdditionalField3,$AdditionalField4,$AdditionalField5,$AdditionalField6,$AdditionalField7,$AdditionalField8);
                    $aMailShippingOutputData[$sBIN][$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID."_".$GroupID."_". $BulkFullName."_".$BulkCompany."_".$BulkAddress1."_".$BulkAddress2."_".$BulkCity."_".$BulkZIPCode."_".$BulkZIPCodeAddOn."_".$BulkCountry][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4);

                }
                else
                {
                    $aMailMergeShippingOutputData[$sBIN][$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4,$DataMatching,$ValidFrom,$MemberSince,$DDAAccount,$Currency,$ImageIDFront,$ImageIDBack,$ExternalCardID,$ExteralCHID,$AdditionalField1,$AdditionalField2,$AdditionalField3,$AdditionalField4,$AdditionalField5,$AdditionalField6,$AdditionalField7,$AdditionalField8);
                    $aMailShippingOutputData[$sBIN][$SHIPPING_METHOD][$ProductID][trim($CARD_STOCK_ID)][$BatchID][] = array($Company, $FullName, $Address1, $Address2,$City,$State,$ZIPCode,$ZIPCodeAddOn, $Country, $FromFullName, $FromAddress1, $FromAddress2, $FromCity, $FromState, $FromCountry, $FromZIPCode, $ServiceType, $PackageType, $WeightOz, $ShipDate, $ImageType, $Reference1,$Reference2,$Reference3,$Reference4);
                }

                $aMailMergeShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4","DataMatching","ValidFrom","MemberSince","DDAAccount","Currency","ImageIDFront","ImageIDBack","ExternalCardID","ExteralCHID","AdditionalField1","AdditionalField2","AdditionalField3","AdditionalField4","AdditionalField5","AdditionalField6","AdditionalField7","AdditionalField8"."\r\n"));
                $aMailShippingOutputDataHeader = implode("\t",array("Company","FullName", "Address1", "Address2", "City", "State", "ZIPCode", "ZIPCodeAddOn","Country", "FromFullName","FromAddress1","FromAddress2","FromCity","FromState","FromCountry","FromZIPCode","ServiceType","PackageType","WeightOz","ShipDate", "ImageType", "Reference1","Reference2","Reference3","Reference4"."\r\n"));

    
    
    
            }
//////END PART 6 CREATING DATA FOR MAILING SYSTEM //

//////START PART 7 WRITING FILE USING ARRAY FOR MAILING SYSTEM //


            if(isset($aMailShippingOutputData) && count($aMailShippingOutputData)!=0)
            {
                echo "$sDateStamp [$sUser]: \n\n MAILING START \n\n";
                //  echo "aMailShippingOutputData";
                //  print_r($aMailShippingOutputData);
                foreach($aMailShippingOutputData as $sBIN => $aRecordsPerBIN) 
                {
                    foreach($aRecordsPerBIN as $keyShipment => $aShippingRecord) 
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
                                        if(!isset($sProductProp))
                                        {
                                            echo"sBIN $sBIN\n";
                                            echo"sBIN $keyProduct\n";
                                            echo"sBIN $keyCardStock\n";
                                            print_r($aMailShippingOutputData);
                                        }
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
            }


            if(isset($aMailMergeShippingOutputData) && count($aMailMergeShippingOutputData)!=0)
            {
                echo "$sDateStamp [$sUser]: \n\n MAILMERGE START \n\n";

                // echo "aMailMergeShippingOutputData";
                // print_r($aMailMergeShippingOutputData);
        
                foreach($aMailMergeShippingOutputData as $sBIN => $aRecordsPerBIN) 
                {
                    foreach($aRecordsPerBIN as $keyShipment => $aShippingRecord) 
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
            }


            if(isset($aBulkMultiShippingOutputData) && count($aBulkMultiShippingOutputData)!=0)
            {
                //print_r($aBulkMultiShippingOutputData);

                echo "$sDateStamp [$sUser]: \n\n BULK PKG MAILING START \n\n";

                
                foreach($aBulkMultiShippingOutputData as $keyBulkShippingMethod => $aBulkShippingData)     
                {
                    $iGroup = 0; 
                    foreach($aBulkShippingData as $keyGroupID => $aGroupID)  
                    {   
                    
                            $iGroup++;
                            //  echo("GROUP $iGroup\n");
                            //  print_r($aGroupID);
                            
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


?>