<?php
////START PART 2 FUNCTION READING CONFIGRATION FILE OF PRODUCTS AND ITS ATTRIBUTES//
function getProductsList($sProductConfigFile){
    $productsConfiguration = file($sProductConfigFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    $sConfigFilename = basename($sProductConfigFile);
    $header = array_shift($productsConfiguration);
    $sDelimiter = ",";
    $productsConfigurationWithHeader = array();
    $aBINs = array();
    foreach($productsConfiguration as $aData)
    {
        if(preg_match("/^#/",$aData, $comments))
        {
            //COMMENT IN COFIGURATION FILE, GO TO NEXT
            continue 1;
        }
        else
        {
            $productsConfigurationWithHeader[] = array_combine(str_getcsv($header,$sDelimiter),str_getcsv($aData,$sDelimiter));
        }
    }
    $iCounter = 0;
    foreach($productsConfigurationWithHeader as $aProducts)
    {
           
            if(preg_match('/products/',strtolower($sConfigFilename)))
            {
                if(empty($aProducts['CardStockID']))
                {
                    $aProducts['CardStockID'] = "NA";
                }
    
                    foreach($aProducts as $key => $value)
                    {
                        $aBINs[$aProducts['BIN']][$aProducts['ProductID']][$aProducts['CardStockID']][$key]= $value;
    
                    }
            }
            else
            {
                $iCounter++;
               
                foreach($aProducts as $key => $value)
                {
                    $aBINs[$iCounter][$key]= $value;
                    
                }
                
            }
        }
        return $aBINs;

}

//        echo"aBINs:\n";
//    print_r($aBINs);
////END PART 2 FUNCTION READING CONFIGRATION FILE OF PRODUCTS AND ITS ATTRIBUTES//
?>