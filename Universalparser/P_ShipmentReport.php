<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 10/14/2021
Revision: 11/19/2021
Name: Radovan Jakus
Version: 2.4
******************************/

/*Production Environment*/
$sInputShipmentReportDir = "/var/TSSS/Files/Reports/galileo/waiting/";
$sInputShipsiDir = "/var/TSSS/Stamps.com/indicium/result/";
$sOutputDir = "/var/TSSS/Files/Reports/galileo/shipmentreport/";
$sProcessedDir = "/var/TSSS/Files/Reports/galileo/waiting/processed/";

// $sInputShipmentReportDir = "D:\Workspace\TagSystem\Parser_Plugin\Galileo_Save\out\REPORT\\SHIPMENT_REPORT\\";
// $sInputShipsiDir = "D:\\Production Data\\stamps\\indicium\\result\\";
// $sOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Shipment_Report\\out\\";
// $sMailOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Shipment_Report\\out\\";
// //$sMailOutputDir = "D:\Production Data\stamps\indicium\\";
// //$sProcessedDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Galileo_Save\\out\REPORT\\SHIPMENT_REPORT\\";  
// $sProcessedDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Galileo_Save\\out\REPORT\\Processed\\";  


//Numbers
//$iNumberOfRecords;

//$iRemainingRecords;

$iShipmentTotalLen;
$iShipsiTotalLen;
$iErrorsPerShipmentFile;






//ob_start();
//echo"HI";
//header('Content-Type: application/json');
date_default_timezone_set ("America/New_York");
$sDateStamp = date('Y-m-d H:i:s', time());
$sUser = get_current_user();



echo "$sDateStamp [$sUser]: Starting Script \n";



$aOptions  = getopt("p::n::");
$sInputFilePath;

