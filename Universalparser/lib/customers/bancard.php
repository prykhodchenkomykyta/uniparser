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

function mapping_bancard($aInputFile)
{
    $aDETAIL_RECORDS = array
    (

        "IDENTIFIER"=> "{",   //IDENTIFIER
        "DESCRIPTION" => "[",   //DESCRIPTION
        "GENERATION_DATE" => "¡ ",   //GENERATION DATE
        "PAN_EMB" => "# ", // PAN with 4 SPACES
        "EXP" => "+ ", //EXPIRATION
        "CARDHOLDER_NAME"=> "$ ", //PLASTIC NAME
        "TRACK1" => "> ", //TRACK1
        "TRACK2" => ";", //TRACK2
        "CVV2" => "      *", //CVV2
        "COMPANY" => "!", //Company
        "ICVV" => "& ", //CVV
        "PIN" => "}", //PIN
        "PROD" => ")", //PROD vs TEST
        "TYPE" => "¿", //PLASTIC TYPE

    );

    $iRecordNo=0;
    foreach($aInputFile as $iIndex => $sRecord){
        
        $sBOM = pack('H*','EFBBBF');        
        $sRecord = preg_replace("/^$sBOM/", '', $sRecord);

        $arr = preg_split('/[{|[|;|!|}|)|¿]|(¡ )|(# )|(\+ )|(\$ )|(> )|(      \*)|(& )/', $sRecord,-1, PREG_SPLIT_OFFSET_CAPTURE);
        $CurrentPos = 0;
        $PositionAfterCurrent = 0;
    
        for($i=0;$i<count($arr);$i++) 
        {
            if($i==0)
            {
                $StartMICPos =$arr[$i][1]; 
                $DataLen = strlen($arr[$i][0]);
            }
            else
            {
                $StartMICPos =$arr[$i-1][1];
                $DataLen = strlen($arr[$i-1][0]);

            }
            $EndMICPos = $arr[$i][1];
            $MICLength = $EndMICPos -$StartMICPos -$DataLen;
            $CurrentPos +=$DataLen;
            $MIC = substr($sRecord, $CurrentPos, $MICLength);
            $CurrentPos +=$MICLength;

            foreach($aDETAIL_RECORDS as $sKey => $sID)
            {
                if($MIC==$sID)
                    $aFileRecords[$arr[0][0]][$sKey] =  $arr[$i][0];
            }  
        }
            $iRecordNo++;
    }

        // echo("PARSING:");
        // print_r($aFileRecords);
        return $aFileRecords;
}

?>