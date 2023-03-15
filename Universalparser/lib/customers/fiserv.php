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

function mapping_fiserv($aInputFile)
    {
     
    
            $aFILE_HEADER_RECORD = array(
                "RECORD_TYPE_ID" => 2,
                "BATCH_ID" => 6,
                "PRODUCT_ID" => 15,
                "SHIPPING_METHOD"=>2,
                "FILLER"=>868,
            );    

            $aDETAIL_RECORDS = array (
                "RECORD_TYPE_ID" => 2,
                "CARD_ID"=>16,
                "CARDHOLDER_NAME"=>26,
                "PAN" => 19,
                "EXP_DATE"=>5,
                "FNAME"=>20,
                "LNAME"=>20,
                "TRACK_1"=>77,
                "TRACK_2" => 38,
                "CVV2" => 3,
                "ICVV" =>3,
                "PIN" => 32,
                "COMPANY_NAME" => 50,
                "MAIL_NAME" => 50,
                "MAIL_TO_ADDRESS_1" => 100,
                "MAIL_TO_ADDRESS_2" => 100,
                "MAIL_CITY" => 85,
                "MAIL_STATE" => 85,
                "MAIL_ZIP" => 10,
                "SHIP_COUNTRY"=>60,
                "CARRIER_MSG"=>100,
                "FILLER"=>100,
            );

            $aFILE_FOOTER_RECORD = array(
                "RECORD_TYPE_ID" => 2,
                "TOTAL_RECORDS"=>7,
                "FILLER"=>191,
            );


            /********************************************************
             *  PARSING 
             ********************************************************/

            $iFileHeaderNo = 0;
            $iBatchHeaderNo = 0;
            $iBatchFooterNo = 0;
            $iFileFooterNo = 0;
            $iRecordNo=0;
            $iBatchID = "";
            $sOrderNumber=0;
            foreach($aInputFile as $iIndex => $sRecord){
                
                $sBOM = pack('H*','EFBBBF');        
                $sRecord = preg_replace("/^$sBOM/", '', $sRecord);

                switch(substr($sRecord,0,2))
                    {
                        case "BH":
                            $iBatchHeaderNo++;
                            $BATCH_LOG_ID = trim(substr($sRecord,3,6));
                            $PRODUCT_ID = trim(substr($sRecord,9,15));
                            $SHIPPING_METHOD = trim(substr($sRecord,24,2));
                            $iPos = 0;
                            
                    
                            foreach($aFILE_HEADER_RECORD as $sKey => $iLength)
                            {
                            $aFileRecords["BATCH_HEADER"][$BATCH_LOG_ID][$PRODUCT_ID][$SHIPPING_METHOD][$sKey] =  substr($sRecord, $iPos, $iLength);
                                $iPos+=$iLength;
                            } 
                            break;
                        case "DT":
                            $iRecordNo++;
                            $iPos = 0;
                            foreach($aDETAIL_RECORDS as $sKey => $iLength)
                            {
                                
                                $aFileRecords["BATCH_HEADER"][$BATCH_LOG_ID][$PRODUCT_ID][$SHIPPING_METHOD]["DETAIL_RECORD"][$iRecordNo][$sKey] = substr($sRecord, $iPos, $iLength);
                                $iPos+=$iLength;
                            } 
                            break;
                        case "BF":
                            $iFileFooterNo++;
                            $iPos = 0;
                            foreach($aFILE_FOOTER_RECORD as $sKey => $iLength)
                            {
                                
                                $aFileRecords["BATCH_FOOTER"][$sKey] = substr($sRecord, $iPos, $iLength);
                                $iPos+=$iLength;
                            } 
                            break;
                        }
                    }
                $iNumberOfRecords = $iRecordNo;

                        return $aFileRecords;
}

?>