$bDataProcessed = false;
$bMailProcessed = false;
$midArray = array();
$aOriginalFileNames = array();
$bAlreadyProcessing = false;

 echo "$sDateStamp [$sUser]: The files that has been already processed and completed will not be processed again. Location of processed file: $sProcessedDir\n";
 echo "$sDateStamp [$sUser]: Using option to process files from predefined directory automatically. List of Shipment reports directory and list of Shipsi reports sub-directory \n \t $sInputShipmentReportDir \n \t\t $sInputShipsiDir \n\n";
 
 $sInputShipmentReportFiles = glob("$sInputShipmentReportDir*ship_rep_not_processed.csv");
 $sInputShipmentReportTmpFiles = glob("$sInputShipmentReportDir*ship_rep_processing.csv");
    if($sInputShipmentReportFiles)
    {
        foreach($sInputShipmentReportFiles as $sInputFilePath)
            {      
                //skip files that have been already processed
                $sShipNotProcessedBaseName = preg_replace("/.ship_rep_not_processed.csv/","",basename($sInputFilePath));
                $sProcessedReportFiles = glob($sProcessedDir."*".$sShipNotProcessedBaseName."*");
                if($sProcessedReportFiles)
                {
                    continue;
                }

                //use processing file instead not processed file. Processing file was already processed onced and might contain filled records. 
                $sInputShipmentReportTmpFiles = glob($sInputShipmentReportDir."*".preg_replace("/.ship_rep_not_processed.csv/",".ship_rep_processing.csv",basename($sInputFilePath))."*");
                if($sInputShipmentReportTmpFiles)
                {
                    $sInputFilePath = $sInputShipmentReportTmpFiles[0];
                    $sInputShipsiFiles = glob($sInputShipsiDir."*".preg_replace("/.ship_rep_processing.csv/","",basename($sInputFilePath))."*");  
                }
                else
                {
                    $sInputShipsiFiles = glob($sInputShipsiDir."*".$sShipNotProcessedBaseName."*");
                }

                    echo "\t".basename($sInputFilePath)." \n";
                    if($sInputShipsiFiles){
                        foreach($sInputShipsiFiles as $sInputShipsiFilePath)
                        {
                            echo "\t\t".basename($sInputShipsiFilePath)." \n";
                        }
                    }
                
            }
            
            foreach($sInputShipmentReportFiles as $sInputFilePath)
            {
                //skip files that have been already processed
                $sShipNotProcessedBaseName = preg_replace("/.ship_rep_not_processed.csv/","",basename($sInputFilePath));
                $sProcessedReportFiles = glob($sProcessedDir."*".preg_replace("/.ship_rep_not_processed.csv/","",basename($sInputFilePath))."*");
                if($sProcessedReportFiles)
                {
                    continue;
                }
                
                
        
                $sInputShipmentReportTmpFiles = glob($sInputShipmentReportDir."*".preg_replace("/.ship_rep_not_processed.csv/",".ship_rep_processing.csv",basename($sInputFilePath))."*");
                if($sInputShipmentReportTmpFiles)
                {
                    $sInputFilePath = preg_replace("/.ship_rep_not_processed.csv/",".ship_rep_processing.csv",$sInputFilePath);
                    $sInputShipsiFiles = glob($sInputShipsiDir."*".preg_replace("/.ship_rep_processing.csv/","",basename($sInputFilePath))."*");
                    $bAlreadyProcessing= true;
                }
                else
                {
                    $sInputShipsiFiles = glob($sInputShipsiDir."*".$sShipNotProcessedBaseName."*");
                }
                echo "\n\n$sDateStamp [$sUser]: ********Shipment report file Processing********: \n";
                echo "\t".basename($sInputFilePath)." \n";
                $aShipmentReportData = Parser($sInputFilePath,",");
               

                       
                       
                        $iShipmentTotalLen = 0;
                        
                        $aShipmentServices = getShipmentServiceOverview($aShipmentReportData,basename($sInputFilePath));
                        $aShipmentServicesPerProduct = getShipmentServiceDetailOverview($aShipmentReportData, basename($sInputFilePath));

                        if($sInputShipsiFiles){
                            echo "\n\t\tShipsi Files belonging to ".basename($sInputFilePath)." \n";
                            foreach($sInputShipsiFiles as $sInputShipsiFilePath)
                            {
                              
                                echo "\t\t\t".basename($sInputShipsiFilePath)." \n";
                            }
                            foreach($sInputShipsiFiles as $sInputShipsiFilePath)
                            {
                                if(count($midArray)==0)
                                {
                                    $midArray = CompleteShipmentReportInput($aShipmentReportData, Parser($sInputShipsiFilePath,"\t"), $sInputFilePath, $sInputShipsiFilePath);
                                    //echo"print array 1\n";
                                    //print_r($midArray);
                                }
                                else
                                {
                                    $midArray = CompleteShipmentReportInput($midArray, Parser($sInputShipsiFilePath,"\t"), $sInputFilePath, $sInputShipsiFilePath);
                                    //echo"print array 2\n";
                                    //print_r($midArray);
                                }
                                
                                
                                echo "$sDateStamp [$sUser]: No of processed records/lines from input Shipsi file: ".basename($sInputShipsiFilePath)." : ".$iShipsiTotalLen." \n";
                            
                            }
                    
                            echo "\n$sDateStamp [$sUser]: All Shipsi files for Shipment report: ".basename($sInputFilePath)." has been processed \n";
                             
                           $sStatus = "";
                           $bIsReportCompleted = isReportCompleted($midArray, basename($sInputFilePath));
        
                            if($bIsReportCompleted)
                            {
                                echo "\n$sDateStamp [$sUser]: All USPS tracking numbers per customer reference ID has been found. The processing of file has been completed \n";
                                $sStatus = "completed";
                                writeReport($midArray, $sInputFilePath ,$sOutputDir,$sProcessedDir,$sStatus);
                            }
                            else if(!$bIsReportCompleted)
                            {
                                echo "\n$sDateStamp [$sUser]: Not all trackable USPS records have tracking number. Trackable services are USPS_TR, USPS_PM, USPS_, or service type US-PM. File will be waiting for reprocessing when more shipsi files with tracking numbers are available \n";
                                $sStatus = "processing";
                                writeReport($midArray, $sInputFilePath ,$sOutputDir,$sProcessedDir,$sStatus);
                            }
                            else
                            {
                                $sStatus = "error";
                                echo "$sDateStamp [$sUser]: Error the Tracking Numbers for USPS_TR: $iUSPS_TRRecs is more than actual trackings: $iAlreadyContainsTrackingRecs\n";

                            }

                            
                            $midArray = null;
                            unset($midArray);
                        }
                        else
                        { 
                                $bIsReportCompleted = isReportCompleted($aShipmentReportData, basename($sInputFilePath));
                                $sStatus = "";
                                if($bIsReportCompleted)
                                {
                                    echo "$sDateStamp [$sUser]: The file has no reference to Shipsi files and no trackable or tracking required records. Trackable services are USPS_TR, USPS_PM, USPS_, or service type US-PM. There is nothing to be processed. Therefore file will be written as completed\n";
                                    $sStatus = "completed";
                                    writeReport($aShipmentReportData, $sInputFilePath ,$sOutputDir,$sProcessedDir,$sStatus);
                                }
                                else
                                {
                                    echo "$sDateStamp [$sUser]: The Shipsi files for this file are not ready yet. File will be waiting for reprocessing\n";
                                    $sStatus = "processing";
                                    writeReport(Parser($sInputFilePath,","), $sInputFilePath ,$sOutputDir,$sProcessedDir,$sStatus);
                                }     
                        }
                
             }
              
    }
    else 
    {
        echo "$sDateStamp [$sUser]: There are no files to be processed in directory. The directory does not contain any not processed shipment reports. Directory: $sInputShipmentReportDir\n";
    }


