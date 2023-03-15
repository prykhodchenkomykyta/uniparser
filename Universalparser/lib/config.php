<?php

/*
Array in order:
Processor Name
Input Path
Processed Path
Products Configuration
*/

function getSystemConfig()
{
    
}
// $aConfigList = array(
    
//     // array("LITHIC",__DIR__."/../in/lithic/",__DIR__."/../in/privacy/","/home/erutberg/Radovan/Products_Configuration_Bancard.csv"),
//     // array("QRAILS",__DIR__."/../in/qrails/",__DIR__."/../in/privacy/","/home/erutberg/Radovan/Products_Configuration_Qrails.csv"),
//     // array("REVX",__DIR__."/../in/revx/",__DIR__."/../in/privacy/","/home/erutberg/Radovan/Products_Configuration_Revx.csv"),
//     // array("GALILEO",__DIR__."/../in/galileo/",__DIR__."/../in/galileo/","/home/erutberg/Radovan/Products_Configuration.csv"),
//     // array("MARQETA",__DIR__."/../in/marqeta/",__DIR__."/../in/marqeta/","/home/erutberg/Radovan/Products_Configuration_Marqeta.csv"),
//     // array("QOLO",__DIR__."/../in/qolo/",__DIR__."/../in/qolo/","/home/erutberg/Radovan/Products_Configuration_Qolo.csv"),
//     // array("FISERV",__DIR__."/../in/fiserv/",__DIR__."/../in/fiserv/","/home/erutberg/Radovan/Products_Configuration_Fiserv.csv"),
//     // array("CORECARD_DB",__DIR__."/../in/corecard_debit/",__DIR__."/../in/corecard_debit/","/home/erutberg/Radovan/Products_Configuration_CoreCard_DB.csv"),
//     // array("CORECARD_CR",__DIR__."/../in/corecard_credit/",__DIR__."/../in/corecard_credit/","/home/erutberg/Radovan/Products_Configuration_CoreCard_CR.csv"),
//     // array("DESERVE",__DIR__."/../in/deserve/",__DIR__."/../in/deserve/","/home/erutberg/Radovan/Products_Configuration_Deserve.csv"),
//     // array("BANCARD",__DIR__."/../in/bancard/",__DIR__."/../in/bancard/","/home/erutberg/Radovan/Products_Configuration_Bancard.csv"),
//     // array("CARTA",__DIR__."/../in/carta/",__DIR__."/../in/carta/","/home/erutberg/Radovan/Products_Configuration_Carta.csv"),

// );



//OUTPUT FILES FOR ANOTHER SYSTEMS
$sOutputDir = "/var/tmp/TSSS/DataPrep/in/";
$sBulkOutputDir = "/var/tmp/TSSS/Files/USPS/BULK/";
$sBulkFedexOutputDir =  "/var/tmp/TSSS/Files/FEDEX/BULK/";
$sMailOutputDir = "/var/tmp/TSSS/Files/USPS/";
$sMailMergeOutputDir = "/var/tmp/TSSS/Files/MAILMERGE/";
$sFedexOutputDir = "/var/tmp/TSSS/Files/FEDEX/";

//COMPOSITE FIELD IS FOR OPERATOR TO DECIDE WHAT VALUE THEY WANT TO HAVE IN SHIPMENTS FILE
$sCompositeFieldReference1Dir = $sWorkdir . "Reference1.php";

//OUTPUT FILES FOR OTHER FACILITY
$sIntShipmentDir = "/var/tmp/TSSS/Files/TAGPL/";

//REPORTS TO DELIVER TO CUSTOMER
$sConfirmationReportDir = "/var/TSSS/Files/Reports/";
$sShipmentReportDir = "/var/TSSS/Files/Reports/galileo/waiting/";

//INTERNAL HELPER INSTEAD OF DB TO USE SERIAL NUMBER FOR SHIPMENT IT WAS IN THE FILE
$SerialNumberLocal =  $sWorkdir . "SerialNumberCounter.csv";
$sSerialNumberurl = "https://atlas.tagsystems.net/barcode/serial/";

//WORKING DIRECTORY FOR EVERYTHING BEFORE FINAL FILE IS CREATED/MOVED TO ITS LOCATION
$sTmpDir = __DIR__ . "/tmp/";
 


?>