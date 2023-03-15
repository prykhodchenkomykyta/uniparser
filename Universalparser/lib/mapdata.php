<?php

/*
Array in order:
Processor Name
Input Path
Processed Path
Products Configuration
*/

function mapDataFromConfig($sConfigurationFile, $aRecord, $sProcessor)
{
    $aMappedData = getProductsList($sConfigurationFile);
    $aMappedDataBeforeValidation = array();
    foreach($aMappedData as $aDataLine)
    {
        
        $sFlag = $aDataLine['Flag'];
        if(strtolower(trim($sFlag)) == 'received')
        {
            $sDataField = $aDataLine['DataField'];
            $sMappingField = $aDataLine[$sProcessor];
            if(preg_match('/^col_/',strtolower($sMappingField)))
            {
                $aColumns = explode('_',$sMappingField);
                if(count($aColumns)==2)
                {
                    $sColumn = trim($aColumns[1])-1;
                    $aMappedDataBeforeValidation = array_merge($aMappedDataBeforeValidation,array($sDataField => $aRecord[$sColumn]));
                }
            }
            if(preg_match('/^pos_/',strtolower($sMappingField)))
            {
                if (!preg_match('/^ef/', strtolower($aRecord))) 
                {
                    $aColumns = explode('_', $sMappingField);
                    if (count($aColumns) == 3) {
                        $iPos = $aColumns[1] - 1;
                        $iLength = $aColumns[2];
                        $aMappedDataBeforeValidation = array_merge($aMappedDataBeforeValidation, array($sDataField => trim(mb_substr($aRecord, $iPos, $iLength, "UTF-8"))));
                    }
                }
            }
            if(preg_match('/^h1_/',strtolower($sMappingField)))
            {
                if (preg_match('/^h1/', strtolower($aRecord['h1']))) 
                {
                    $aColumns = explode('_', $sMappingField);
                    if (count($aColumns) == 3) {
                        $iPos = $aColumns[1] - 1;
                        $iLength = $aColumns[2];
                        $aMappedDataBeforeValidation = array_merge($aMappedDataBeforeValidation, array($sDataField => trim(mb_substr($aRecord['h1'], $iPos, $iLength, "UTF-8"))));
                    }
                }
            }
            if(preg_match('/^h2_/',strtolower($sMappingField)))
            {
                if (preg_match('/^h2/', strtolower($aRecord['h2']))) 
                {
                    $aColumns = explode('_', $sMappingField);
                    if (count($aColumns) == 3) {
                        $iPos = $aColumns[1] - 1;
                        $iLength = $aColumns[2];
                        $aMappedDataBeforeValidation = array_merge($aMappedDataBeforeValidation, array($sDataField => trim(mb_substr($aRecord['h2'], $iPos, $iLength, "UTF-8"))));
                    }
                }
            }
            if(preg_match('/^h3_/',strtolower($sMappingField)))
            {
                if (preg_match('/^h3/', strtolower($aRecord['h3']))) 
                {
                    $aColumns = explode('_', $sMappingField);
                    if (count($aColumns) == 3) {
                        $iPos = $aColumns[1] - 1;
                        $iLength = $aColumns[2];
                        $aMappedDataBeforeValidation = array_merge($aMappedDataBeforeValidation, array($sDataField => trim(mb_substr($aRecord['h3'], $iPos, $iLength, "UTF-8"))));
                    }
                }
            }
            if(preg_match('/^d4_/',strtolower($sMappingField)))
            {
                if (preg_match('/^d4/', strtolower($aRecord['d4']))) 
                {
                    $aColumns = explode('_', $sMappingField);
                    if (count($aColumns) == 3) {
                        $iPos = $aColumns[1] - 1;
                        $iLength = $aColumns[2];
                        $aMappedDataBeforeValidation = array_merge($aMappedDataBeforeValidation, array($sDataField => trim(mb_substr($aRecord['d4'], $iPos, $iLength, "UTF-8"))));
                    }
                }
            }
            if(preg_match('/^fh_/',strtolower($sMappingField)))
            {
                if (preg_match('/^ef/', strtolower($aRecord))) {
                    $aColumns = explode('_', $sMappingField);
                    if (count($aColumns) == 3) {
                        $iPos = $aColumns[1] - 1;
                        $iLength = $aColumns[2];
                        $aMappedDataBeforeValidation = array_merge($aMappedDataBeforeValidation, array($sDataField => trim(mb_substr($aRecord, $iPos, $iLength, "UTF-8"))));
                    }
                }
            }
            if(empty($sMappingField) || strtolower($sMappingField) == "na")
            {
                $aMappedDataBeforeValidation = array_merge($aMappedDataBeforeValidation,array($sDataField => ""));
            }
            if(preg_match('/^xml_/',strtolower($sMappingField)))   
            {
                $aColumns = explode('_',$sMappingField,2);
                $aMappedDataBeforeValidation = array_merge($aMappedDataBeforeValidation,array($sDataField => trim(getvalue($aColumns[1], $aRecord))));
            }


        }
        else
        {
            //INIT VALUES
            $sDataField = $aDataLine['DataField'];
            $aMappedDataBeforeValidation = array_merge($aMappedDataBeforeValidation,array($sDataField => ""));
        }

    }
    return  $aMappedDataBeforeValidation;
}

?>