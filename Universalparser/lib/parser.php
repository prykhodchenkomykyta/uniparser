<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 09/27/2022
Revision: 09/27/2022
Name: Radovan Jakus
Version: 1.0
Notes: Global Variables Definition
******************************/



    function Parser($sProcessor,$inputDir,$sDataMapConfiguration)
    {
        global $sDateStamp;
        global $sUser;
        global $sCustomerName;
        global $sProcessedFilename;
        global $sSerialNumberurl;
        global $SerialNumberLocal; 
        global $SerialNumberOfDigits;


        $aInputFile = file($inputDir, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";
        echo "$sDateStamp [$sUser]: Starting Parsing Data\n";
        $aRecordData = array();
        switch(strtolower($sProcessor))
        {
            case "lithic":
            case "qrails":  
            case "corecard_db":
            case "corecard_cr":
                foreach($aInputFile as $aData)
                {
                    $aInputFileData =str_getcsv($aData);
                    $aRecordData[] =$aInputFileData;
                }  
                break;
            case "highnote":
                foreach($aInputFile as $aData)
                {
                    $aInputFileData =str_getcsv($aData,'|');
                    $aRecordData[] =$aInputFileData;
                }  
                break;     
            case "galileo":
                $aRecordData =$aInputFile;
                break;
            case "qolo":
                $sFunctionName =  'mapping_'.strtolower($sProcessor);
                $aRecordData = call_user_func($sFunctionName,$inputDir);
                break;
            case "marqeta":
                $sFunctionName =  'mapping_'.strtolower($sProcessor);
                $aRecordData = call_user_func($sFunctionName,$inputDir);
                break;                 
            case "gps":
                $sFunctionName =  'mapping_'.strtolower($sProcessor);
                ##$aRecordData = call_user_func($sFunctionName,$aInputFile);
                $aRecordData = call_user_func($sFunctionName,$inputDir);
                break;
            case "dc_bank":
                $sFunctionName =  'mapping_'.strtolower($sProcessor);
                ##$aRecordData = call_user_func($sFunctionName,$aInputFile);
                $aRecordData = call_user_func($sFunctionName,$inputDir);
                break;
            default:
                $sFunctionName =  'mapping_'.strtolower($sProcessor);
                ##$aRecordData = call_user_func($sFunctionName,$aInputFile);
                $aRecordData = call_user_func($sFunctionName,$inputDir);
                break;
    
        }
            

        return  $aRecordData;
    }

 