<?php
function MaskPANData($sDataToMask,$BIN,$iPANLength)
{
    $iPanPosition = strpos($sDataToMask,$BIN);
    $iMaskedCharsln = abs($iPANLength-4-6);
    if($iPanPosition!==false)
    {
        $sMaskedData = substr_replace($sDataToMask,"XXXXXX",$iPanPosition+6,$iMaskedCharsln);
    }
    else
    {
        $sMaskedData = "unable to mask PAN in data - view not allowed";
    }
    return $sMaskedData;
}

//////START 8 HELPERS FUNCTION SEQUENCE //
?>