<?php


function getDetailOverview($aInputData){

    global $sCustomerName;
    global $aBINs;
   
    global $iNoErrorRecs;
    $iTotalNumberOfRecords=0;

    //$aInputData = array_slice($aInputData,0,1);
    //print_r($aInputData);
    echo "\n\t Detail Summary of records in file Shipment Method and per Product \n";
    printf('            %-10s| %-18s|  %-11s|  %-21s|  %-25s|  %-12s','Customer', 'ProductID-Name', 'CardStockID','ShipmentMethodID-Name','BULK Group/Location ID','Total Records');
    echo"\n";

    foreach($aInputData as $sBIN => $aRecordsPerBIN)
    {
        foreach($aRecordsPerBIN as $keyShipmentMethod => $aShipRecords)
        { 
            foreach($aShipRecords as $keyPerProduct => $aProdRecords)
            {
                foreach($aProdRecords as $keyCardStock => $aCardStocks)
                {
                    //$sShipmentAlias= $aBINs[$sBIN][$keyPerProduct][$keyCardStock]['ShippingMethodsBulk'][$keyShipmentMethod];
                    $iGroup = 0;

                    foreach($aCardStocks as $keyBulkShipment => $aRecords)
                    {
                        if(!empty(explode("_",$keyBulkShipment)[1]))
                            $keyBulkShipment = explode("_",$keyBulkShipment)[1]."_".++$iGroup."-".explode("_",$keyBulkShipment)[3];
                        
                        $sCustomerName = $aBINs[$sBIN]['Customer'];
                        $sProductAlias = $aBINs[$sBIN][$keyPerProduct][$keyCardStock]['Product'];
                        // if(!isset($sProductAlias))
                        // {
                        //     echo"BIN $sBIN \n";
                        //     echo"keyPerProduct $keyPerProduct\n";
                        //     echo"keyCardStock $keyCardStock\n";
                        //     print_r($aInputData);
                        // }

    
                        //$sBulkShipmentAlias= $aBINs[$sBIN][$keyPerProduct][$keyCardStock]['ShippingMethodsBulk'][$keyBulkShipment];
                        $iTotalNoPerService = 0;

                        //foreach($aGroupIDs as $keyGroupID => $aRecords)
                        {
                            $iTotalNoPerService = count($aRecords);
                            $iTotalNumberOfRecords+=count($aRecords);
                            $keyGroupID = "";
                            printf('            %-10s| %-18s|  %-11s|  %-21s|  %-25s|  %12d ',$sCustomerName,($keyPerProduct."-".$sProductAlias), $keyCardStock, ($keyShipmentMethod),$keyBulkShipment,  $iTotalNoPerService);
                            echo"\n";
                        }
                    }
                }
            }
        }
    }
        printf('           %-87s','--------------------------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-64s    %-20s    %20d', 'Total Good Processed Records','',$iTotalNumberOfRecords);
        echo"\n\n";

        printf('           %-87s','--------------------------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-64s    %-20s    %20d', 'Total Bad/Errored Records in file','',$iNoErrorRecs);
        echo"\n\n";

        printf('           %-87s','--------------------------------------------------------------------------------------------------------------------');
        echo"\n";
        printf('           %-64s    %-20s    %20d', 'Total Records in file','',$iTotalNumberOfRecords+$iNoErrorRecs);
        echo"\n\n";
        
    //return  $aCollectShipment;

}

//////END 10 HELPERS GIVES OVERVIEW OF PRODUCTS IN THE FILE AND ITS AMOUNT, THEY WOULD LIKE TO CHANGE AS REPORT THAT THEY CAN PRINT //


?>