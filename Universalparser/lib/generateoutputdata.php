<?php

$aFileNamesStatus = array();
$json_sConfigurationName = '';
$sPythonScriptLocation = __DIR__.'/JobTicketXls.py';
function GenerateOutputData($aMappedData, $sConfigurationFilePath, $sConfigurationName)
{
    global $aConfigDataSelected;
    global $aErrors;
    global $aFileNamesStatus;
    global $json_sConfigurationName;

    $json_sConfigurationName = $sConfigurationName;

    $sDateStamp = date('Y-m-d H:i:s', time());
    $sUser = get_current_user();
    $aInputFile = file_get_contents($sConfigurationFilePath);
    $aConfigData = json_decode($aInputFile, true);
    if(isset($aConfigData[$sConfigurationName]))
    {
          $aConfigDataSelected = $aConfigData[$sConfigurationName];
          echo "\n$sDateStamp [$sUser]: START WRITING $sConfigurationName  \n";
    }
    else
    {
        $ErrorMessage = "$sDateStamp [$sUser]: ERROR: $sConfigurationName does not exist in $sConfigurationFilePath";
        $aErrors[] = $ErrorMessage;
    }
    $objQuery = \Linq\LinqFactory::createLinq();




    //Where search condition
    if(isset($aConfigDataSelected['Where'])&& !empty($aConfigDataSelected['Where']))
    {
        $aWhereConditions = explode(',',$aConfigDataSelected['Where']);
        foreach($aWhereConditions as $sStatement)
        {
            $aStatements = explode('|',$sStatement,3);
            global $sItem ;
            global $sOperand;
            global $sTestedaMappedData;
            $sItem =  $aStatements[0];
            $sOperand =  $aStatements[1];
            $sTestedaMappedData =  $aStatements[2];

            $objQuery
            -> from ($aMappedData)
            ->where(function($aItem){
                    global $sItem;
                    global $sOperand;
                    global $sTestedaMappedData;
                
                    switch($sOperand)
                    {
                        case "==":
                            if($aItem[$sItem] == $sTestedaMappedData)
                                return $aItem;
                            break;
                        case "<=":
                            if($aItem[$sItem] <= $sTestedaMappedData)
                                return $aItem;
                            break;
                        case ">=":
                            if($aItem[$sItem] >= $sTestedaMappedData)
                                return $aItem;
                            break;
                        case ">":
                            if($aItem[$sItem] > $sTestedaMappedData)
                                return $aItem;
                            break;
                        case "<":
                            if($aItem[$sItem] < $sTestedaMappedData)
                                return $aItem;
                            break;
                        case "!=":
                            if($aItem[$sItem] != $sTestedaMappedData)
                                return $aItem;
                            break;
                        case "contains":
                            if(preg_match('/'.$sTestedaMappedData.'/',$aItem[$sItem]))
                                return $aItem;
                            break;
                        case "notcontains":
                            if(!preg_match('/'.$sTestedaMappedData.'/',$aItem[$sItem]))
                                return $aItem;
                            break;

                    }
          
                });

                $aMappedData = $objQuery->getResults();
        }  
    }

    //TODO
    $aNewMappedData = array();
    //DISTINC
    if(isset($aConfigDataSelected['DistincBy'])&& !empty($aConfigDataSelected['DistincBy']))
    {  
        $tempArr = array();
        $aDistinctElements = explode(',',$aConfigDataSelected['DistincBy']);
        foreach($aDistinctElements as $sIndex)
        {
            $tempArr = array_merge($tempArr,array_unique(array_column($aMappedData, $sIndex)));
        }

        $aNewMappedData =  (array_intersect_key($aMappedData, $tempArr));
        $aMappedData =   $aNewMappedData;
    }


    //GroupCondition 
    if(isset($aConfigDataSelected['GroupBy'])&& !empty($aConfigDataSelected['GroupBy']))
    {
        $aGroupElements = explode(',',$aConfigDataSelected['GroupBy']);
            $objQuery
            -> from ($aMappedData)
            -> groupBy(...$aGroupElements);
        $aMappedData = $objQuery->getResults();

    }

  
 
    if(count($aMappedData)!=0)
    {
        //echo "$sDateStamp [$sUser]:  Data found \n";
        writeFile($aMappedData,$aConfigDataSelected);
        //Write Status of Writing
        foreach($aFileNamesStatus as $sFileName => $bFileWriting1)
        {
            if($bFileWriting1)
            {
                if(isset($aConfigDataSelected['OutputFolder'])&& !empty($aConfigDataSelected['OutputFolder']))
                    $sOutputDir = $aConfigDataSelected['OutputFolder'];
                else
                    $aErrors[]= "$sDateStamp [$sUser]: ERROR: OutputFolder is not properly defined in configuration";

                $sOutputFilename = $sOutputDir.basename($sFileName);
                $bFileMoved = rename("$sFileName", "$sOutputFilename");
                if($bFileMoved)
                echo "$sDateStamp [$sUser]: File succesfully written as: $sOutputFilename\n";
                else
                    $aErrors[]= "$sDateStamp [$sUser]: ERROR: Moving $sFileName from ".dirname($sFileName)." to ".$sOutputDir ." directory failed.\n";

            }
            else
            {
                $aErrors[]= "$sDateStamp [$sUser]: ERROR: Writing $sFileName failed \n";
               // fclose($fp);
            }
        }
        $aFileNamesStatus = array();
    }
    else
    {
        echo "$sDateStamp [$sUser]: 0 Records found \n";
    }

    return false;
}
       function writeFile($aMappedData,$aConfigDataSelected){

                global $aErrors;
                $sDateStamp = date('Y-m-d H:i:s', time());
                $sUser = get_current_user();

                //loop each row of array 

                    
                    

                //GOOD WORKING FOR FIRST ONE
                $iterator = new RecursiveArrayIterator($aMappedData);
                $nextLevel = $iterator->getChildren();                
                    reset($nextLevel);
                    $nextLevel = new RecursiveArrayIterator($nextLevel);
                

                if(!($nextLevel->hasChildren()))
                {
                    writeRecords($aMappedData,$aConfigDataSelected);
                }
                else
                {
                    foreach($aMappedData as $key => $value)
                    {      
                       
                       
                        $iterator = new RecursiveArrayIterator($value);
                        if ($iterator->hasChildren())
                        {
                            $nextLevel = $iterator->getChildren();
                            reset($nextLevel);
                            $nextLevel = new RecursiveArrayIterator($nextLevel);
    
                            if($iterator->hasChildren() && $nextLevel->hasChildren() ) 
                            {    
                                writeFile($value,$aConfigDataSelected);
                                continue;//return;
                            }
                        }
                        writeRecords($value,$aConfigDataSelected);
                    }
                }
                    return;
                }
        

                function writeRecords($aRecords,$aConfigDataSelected)
                {
                    global $json_sConfigurationName;
                    global $sPythonScriptLocation;
                    $sxlsx_MasterPID = '';
                    $sSheetName_MasterPID = '';
                    $sxlsx_SchemaJobTicket = '';
                    $axlsx_FieldsToWriteFromMasterPID = '';
                    $axlsx_CellIndexOfFieldsToWriteProductConfig = '';
                    $axlsx_CellIndexForMasterPIDFieldsToWrite = '';
                    $aMappingFieldNames_ProdConfig_MasterPID = '';
                    $aMappingFieldValues_ProdConfig_MasterPID = '';

                    global $aFileNamesStatus;
                    $sDateStamp = date('Y-m-d H:i:s', time());
                    $sUser = get_current_user();

                    $numSplits = 0;
                    $recordsDone = 0;
                    $fp = null;
                    $neededSplits = 0;
                    $aFileWriting1 = array();
                    $bFileWriting1 = false;
                    $sFileName = "";
                    $NoOfCardsPerGroup = count($aRecords);


              
                    $aNewMappedData = array();
                    if(isset($aConfigDataSelected['UniqueOccurenceBy'])&& !empty(isset($aConfigDataSelected['UniqueOccurenceBy'])))
                    {
                         $sUniqueField = $aConfigDataSelected['UniqueOccurenceBy'];
                        //$aGroupElements = explode(',',$aConfigDataSelected['FileNameFromFields']);
                        $tempArr = array_unique(array_column($aRecords, $sUniqueField));
                        $aRecords = (array_intersect_key($aRecords, $tempArr));
                        //$aRecords= array_unique($aRecords,SORT_NUMERIC); 
             
                    }
                    if(isset($aConfigDataSelected['MaxRecords'])&& !empty($aConfigDataSelected['MaxRecords']))
                            $iMaxRec = $aConfigDataSelected['MaxRecords'];
                    else
                    {
                        $aErrors[]= "$sDateStamp [$sUser]: ERROR: MaxRecords is not properly defined in configuration";
                        $iMaxRec = count($aRecords);
                    }
            
                    if(isset($aConfigDataSelected['TempFolder'])&& !empty($aConfigDataSelected['TempFolder']))
                            $sTmpDir =  $aConfigDataSelected['TempFolder'];
                    else
                            $aErrors[]= "$sDateStamp [$sUser]: ERROR: TempFolder is not properly defined in configuration";

                   
                    if(isset($aConfigDataSelected['FileType'])&& !empty($aConfigDataSelected['FileType']))
                            $sFileType = $aConfigDataSelected['FileType'];
                    else
                            $aErrors[]= "$sDateStamp [$sUser]: ERROR: FileType is not properly defined in configuration";

                    if(isset($aConfigDataSelected['FieldDelimiter'])&& !empty($aConfigDataSelected['FieldDelimiter']))
                            $sFieldDelimiter = $aConfigDataSelected['FieldDelimiter'];

                    if(count($aRecords)>$iMaxRec)
                    {
                        $neededSplits = ceil(count($aRecords) / $iMaxRec);
                        // $aMappedData = array_chunk($aMappedData,$iMaxRec, true);
                    }
                
                        foreach($aRecords as $sProps => $sVal)
                        {                          
                            $aDataToWrite = "";
                            $sVal = array_merge($sVal, compact('NoOfCardsPerGroup'));
                            
                        
                            if(isset($aConfigDataSelected['FieldsToWrite'])&& !empty($aConfigDataSelected['FieldsToWrite']))
                            {
                                $aFieldsToUse = explode(',',$aConfigDataSelected['FieldsToWrite']);  
                                foreach($aFieldsToUse as $sIndex)
                                {
                                    if(preg_match('/\+/',$sIndex))
                                    {
                                        $aStr = explode('+',$sIndex);  
                                        foreach($aStr as $sIndexInField)
                                        if(isset($sVal[$sIndexInField])) 
                                        {
                                            $sIndex = str_replace($sIndexInField,$sVal[$sIndexInField],$sIndex);
                                        }
        
                                    }
                                    $sIndex = str_replace('+','',$sIndex);
                                    if(isset($sVal[$sIndex])) 
                                    {
                                        $sIndex = str_replace($sIndex,$sVal[$sIndex],$sIndex);
                                    }
                                  
                                
                                $aDataToWrite .= $sIndex."\t"; 
                                }
                                $aDataToWrite = explode("\t",$aDataToWrite);
                                if($aDataToWrite[count($aDataToWrite)-1] == "")
                                {
                                    $aDataToWrite = array_slice($aDataToWrite,0,count($aDataToWrite)-1);
                                }

                                if($sFileType=="XLSX")
                                {
                                    if(isset($aConfigDataSelected['FieldMap_ProdConfig_MasterPID'])&& !empty($aConfigDataSelected['FieldMap_ProdConfig_MasterPID']))
                                    {
                                        $aFieldsToUse = explode(',',$aConfigDataSelected['FieldMap_ProdConfig_MasterPID']);  
                                        foreach($aFieldsToUse as $sIndex)
                                        {
                                            if(preg_match('/\+/',$sIndex))
                                            {
                                                $aStr = explode('+',$sIndex);  
                                                foreach($aStr as $sIndexInField)
                                                if(isset($sVal[$sIndexInField])) 
                                                {
                                                    $sIndex = str_replace($sIndexInField,$sVal[$sIndexInField],$sIndex);
                                                }
                
                                            }
                                            $sIndex = str_replace('+','',$sIndex);
                                            if(isset($sVal[$sIndex])) 
                                            {
                                                $sIndex = str_replace($sIndex,$sVal[$sIndex],$sIndex);
                                            }                                  
                                        
                                            $aMappingFieldValues_ProdConfig_MasterPID .= $sIndex.","; 
                                        }
                                        $aMappingFieldValues_ProdConfig_MasterPID = explode(",",$aMappingFieldValues_ProdConfig_MasterPID);
                                        if($aMappingFieldValues_ProdConfig_MasterPID[count($aMappingFieldValues_ProdConfig_MasterPID)-1] == "")
                                        {
                                            $aMappingFieldValues_ProdConfig_MasterPID = array_slice($aMappingFieldValues_ProdConfig_MasterPID,0,count($aMappingFieldValues_ProdConfig_MasterPID)-1);
                                        }
                                    }
                                }
                            }
                            else
                                $aErrors[]= "$sDateStamp [$sUser]: ERROR: FieldsToWrite is not properly defined in configuration";

                        
                            
                                
                            if($recordsDone == $iMaxRec)
                            {
                                $recordsDone = 0;
                            }
                            if($recordsDone == 0)
                            {
                                $sFileName = "";
                                if(isset($aConfigDataSelected['FileNamePrefix'])&& !empty($aConfigDataSelected['FileNamePrefix']))
                                $sFileName .= empty($aConfigDataSelected['FileNamePrefix']) ? "" : $aConfigDataSelected['FileNamePrefix']."_";
                                if(isset($aConfigDataSelected['FileNameFromFields'])&& !empty($aConfigDataSelected['FileNameFromFields']))
                                {
                                    $aFieldsToUse = explode(',',$aConfigDataSelected['FileNameFromFields']);  
                                    foreach($aFieldsToUse as $sIndex)
                                        $sFileName .= empty($sVal[$sIndex]) ? "" : $sVal[$sIndex]."_";
                                }
                                if(isset($aConfigDataSelected['FileNameSuffix'])&& !empty($aConfigDataSelected['FileNameSuffix']))
                                    $sFileNameSuffix = $aConfigDataSelected['FileNameSuffix'];

                                if($numSplits > 0)
                                    fclose($fp);
                                ++$numSplits;
                                $sJobTicketFileName = $sFileName;
                                $sFileName =  $sTmpDir.$sFileName;
                                if($neededSplits > 0)
                                    $sFileName .= "_".$numSplits."_of_".$neededSplits."_";
                                $sFileName .= $sFileNameSuffix;
                                if($sFileType!="XLSX")
                                {
                                if(file_exists($sFileName) && isset($aConfigDataSelected['Append'])&& preg_match('/yes|YES|Y|TRUE|true/',strtolower($aConfigDataSelected['Append'])))
                                {
                                    $fp = fopen($sFileName, "a");
                                }
                                else
                                {
                                    $fp = fopen($sFileName, "w");
                                    if(isset($aConfigDataSelected['HeaderNames'])&& !empty($aConfigDataSelected['HeaderNames']))
                                        {   $aFileHeader = explode(",",$aConfigDataSelected["HeaderNames"]);
                                            if($sFileType=="CSV")  
                                                fputcsv($fp, $aFileHeader, $sFieldDelimiter);
                                            else if($sFileType=="MIXED_CSV") 
                                            {
                                                $aFileHeader =   implode($sFieldDelimiter,$aFileHeader)."\r\n";
                                                fwrite($fp, $aFileHeader);   
                                            }
                                            else if ($sFileType=="FIXED_LENGTH")
                                            {
                                                if(isset($aConfigDataSelected['HeaderSizes'])&& !empty($aConfigDataSelected['HeaderSizes']))
                                                {
                                                    $sHeaderToDump = "";
                                                    $aFileHeaderSizes = explode(",",$aConfigDataSelected["HeaderSizes"]);
                                                    foreach ($aFileHeaderSizes as $index => $size)
                                                    {
                                                        $sHeaderToDump = $sHeaderToDump . str_pad($aRecords[0][$aFileHeader[$index]],$size,$sFieldDelimiter,STR_PAD_RIGHT);
                                                    }
                                                    fwrite($fp, $sHeaderToDump . "\n");
                                                }
                                            }

                                        }
                                }
				}
                            
                                        
                                        
                                
                                
                            }
                            if($sFileType=="CSV")
                            {
                                    $bFileWriting1 =  fputcsv($fp, $aDataToWrite, $sFieldDelimiter);    
                            }
                            else if($sFileType=="MIXED_CSV")
                            {
                                $aDataToWrite =   implode($sFieldDelimiter,$aDataToWrite)."\r\n";
                                $bFileWriting1 =  fwrite($fp, $aDataToWrite);    
                            }
                            else if($sFileType=="FIXED_LENGTH")
                            {
                                if(isset($aConfigDataSelected['FieldsToWriteSizes'])&& !empty($aConfigDataSelected['FieldsToWriteSizes']))
                                {
                                    $sFieldsToDump = "";
                                    $aFieldsToWrite = explode(",", $aConfigDataSelected["FieldsToWrite"]);
                                    $aFieldsToWriteSizes = explode(",",$aConfigDataSelected["FieldsToWriteSizes"]);
                                    foreach ($aFieldsToWriteSizes as $index => $size)
                                    {
                                        $sFieldsToDump = $sFieldsToDump . str_pad($sVal[$aFieldsToWrite[$index]],$size,$sFieldDelimiter,STR_PAD_RIGHT);
                                    }
                                    $bFileWriting1 =  fwrite($fp, $sFieldsToDump . "\n");
                                } 
                            }
                            else if($sFileType=="XLSX")
                            {
                                $sxlsx_MasterPID = $aConfigDataSelected['MasterPID'];
                                $sxlsx_SchemaJobTicket = $aConfigDataSelected['SchemaJobTicket'];
                                $sSheetName_MasterPID = $aConfigDataSelected['SheetNameMasterPID'];
                                $axlsx_FieldsToWriteFromMasterPID = explode(",",$aConfigDataSelected['FieldsToWriteFromMasterPID']);
                                $axlsx_CellIndexOfFieldsToWriteProductConfig = explode(",",$aConfigDataSelected['CellIndexOfFieldsToWrite']);
                                $axlsx_CellIndexForMasterPIDFieldsToWrite = explode(",",$aConfigDataSelected['CellIndexForMasterPIDFieldsToWrite']);
                                $aMappingFieldNames_ProdConfig_MasterPID = explode(",",$aConfigDataSelected["FieldMap_ProdConfig_MasterPID"]);

                                $aFieldsToWriteFromProductConfig = json_encode($aDataToWrite);
                                $aFieldsToWriteFromProductConfig = str_replace(",", '\\""","\\"',$aFieldsToWriteFromProductConfig);
                                $aFieldsToWriteFromProductConfig = str_replace('[', '[\\"', $aFieldsToWriteFromProductConfig);
                                $aFieldsToWriteFromProductConfig = str_replace(']', '\\"]', $aFieldsToWriteFromProductConfig);
                                $aFieldsToWriteFromProductConfig = str_replace('"""', '""', $aFieldsToWriteFromProductConfig);

                                $axlsx_CellIndexOfFieldsToWriteProductConfig = json_encode($axlsx_CellIndexOfFieldsToWriteProductConfig);
                                $axlsx_CellIndexOfFieldsToWriteProductConfig = str_replace(",", '\\""","\\"',$axlsx_CellIndexOfFieldsToWriteProductConfig);
                                $axlsx_CellIndexOfFieldsToWriteProductConfig = str_replace('[', '[\\"', $axlsx_CellIndexOfFieldsToWriteProductConfig);
                                $axlsx_CellIndexOfFieldsToWriteProductConfig = str_replace(']', '\\"]', $axlsx_CellIndexOfFieldsToWriteProductConfig);
                                $axlsx_CellIndexOfFieldsToWriteProductConfig = str_replace('"""', '""', $axlsx_CellIndexOfFieldsToWriteProductConfig);

                                $axlsx_FieldsToWriteFromMasterPID = json_encode($axlsx_FieldsToWriteFromMasterPID);
                                $axlsx_FieldsToWriteFromMasterPID = str_replace(",", '\\""","\\"',$axlsx_FieldsToWriteFromMasterPID);
                                $axlsx_FieldsToWriteFromMasterPID = str_replace('[', '[\\"', $axlsx_FieldsToWriteFromMasterPID);
                                $axlsx_FieldsToWriteFromMasterPID = str_replace(']', '\\"]', $axlsx_FieldsToWriteFromMasterPID);
                                $axlsx_FieldsToWriteFromMasterPID = str_replace('"""', '""', $axlsx_FieldsToWriteFromMasterPID);

                                $axlsx_CellIndexForMasterPIDFieldsToWrite = json_encode($axlsx_CellIndexForMasterPIDFieldsToWrite);
                                $axlsx_CellIndexForMasterPIDFieldsToWrite = str_replace(",", '\\""","\\"',$axlsx_CellIndexForMasterPIDFieldsToWrite);
                                $axlsx_CellIndexForMasterPIDFieldsToWrite = str_replace('[', '[\\"', $axlsx_CellIndexForMasterPIDFieldsToWrite);
                                $axlsx_CellIndexForMasterPIDFieldsToWrite = str_replace(']', '\\"]', $axlsx_CellIndexForMasterPIDFieldsToWrite);
                                $axlsx_CellIndexForMasterPIDFieldsToWrite = str_replace('"""', '""', $axlsx_CellIndexForMasterPIDFieldsToWrite);                               
                    
                                $aMappingFieldNames_ProdConfig_MasterPID = json_encode($aMappingFieldNames_ProdConfig_MasterPID);
                                $aMappingFieldNames_ProdConfig_MasterPID = str_replace(",", '\\""","\\"',$aMappingFieldNames_ProdConfig_MasterPID);
                                $aMappingFieldNames_ProdConfig_MasterPID = str_replace('[', '[\\"', $aMappingFieldNames_ProdConfig_MasterPID);
                                $aMappingFieldNames_ProdConfig_MasterPID = str_replace(']', '\\"]', $aMappingFieldNames_ProdConfig_MasterPID);
                                $aMappingFieldNames_ProdConfig_MasterPID = str_replace('"""', '""', $aMappingFieldNames_ProdConfig_MasterPID);
                             
                                $aParts =  explode("|", $aMappingFieldValues_ProdConfig_MasterPID[2]); //green1|xyz => green1
                                $aMappingFieldValues_ProdConfig_MasterPID[2] = $aParts[0];
                                $aMappingFieldValues_ProdConfig_MasterPID = json_encode($aMappingFieldValues_ProdConfig_MasterPID);
                                $aMappingFieldValues_ProdConfig_MasterPID = str_replace(",", '\\""","\\"',$aMappingFieldValues_ProdConfig_MasterPID);
                                $aMappingFieldValues_ProdConfig_MasterPID = str_replace('[', '[\\"', $aMappingFieldValues_ProdConfig_MasterPID);
                                $aMappingFieldValues_ProdConfig_MasterPID = str_replace(']', '\\"]', $aMappingFieldValues_ProdConfig_MasterPID);
                                $aMappingFieldValues_ProdConfig_MasterPID = str_replace('"""', '""', $aMappingFieldValues_ProdConfig_MasterPID);
                                $aMappingFieldValues_ProdConfig_MasterPID = str_replace('"\\""\\"', '"\\""NA"\\"', $aMappingFieldValues_ProdConfig_MasterPID);

                                $sFileName = $sTmpDir.$sJobTicketFileName.$aDataToWrite[3].$sFileNameSuffix; //$aDataToWrite[3] = JobCode
                                $command = "python3 $sPythonScriptLocation $sxlsx_MasterPID $sxlsx_SchemaJobTicket $sFileName $axlsx_CellIndexForMasterPIDFieldsToWrite $axlsx_FieldsToWriteFromMasterPID $axlsx_CellIndexOfFieldsToWriteProductConfig $aFieldsToWriteFromProductConfig $aMappingFieldNames_ProdConfig_MasterPID $aMappingFieldValues_ProdConfig_MasterPID $sSheetName_MasterPID";
                                
                                $sScriptMessage = exec($command, $output);
                                $aMappingFieldValues_ProdConfig_MasterPID = ''; 
                                $bFileWriting1 = true;

                               if(strcmp($sScriptMessage, "TRUE") != 0)
                                {
                                    $aErrors[]= "$sDateStamp [$sUser]: ERROR: ".$sScriptMessage;
                                    $bFileWriting1 = false;
                                }
                                else
                                {
                                    echo "\n$sDateStamp [$sUser]: File successfully written as: ".$sJobTicketFileName.$aDataToWrite[3].$sFileNameSuffix."\n";
                                }                                                          
                            }

                            $aFileNamesStatus = array_merge($aFileNamesStatus, array($sFileName => $bFileWriting1));
                            $recordsDone++;
                        
                        }
                        return;
                }
            

?>