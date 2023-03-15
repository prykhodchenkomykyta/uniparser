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

    function mapping_revx($aInputFile)
    {
        $aFILE_HEADER_RECORD = array(
            "SEQ_NUMBER" => 6,
            "ODER_NUMBER" => 10,
            "INSTITUTION_NAME" => 30,
            "INSTITUTION_SUB_NAME"=>30,
            "CONTACT_PHONE_NUMBER"=>15,
            "BRANCH_ID"=>10, //ProgramID 15002 RevX
            "FILLER"=>868,
            "LF"=>1
        );    
        $aDETAIL_RECORDS = array (
            "SEQ_NUM" => 6,
            "MPLREF_NO"=>20,
            "ACC_NO"=>20,
            "EMB_NAME" => 26,
            "EMB_NAME_2" => 26,
            "EXP_DATE"=>4,
            "TRACK_1"=>78,
            "TRACK_2" => 39,
            "IMPRINT" => 20,
            "NUMBER_OF_CARDS" =>1,
            "PIN" => 16,
            "MAIL_TO_ADDRESS_1" => 40,
            "MAIL_TO_ADDRESS_2" => 40,
            "MAIL_TO_ADDRESS_3" => 40,
            "MAIL_TO_ADDRESS_4" => 40,
            "MAIL_TO_ZIP" => 10,
            "DISTRIBUTION_CENTER_ID"=>40,
            "ITEM_BAR_CODE"=>30,
            "BUNDLER_BAR_CODE"=>30,
            "LOCAL_PHONE_NO"=>20,
            "PROXY_ACC_NO_TYPE"=>4,
            "BANK_ID"=>20,
            "PROXY_ACC_NO"=>20,
            "EXT_ACC_NO"=>50,
            "EXT_ACC_PRESENTATION"=>50,
            "MPLACC"=>30,
            "CARD_OPT_1"=>30,
            "CARD_OPT_2"=>30,
            "CARD_OPT_3"=>30,
            "CARD_OPT_4"=>30,
            "CARD_OPT_5"=>30,
            "CARD_OPT_6"=>30,
            "FILLER"=>99,
            "EXTENDED_FORMAT_FIELDS"=>2000,
            "LF"=>1,
        );
        $aFILE_FOOTER_RECORD = array(
            "SEQ_NO" => 6,
            "DETAIL_REC"=>6,
            "FILLER"=>987,
            "LF"=>1,
        );

                $iFileHeaderNo = 0;
                $iBatchHeaderNo = 0;
                $iDetailRecordNo = 0;
                $iBatchFooterNo = 0;
                $iFileFooterNo = 0;
                $iRecordNo=0;
                $iBatchID = "";
                $sOrderNumber=0;
            
                foreach($aInputFile as $sRecord){ 
                    $sBOM = pack('H*','EFBBBF');        
                    $sRecord = preg_replace("/^$sBOM/", '', $sRecord);
            
                    switch(substr($sRecord,0,6))
                        {
                            case "000000":
                                $iBatchHeaderNo++;
                                $sOrderNumber= substr($sRecord,6,10);
                                $iPos = 0;
                                foreach($aFILE_HEADER_RECORD as $sKey => $iLength)
                                {
                                    $aFileRecords["BATCH_HEADER"][$iBatchHeaderNo][$sKey] =  substr($sRecord, $iPos, $iLength);
                                    $iPos+=$iLength;
                                } 
                                break;
                            case "999999":
                                $iFileFooterNo++;
                                $iPos = 0;
                                foreach($aFILE_FOOTER_RECORD as $sKey => $iLength)
                                {
                                    
                                    $aFileRecords["BATCH_FOOTER"][$iBatchHeaderNo][$sKey] = substr($sRecord, $iPos, $iLength);
                                    $iPos+=$iLength;
                                } 
                                break;
                            default:
                                    $iDetailRecordNo++;
                                    $sRecordNumber = substr($sRecord,0,6);
                                    $iPos = 0;
                                    //$iBatchId = trim(substr($sRecord,2,30));
                                    foreach($aDETAIL_RECORDS as $sKey => $iLength)
                                    {
                                        $aFileRecords["BATCH_HEADER"][$iBatchHeaderNo]['DETAIL RECORD'][$sRecordNumber][$sKey] = substr($sRecord, $iPos, $iLength);
                                        $iPos+=$iLength;
                
                                    } 
                                    break;
                            }
                        }
                        $iNumberOfRecords = $iDetailRecordNo;
                        return $aFileRecords;
    }

?>