function Parser($inputDir,$sDelimiter)
{
    global $sDateStamp;
    global $sUser;
    //global $iNumberOfRecords;
    //$iNumberOfRecords = 0;
    
  
    
    //echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";

    $sFileName = basename($inputDir);
    $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);
    $header = array_shift($aInputFile);
    $aRecordData;
    foreach($aInputFile as $aData)
    {
        //$iNumberOfRecords++;
        $aRecordData[] = array_combine(str_getcsv($header,$sDelimiter),str_getcsv($aData,$sDelimiter));
    }   
    //echo"HERE";
    //print_r($aRecordData);

    return  $aRecordData;
}








function CompleteShipmentReportInput($aInputShipmentReportData, $aInputShipsiData, $inputShipmentReportPath, $inputShipsiFilePath)
{
    global $sDateStamp;
    global $sUser;
    global $iShipsiTotalLen;
    global $iShipmentTotalLen; 
    
    $iShipsiCounter = 0;
    $iRecsWithTracking = 0;
    $iRecsWithoutTrackingNo = 0;
    $iAlreadyContainsTrackingRecs = 0;
    $iErrorsPerShipsiFile = 0;
    $iShipsiTotalLen = count($aInputShipsiData);
    $iShipmentTotalLen = count($aInputShipmentReportData);

    echo "\n$sDateStamp [$sUser]: Shipsi report file processing:\n $inputShipsiFilePath \n";

                        /*echo "\nShipsi\n";
                        print_r($inputShipmentReport);
                        echo "\nShipment\n";
                        print_r($inputShipsi);*/
    $ShipmentReportCor = $aInputShipmentReportData;

    //echo("Input Shipment Report Data");
    //print_r($aInputShipmentReportData);
    
    $aErrorMessages = array();

    foreach($aInputShipsiData  as  $keyShipsi => $aShipsiRecord){
            

            foreach($aInputShipmentReportData as $keyShip => $aShipRecord){
                        
                        if($aShipRecord['Token']==$aShipsiRecord['Reference3'])
                        {   
                            
                            if(preg_match('/Not Available/',$aShipRecord['Tracking']))
                            {

                                if($aShipsiRecord['ErrorMessage']=="null")
                                { 
                                    if($aShipsiRecord['TrackingNumber']!=null){
                                        $iRecsWithTracking++;
                                        $aInputShipmentReportData[$keyShip]['Tracking']=$aShipsiRecord['TrackingNumber'];
                                        if($aShipRecord['adr1']!=$aShipsiRecord['Address1'])
                                        {
                                            $aInputShipmentReportData[$keyShip]['adr1']=$aShipsiRecord['Address1'];
                                        }
                                        if($aShipRecord['adr2']!=$aShipsiRecord['Address2'])
                                        {
                                            $aInputShipmentReportData[$keyShip]['adr2']=$aShipsiRecord['Address2'];
                                        }
                                        if($aShipRecord['city']!=$aShipsiRecord['City'])
                                        {
                                            $aInputShipmentReportData[$keyShip]['city']=$aShipsiRecord['City'];
                                        }
                                        if($aShipRecord['state']!=$aShipsiRecord['State'])
                                        {
                                            $aInputShipmentReportData[$keyShip]['state']=$aShipsiRecord['State'];
                                        }
                                        $aInputShipmentReportData[$keyShip]['Status']="OK";
                                        
                                    }
                                    else
                                    {        
                                        $iRecsWithoutTrackingNo++;
                                    }

                                }
                                else
                                {
                                    $aErrorMessages[] = $aShipsiRecord['ErrorMessage'];
                                    $aInputShipmentReportData[$keyShip]['Status']="NOK";
                                    $aInputShipmentReportData[$keyShip]['Tracking']="Not Available";
                                    $iErrorsPerShipsiFile++;
                                }

                                $ShipmentReportCor = $aInputShipmentReportData;
                                break;
                            }
                            else
                            {
                                $iAlreadyContainsTrackingRecs++;
                            }
                        }
        }
     }
    
     if(isset($aErrorMessages)&&count($aErrorMessages)!=0){
       $aErrorList = array_count_values($aErrorMessages);
       echo "$sDateStamp [$sUser]: ERRORs found in Shipsi file\n";
       foreach($aErrorList as $colName => $iApperance)
       {
            echo "$sDateStamp [$sUser]: ERROR from Shipsi: [$colName] found  $iApperance times. \n";
       }
       echo"\n";
        
     }
  

    printf('%s [%s]: %-70s  %20d%s', $sDateStamp,$sUser,'No of total records', $iShipsiTotalLen, PHP_EOL);
    printf('%s [%s]: %-70s  %20d%s', $sDateStamp,$sUser,'No of records "new" tracking number is found', $iRecsWithTracking,PHP_EOL);
    printf('%s [%s]: %-70s  %20d%s', $sDateStamp,$sUser,'No of records already have tracking number', $iAlreadyContainsTrackingRecs,PHP_EOL);
    printf('%s [%s]: %-70s  %20d%s', $sDateStamp,$sUser,'No of records do not have tracking number(might not be trackable)', $iRecsWithoutTrackingNo,PHP_EOL);
    printf('%s [%s]: %-70s  %20d%s', $sDateStamp,$sUser,'No of records do not have tracking number due to error', $iErrorsPerShipsiFile,PHP_EOL);

    //echo"\nDATA";
   // print_r($ShipmentReportCor);
    return $ShipmentReportCor;

}

