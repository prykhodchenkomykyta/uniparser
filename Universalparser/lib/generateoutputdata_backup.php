<?php

$aFileWriting1 = array();
$aFileNames = array();

function GenerateOutputData($aMappedData, $sConfigurationFilePath, $sConfigurationName)
{
    global $aConfigDataSelected;
    global $aErrors;
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
            $aStatements = explode('|',$sStatement);
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

    //Distinc
    if(isset($aConfigDataSelected['DistincBy'])&& !empty($aConfigDataSelected['DistincBy']))
    {  
        $aDistinctElements = explode(',',$aConfigDataSelected['DistincBy']);
        $objQuery
        -> from ($aMappedData)
        -> distinct(...$aDistinctElements);
        $aMappedData = $objQuery->getResults();
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
                    $sDateStamp = date('Y-m-d H:i:s', time());
                    $sUser = get_current_user();

                    $numSplits = 0;
                    $recordsDone = 0;
                    $fp = null;
                    $neededSplits = 0;
                    $aFileWriting1 = array();
                    $aFileNames = array();
                    $bFileWriting1 = false;
                    $sFileName = "";
                    $NoOfCardsPerGroup = count($aRecords);

                    // if(isset($aConfigDataSelected['UniqueOccurence'])&& preg_match('/yes|YES|Y|TRUE|true/',strtolower($aConfigDataSelected['UniqueOccurence'])))
                    // {  
                    //     $objQuery = \Linq\LinqFactory::createLinq();
                    //     $objQuery
                    //     -> from ($aRecords)
                    //     -> distinct();
                    //     $aRecords = $objQuery->getResults();
                    // }
                    if(isset($aConfigDataSelected['UniqueOccurence'])&& preg_match('/yes|YES|Y|TRUE|true/',strtolower($aConfigDataSelected['UniqueOccurence'])))
                    {  
                           // $aRecords = array_map("unserialize", array_unique(array_map("serialize", $aRecords)));
                        $aRecords= array_unique($aRecords,SORT_NUMERIC); 
                        // foreach($aRecords as $sProps => $sVal)
                        // {
                        //     foreach($sVal as $sPropsName => $sPropVals)
                        //     {
                        //         $temp = array_unique(array_column($sVal, $sPropVals));
                        //         $aRecords = array_intersect_key($aRecords, $temp);
                        //     }
                        // }
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

                    if(isset($aConfigDataSelected['OutputFolder'])&& !empty($aConfigDataSelected['OutputFolder']))
                            $sOutputDir = $aConfigDataSelected['OutputFolder'];
                    else
                            $aErrors[]= "$sDateStamp [$sUser]: ERROR: OutputFolder is not properly defined in configuration";

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
                                  
                                
                                $aDataToWrite .= $sIndex.","; 
                                }
                                $aDataToWrite = explode(",",$aDataToWrite);

                            }
                            else
                                $aErrors[]= "$sDateStamp [$sUser]: ERROR: FieldsToWrite is not properly defined in configuration";

                        
                            
                                
                            if($recordsDone == $iMaxRec)
                            {
                                $recordsDone = 0;
                                foreach($aFileWriting1 as $bFileWritingStatus)
                                {
                                    if($bFileWritingStatus == false)
                                        $bFileWriting1 = false;
                                    else
                                        $bFileWriting1 = true;
                                        
                                }
                                $aFileWriting1 = array();
                                if($bFileWriting1)
                                { 
                                    echo "$sDateStamp [$sUser]: File succesfully written as: $sFileName.\n";

                                    //     $sOutputFilename = $sOutputDir.basename($sFileName);
                                    //     $bFileMoved = rename("$sFileName", "$sOutputFilename");
                                    // if($bFileMoved)
                                    // echo "$sDateStamp [$sUser]: File succesfully written as: $sOutputFilename.\n";
                                    // else
                                    //     $aErrors[]= "$sDateStamp [$sUser]: ERROR: Moving $sFileName from ".$sTmpDir." to ".$sOutputDir ." directory failed.\n";
        
                                
                                }
                                else
                                {
                                    $aErrors[]= "$sDateStamp [$sUser]: ERROR: Writing $sFileName failed \n";
                                    fclose($fp);
        
                                }
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
                                $sFileName =  $sTmpDir.$sFileName;
                                if($neededSplits > 0)
                                    $sFileName .= "_".$numSplits."_of_".$neededSplits."_";
                                $sFileName .= $sFileNameSuffix;
                                if(file_exists($sFileName))
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
                                    }
                                }
                            
                                      
                                        
                                
                                
                            }
                            if($sFileType=="CSV")
                            {
                                    $aFileWriting1[] =  fputcsv($fp, $aDataToWrite, $sFieldDelimiter); 
                                    $aFileNames =  array_merge($aFileNames, array('FileName' => $sFileName , 'FileNameStatus' => $aFileWriting1));
   
                            }
                            else if($sFileType=="MIXED_CSV")
                            {
                                $aDataToWrite =   implode($sFieldDelimiter,$aDataToWrite)."\r\n";
                                $aFileWriting1[] =  fwrite($fp, $aDataToWrite);    
                            }
                            $recordsDone++;
                        
                        }
                    foreach($aFileWriting1 as $bFileWritingStatus)
                    {
                        if($bFileWritingStatus == false)
                            $bFileWriting1 = false;
                        else
                            $bFileWriting1 = true;
                            
                    }
                    $aFileWriting1 = array();
                    if($bFileWriting1)
                    { 
                        echo "$sDateStamp [$sUser]: File succesfully written as $sFileName\n";

                        //     $sOutputFilename = $sOutputDir.basename($sFileName);
                        //     $bFileMoved = rename("$sFileName", "$sOutputFilename");
                        // if($bFileMoved)
                        // echo "$sDateStamp [$sUser]: File succesfully written as: $sOutputFilename.\n";
                        // else
                        //     $aErrors[]= "$sDateStamp [$sUser]: ERROR: Moving $sFileName from ".$sTmpDir." to ".$sOutputDir ." directory failed.\n";
                    }
                    else
                    {
                        $aErrors[]= "$sDateStamp [$sUser]: ERROR: Writing $sFileName failed \n";
                        fclose($fp);

                    }
                }
            

?>