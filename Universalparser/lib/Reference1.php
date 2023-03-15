<?php
use JsonSchema\Uri\Retrievers\Curl;
/*AVAILABLE VARIABLES TO USE
$Customer -> It's the name from Producst_configuration_xy.csv defined as Customer
$Customer3 -> It's first 3 of Customer Name
$ProductName -> It's the name from Products_confiugration_xy.csv defined as TagProductID
$ProductName3 -> It's first 2 of Product Name
$ProductID -> It's the Product ID identifier as processor send it to us in embossing file, it should be same as field ProductID in products_configuration_xy.csv
$FullName -> Cardholder Fullname for shipping
$SerialNumber -> It's 6-9 digits serial number padded with 0, always continues. It's not per job, it's per how many files has been processed total
$RecordNo -> It's sequence per Record per file
$PANLast4 -> Last 4 of the PAN
$BIN -> BIN Number
*/
$CustomerName3 = substr($Customer,0,3);
$ProductName3 = substr($ProductName,0,3);

$Reference1 = "$CustomerName3-$ProductName3-$SerialNumber-$PANLast4";

//$Reference1 = "$sCustomerName3-$sProductName3-$sSerialNumber-$sPAN4";
return $Reference1;
?>