function writeReport($aData, $inputFile,$outputDir,$sProcessedDir, $status){
 
    global $sDateStamp;
    global $sUser;
    global $bAlreadyProcessing;
    //echo"\nDATA to Write\n";
    //print_r($aData);

    $sOriginalFilename = basename($inputFile);
    $sOriginalInputDir = dirname($inputFile)."\\";
    $sProcessedFile="";
    $sOutputFile="";

    

    if($status == "completed")
    {
        if($bAlreadyProcessing)
        {
               
                $sOutputFileName = preg_replace("/_processing/","_completed",$sOriginalFilename);
                $sProcessedFilename = preg_replace("/_processing/","_processed",$sOriginalFilename);
                $sOutputFile =  $outputDir.$sOutputFileName;
                $sProcessedFile = $sProcessedDir.$sProcessedFilename;

                $bFileMoved = rename($inputFile , $sProcessedFile);
                if($bFileMoved)
                {
                    echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedFile \n";
                }
                else 
                {
                    echo "$sDateStamp [$sUser]: Processed File failed to be moved to: $sProcessedFile \n";
                }
                $bAlreadyProcessing=false;
        }
        else
        {
            $sOutputFileName = preg_replace("/_not_processed/","_completed",$sOriginalFilename);
            $sProcessedFilename = preg_replace("/_not_processed/","_processed",$sOriginalFilename);
            $sOutputFile =  $outputDir.$sOutputFileName;
            $sProcessedFile = $sProcessedDir.$sProcessedFilename;

            $bFileMoved = rename($inputFile , $sProcessedFile);
            if($bFileMoved)
            {
                echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedFile \n";
            }
            else 
            {
                echo "$sDateStamp [$sUser]: Processed File failed to be moved to: $sProcessedFile \n";
            }
        }
      
        
    }
    else if($status =="processing")
    {
        $sOutputFileName = preg_replace("/_not_processed/","_processing",$sOriginalFilename);
        $sOutputFile = $sOriginalInputDir.$sOutputFileName;
    }
    
    
    
    $fp = fopen($sOutputFile, "w");
    fwrite($fp, implode(",",array("Token","ShipmentMethod", "Tracking", "name1", "name2", "adr1", "adr2", "city","state", "zipcode","expdate", " ForecastDeliveryDate","Product","Status"))).fwrite($fp, "\r\n");

    foreach($aData as $row)
    {
        //$bFileWriting1 =fwrite($fp, implode(",",$row)).fwrite($fp, "\r\n");
        $bFileWriting1 =fputcsv($fp,$row);
        $aFilesWritingStatus[] = $bFileWriting1;
    }
    if($bFileWriting1)
    {
        echo "$sDateStamp [$sUser]: Shipment report $sOutputFile succesfully written.\n";
        fclose($fp);
    }
    else 
    {
        echo "$sDateStamp [$sUser]: Writing file $sOutputFile failed\n";
        fclose($fp);
    }
    foreach($aFilesWritingStatus as $bFileStatus)
    {
        if(!$bFileStatus)
        {
            return false;
        }
    }

   
    return true;
    
}   


function getShipmentServiceOverview($aInputShipmentReportData, $sFileName){

    global $sDateStamp;
    global $sUser;
    $iTotalNumberOfRecords = 0;
    foreach($aInputShipmentReportData as $keyShip => $aShipRecord)
    { 
        $aCollectShipment[] = $aShipRecord['ShipmentMethod'];
    }
    $aShipmentServices = array_count_values($aCollectShipment);
    echo "\n\t Summary of records per Shipment Method for file $sFileName: \n";
    printf('            %-20s|  %-20s|  %-20s ', 'Shipment Method', 'Service Type', 'Total Number of Records');
    echo"\n";

    foreach($aShipmentServices as $keyShipService => $iTotalNoPerService)
    {
        $sShipmentMethodType = $keyShipService;
        $sShipmentMethodServiceType = "";
        if(preg_match('/\|/',$keyShipService))
        {
            $sShipmentMethodDetail = explode("|",$keyShipService);
            $sShipmentMethodType = $sShipmentMethodDetail[0];
            $sShipmentMethodServiceType = $sShipmentMethodDetail[1];
        }
        $iTotalNumberOfRecords+=$iTotalNoPerService;
        printf('            %-20s|  %-20s|  %20d ', $sShipmentMethodType, (empty($sShipmentMethodServiceType) ? "N/A" :"$sShipmentMethodServiceType"), $iTotalNoPerService);
        echo"\n";
        //echo "Shipment Method: $sShipmentMethodType,\tService Type: ".(empty($sShipmentMethodServiceType) ? "N/A,\t" :"$sShipmentMethodServiceType ,\t")."Total Number of Records: $iTotalNoPerService \n";
    }
    printf('            %-67s','---------------------------------------------------------------------');
    echo"\n";
    printf('            %-20s   %-20s  %20d ', 'Total Records in file', '', $iTotalNumberOfRecords);
    echo"\n";

    return  $aShipmentServices;

}

function getShipmentServiceDetailOverview($aInputShipmentReportData, $sFileName){

    global $sDateStamp;
    global $sUser;
    $iTotalNumberOfRecords=0;
    foreach($aInputShipmentReportData as $keyShip => $aShipRecord)
    { 
        $aCollectShipment[$aShipRecord['Product']][] =  $aShipRecord['ShipmentMethod'];
    }
    //print_r($aCollectShipment);
    //$aShipmentServicesPerProduct = array_count_values($aCollectShipment);
    
    
    echo "\n\t Detail Summary of records per Product and Shipment Method for file $sFileName: \n";
    printf('            %-20s|  %-20s|  %-20s|  %-20s ', 'Product/Shipment', 'Shipment Method', 'Service Type', 'Total Number of Records');
    echo"\n";

    foreach($aCollectShipment as $keyShipPerProduct => $aProducts)
    {
        //print_r($aProducts);
        $aShipmentServices = array_count_values($aProducts);
        $iSubTotalNumberOfRecords = 0;
        
        foreach($aShipmentServices as $keyShipService => $iTotalNoPerService)
        {
            $sShipmentMethodType = $keyShipService;
            $sShipmentMethodServiceType = "";
            $sProductType = $keyShipPerProduct;

            if(preg_match('/\|/',$keyShipService))
            {
                $sShipmentMethodDetail = explode("|",$keyShipService);
                $sShipmentMethodType = $sShipmentMethodDetail[0];
                $sShipmentMethodServiceType = $sShipmentMethodDetail[1];
            }
            $iTotalNumberOfRecords+=$iTotalNoPerService;
            $iSubTotalNumberOfRecords+=$iTotalNoPerService;
            printf('            %-20s|  %-20s|  %-20s|  %20d ', $sProductType,$sShipmentMethodType, (empty($sShipmentMethodServiceType) ? "N/A" :"$sShipmentMethodServiceType"), $iTotalNoPerService);
            echo"\n";
            //echo "Shipment Method: $sShipmentMethodType,\tService Type: ".(empty($sShipmentMethodServiceType) ? "N/A,\t" :"$sShipmentMethodServiceType ,\t")."Total Number of Records: $iTotalNoPerService \n";
        }
        printf('           %-87s','............................................................................................');
        echo"\n";
        printf('           %-68s  %20d', 'Subtotal Records per Product',$iSubTotalNumberOfRecords);
        echo"\n\n";
    }
        printf('           %-87s','--------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-20s    %-20s   %-20s  %20d', 'Total Records in file','','',$iTotalNumberOfRecords);
        echo"\n";
    
    return  $aCollectShipment;

}

function isReportCompleted($midArray, $sFileName)
{
    global $sDateStamp;
    global $sUser;
    $iTotalNumberOfRecords=0;
    $iTrackable = 0;
    $iTrackableWithTracking = 0;
    $iTrackableWithoutTracking = 0;
    $bisReportCompleted = false;
    foreach($midArray as $keyShip => $aShipRecord)
    { 
        $bIsTrackable = preg_match('/USPS_TR|USPS_PM|USPS_|US-PM/',$aShipRecord['ShipmentMethod']);
        if($bIsTrackable) 
        {
            $iTrackable++;
            if((!preg_match('/Not Available/',$aShipRecord['Tracking'])) && ($aShipRecord['Status']=='OK')){
                $aCollectShipment[$aShipRecord['ShipmentMethod']][] =  'TRACKING FOUND';
                $iTrackableWithTracking++;
            }
            else if((preg_match('/Not Available/',$aShipRecord['Tracking'])) || ($aShipRecord['Status']=='NOK'))
            {
                $aCollectShipment[$aShipRecord['ShipmentMethod']][] = 'MISSING TRACKING-ERRORED RECORDS';
                $iTrackableWithoutTracking++;
                $bisReportCompleted = false;
            }
        }
        else
        {
            if((preg_match('/Not Available/',$aShipRecord['Tracking'])) && ($aShipRecord['Status']=='OK')){
                $aCollectShipment[$aShipRecord['ShipmentMethod']][] =  'TRACKING NOT REQIRED BUT FOUND';
            }
            else if((preg_match('/Not Available/',$aShipRecord['Tracking'])) || ($aShipRecord['Status']=='NOK'))
            {
                $aCollectShipment[$aShipRecord['ShipmentMethod']][] =  'TRACKING NOT REQIRED';
                
            }
            
        } 
    }

    if($iTrackable == $iTrackableWithTracking)
    {
        $bisReportCompleted = true;
    }
    else if($iTrackable == 0)
    {
        $bisReportComplete = true;
    }
    else
    {
        $bisReportComplete = false;
    }

    //print_r($aCollectShipment);
    //$aShipmentServicesPerProduct = array_count_values($aCollectShipment);
    echo "\n\t Report after processing - summary of tracking numbers status $sFileName: \n";
    printf('            %-20s|  %-40s|  %-20s ', 'Shipment Method', 'Tracking Status', 'Total Number of Records');
    echo"\n";

    foreach($aCollectShipment as $keyCol1 => $aValCol1)
    {
        $aShipmentServices = array_count_values($aValCol1);    
        foreach($aShipmentServices as $keyCol2 => $iTotalNoPerCol2)
        {
            $sStatus = $keyCol2;
            $sShipmentMethodType = $keyCol1;
            $iTotalNumberOfRecords+=$iTotalNoPerCol2;
            printf('            %-20s|  %-40s|  %20d ', $sShipmentMethodType,$sStatus, $iTotalNoPerCol2);
            echo"\n";
        }
    }
        printf('           %-87s','--------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-20s    %-40s  %20d', 'Total Records in file','',$iTotalNumberOfRecords);
        echo"\n";
        return $bisReportCompleted;
}
echo "$sDateStamp [$sUser]: Ending Script";

?> 
