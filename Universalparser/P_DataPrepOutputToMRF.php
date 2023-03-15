<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 12/5/2020
Revision: 10/14/2022
Name: Radovan Jakus
Version: 1.59
Notes: Adding Revolut Alternative Configuration
******************************/
/*Production Environment:*/
$sInputDir = "/var/TSSS/DataPrep/out/";
$sInputFilePath;
$sOutputDir = "/var/TSSS/Files/";
$sProcessedDir = "/var/TSSS/DataPrep/machine_processed/";
//$sProcessedDir = "/var/TSSS/DataPrep/out/";
$sMordorProductsConfig = "/opt/mordor2go_4/products_config.yml";
$sAltMordorProductsConfig = "/opt/mordor2go_4/alt_products_config.yml";
$sProductConfigFileGalileo = "/opt/rpms/Parser_Plugin/configurations/Products_Configuration.csv";
$sProductConfigFileLithic = "/opt/rpms/Parser_Plugin/configurations/Products_Configuration_Lithic.csv";
$sProductConfigFileI2C = "/opt/rpms/Parser_Plugin/configurations/Products_Configuration_I2C.csv";
$sProductConfigFileMarqeta = "/opt/rpms/Parser_Plugin/configurations/Products_Configuration_Marqeta.csv";
$sProductConfigFileQolo ="/opt/rpms/Parser_Plugin/configurations/Products_Configuration_Qolo.csv";
$sProductConfigFileQrails ="/opt/rpms/Parser_Plugin/configurations/Products_Configuration_Qrails.csv";
$sProductConfigFileBancard ="/opt/rpms/Parser_Plugin/configurations/Products_Configuration_Bancard.csv";
$sProductConfigFileVault ="/opt/rpms/Parser_Plugin/configurations/Products_Configuration_GPS.csv";
$sProductConfigFileHighnote ="/opt/rpms/Parser_Plugin/configurations/Products_Configuration_Highnote.csv";
$sImagesDir = "/var/TSSS/Files/Logos/";
$sImagesDirMachineLevel = "X:\\Logos\\";
$sImagesDirRevolut ="/var/TSSS/Files/RevolutLogos/";
$sImagesDirMachineLevelRevolut = "X:\\RevolutLogos\\";
$sKMSpreProdDir = "/home/gabreu@tagdpn.us/";


header('Content-Type: application/json');
date_default_timezone_set ("America/New_York");
$sDateStamp = date('Y-m-d H:i:s', time());
$sUser = get_current_user();

$sScriptFullName=(__FILE__);
//echo("Script name $sScriptName");
//ob_start();


$key = ftok($sScriptFullName, "1");
$lock = sem_get($key, 1);
if (sem_acquire($lock, 1) !== false) {
  
    $sScriptName = basename($sScriptFullName);
    echo "$sDateStamp [$sUser]: Starting Script:  $sScriptName \n";

    $aBINs[]=getProductsList($sProductConfigFileGalileo)+getProductsList($sProductConfigFileLithic)+getProductsList($sProductConfigFileI2C)+getProductsList($sProductConfigFileMarqeta)+getProductsList($sProductConfigFileQolo)+getProductsList($sProductConfigFileQrails)+getProductsList($sProductConfigFileBancard)+getProductsList($sProductConfigFileVault)+getProductsList($sProductConfigFileHighnote);

    define("MAX_CHARS_VERTICAL_CARD",12);
    define("MAX_CHARS_HORIZONTAL_CARD",26);


    $sCustomerName;
    $sProductName;
    $sBIN;
    $aBINs['531972']=array("Customer"=>"EP_FINANCIAL",
                           "Profile"=>"EP_FINANCIAL_221952348");
    $aBINs['525831']=array("Customer"=>"SAVE",
                        "Profile"=>"Save_Debit");
    $aBINs['411023']=array("Customer"=>"COT",
                            "Profile"=>"COT_VISA_CREDIT_C3");
    $aBINs['442581']=array("Customer" => "ZORO",
                            "Profile"=>"P_ZORO_VISA_D1_SECORA_S");
    $aBINs['412055']=array("Customer" => "LILI",
                            "Profile"=>"P_LILI_VISA_D1_SECORA_S");
    $aBINs['412407']=array("Customer" => "MESH",
                            "Profile"=>"MESH_VISA_D1_ST_TOPAZ");
    $aBINs['541636']=array("Customer" => "MARYGOLD",
                            "Profile"=>"P_MARYGOLD_MC_45_xx_xx_94_98_85_SECORA_S_V162307013");  
    $aBINs['547018']=array("Customer" => "GEMINI",
                            "Profile"=>"P_GEMINI_20_94_NXP_P71_V01"); 
    $aBINs['409177']=array("Customer" => "OXYGEN",
                            "Profile"=>"P_OXYGENE_VISA_D5_NXP_P71_V01_v143");
    $aBINs['555740']=array("Customer" => "FOUND",
                            "Profile"=>"P_FOUND_MC_20_94_ST_TOPAZ_122_V01_v143");
    $aBINs['522419']=array("Customer" => "LITHIC",
                            "Profile"=>"LITHIC_MC_20_94_ST_TOPAZ_122_V01");
    $aBINs['534271']=array("Customer" => "MERCURY",
                            "Profile"=>"MERCURY_MC_20_94_ST_TOPAZ_123_V01"); 
    $aBINs['525355']=array("Customer" => "REVOLUT",
                            "Profile"=>"P_REVOLUT_MC_35_61_xx_xx_KONA_V184037016"); 
    $aBINs['521747']=array("Customer" => "REVOLUT",
                            "Profile"=>"P_REVOLUT_MC_35_61_xx_xx_KONA_V184037016"); 
    $aBINs['516529']=array("Customer" => "REVOLUT",
                            "Profile"=>"P_REVOLUT_MC_35_61_xx_xx_KONA_V184037016");
    $aBINs['406000']=array("Customer" => "REVOLUT",
                            "Profile"=>"REVOLUT_VISA_D5_PECTORAL_123_V01");                      
    $aBINs['528898']=array("Customer" => "DESERVE",
                            "Profile"=>"DESERVE_MC_20_94_ST_TOPAZ_122_V01_v143"); 
    $aBINs['418016']=array("Customer" => "REVX",
                            "Profile"=>"REVX_VISA_D5_SECORA_S_V01");
    $aBINs['519605']=array("Customer" => "ZOLVE",
                            "Profile"=>"ZOLVE_MC_34_94_ST_TOPAZ_122_V01");
    $aBINs['558541']=array("Customer" => "ZOLVE",
                            "Profile"=>"ZOLVE_MC_34_61_94_98_ST_TOPAZ_122_V01");
    $aBINs['553742']=array("Customer" => "STIFEL",
                            "Profile"=>"STIFEL_MC_20_94_NXP_P71_V01");
    $aBINs['546854']=array("Customer" => "POMELO",
                            "Profile"=>"POMELO_MC_20_94_ST_TOPAZ_122_V01");
    $aBINs['418066']=array("Customer" => "ZENDA",
                            "Profile"=>"ZENDA_VISA_D5_ST_TOPAZ_123_V01");
    $aBINs['415621']=array("Customer" => "C2FO",
                            "Profile"=>"C2FO_VISA_C3_ST_TOPAZ_123_V01");
    $aBINs['415839']=array("Customer" => "MYCD-MILLIONS",
                            "Profile"=>"MYCD-MILLIONS_D5_ST_TOPAZ_123_V01");
    $aBINs['519369']=array("Customer" => "MERCANTILE",
                            "Profile"=>"MERCANTILE_MC_20_94_ST_TOPAZ_123_V01");
    $aBINs['411172']=array("Customer" => "GLIDE",
                            "Profile"=>"GLIDE_VISA_D1_ST_TOPAZ_123_V01");
    $aBINs['525691']=array("Customer" => "HRBLOCK",
                            "Profile"=>"HRBK_MC_20_61_94_98_ST_TOPAZ_123_V01");
    $aBINs['449940']=array("Customer" => "LASCO",
                            "Profile"=>"LASCO_VISA_C3_ST_TOPAZ_123_V01");
    $aBINs['485141']=array("Customer"=>"CLEO",
                            "Profile"=>"CLEO_VISA_C1_ST_TOPAZ_123");
    $aBINs['456713']=array("Customer" => "MBTEST",
                            "Profile"=>"MBTEST");
    $aBINs['444607']=array("Customer" => "DOORDASH",
                            "Profile"=>"DOORDASH_VISA_D1_ST_TOPAZ_13");
    $aBINs['523215']=array("Customer"=>"CUSTOMER_BANK",
                            "Profile"=>"CUSTOMER_BANK_MC_20_94_ST_TOPAZ_12");
    $aBINs['515856']=array("Customer"=>"PERPAY",
                            "Profile"=>"PERPAY_MC_20_ST_TOPAZ_12");
    $aBINs['558345']=array("Customer" => "GLORIFI",
                            "Profile"=>"GLORIFI_CR_MT_MC_20_94_NXP_P71"); 
    $aBINs['555684']=array("Customer" => "DESERVE-CELTIC",
                            "Profile"=>"DESERVE_CELTIC_MC_CREDIT_ST_TOPAZ_12"); 
    $aBINs['52403218']=array("Customer" => "POWER",
                            "Profile"=>"POWER_MC_CREDIT_ST_TOPAZ_12"); 
    $aBINs['44662500']=array("Customer" => "AMC",
                            "Profile"=>"AMC_VISA_C3_ST_TOPAZ_13"); 
    $aBINs['44211500']=array("Customer" => "ROCKETMONEY",
                            "Profile"=>"ROCKETMONEY_VISA_C3_ST_TOPAZ_13"); 


    //array_merge($aBINs, getProductsList($sProductConfigFile));
    //print_r($aBINs);
    // array_push($aBINs,array_shift(getProductsList($sProductConfigFile)));
    //print_r($aBINs);

    $sKMC = "HSMARLIB.KEY.TEST.KONA.KMC.CP";
    $sKMCKCV = "7D2BB7";
    //ISD Manufacturing key
    $sISD = "HSMARLIB.KEY.TEST.KONA.KMC.CP";
    $sISDKCV = "7D2BB7";

    $aErrors;
    $iNoErrorRecs;
                                                    






    $aOptions = getopt("p::n::", array("keyword::","directory::"));
    $sInputFilePath;


    if(!empty($aOptions['p'])){
        $sInputFilePath = $aOptions['p'];
        echo "$sDateStamp [$sUser]: Using full path option \n";
        if(file_exists($sInputFilePath))
            {
                DataPrepToMRF($sInputFilePath, $sOutputDir, $sProcessedDir);
            }
            else
            {
                die("\nERROR: The file does not exist. Check the name or path of the file. FILE:".$sInputFilePath);  
            }
    }else if(!empty($aOptions['n'])){
        $sInputFilePath = $sInputDir."\\".$aOptions['n'];
        echo "$sDateStamp [$sUser]: Using file name option \n";
        if(file_exists($sInputFilePath))
        {
            DataPrepToMRF($sInputFilePath, $sOutputDir, $sProcessedDir);
        }
        else
        {
            die("\nERROR: The file does not exist. Check the name or path of the file. FILE:".$sInputFilePath);  
        }
    }
    else if(!empty($aOptions['keyword']) && !empty($aOptions['directory'])){
        $sKeyWords = $aOptions['keyword'];
        $sInputDir = $aOptions['directory'];
        $sListOfKeyWords = explode(",",$sKeyWords);
        $aInputFiles = array();
        foreach($sListOfKeyWords as $sPattern)
        {
            //array_push($aInputFiles, glob("$sInputDir*$sPattern*.csv"));
            $aInputFiles = array_merge($aInputFiles, glob("$sInputDir*$sPattern*.csv"));
            
        }
        echo "$sDateStamp [$sUser]: Using filename pattern in selected directory $sInputDir\n";
        if($aInputFiles){
            echo "$sDateStamp [$sUser]: List of files to be processed: \n";
            foreach($aInputFiles as $sInputFilePath){
                $aFileNaming = explode('_',basename($sInputFilePath));
                if(preg_match('/\d/',$aFileNaming[0]) && preg_match('/\d/',$aFileNaming[1]))
                    continue;
                    echo "\t".basename($sInputFilePath)." \n";
            }
            foreach($aInputFiles as $sInputFilePath){
                $aFileNaming = explode('_',basename($sInputFilePath));
                if(preg_match('/\d/',$aFileNaming[0]) && preg_match('/\d/',$aFileNaming[1]))
                    continue;

                if(preg_match('/KMS/',strtoupper($sInputFilePath)))
                {
                    $bFileMoved = copy($sInputFilePath, $sKMSpreProdDir.basename($sInputFilePath));     
                }
                DataPrepToMRF($sInputFilePath, $sOutputDir, $sProcessedDir);
            }
        }
        else
        {
            echo "$sDateStamp [$sUser]: There are no files to be processed in directory. The directory does not contain customer files. Directory: $sInputDir\n";
        }
    }
    else if(!empty($aOptions['keyword'])){
        $sPattern = $aOptions['keyword'];
        $sKeyWords = $aOptions['keyword'];
        $sListOfKeyWords = explode(",",$sKeyWords);
        $aInputFiles = array();
        foreach($sListOfKeyWords as $sPattern)
        {
            $aInputFiles = array_merge($aInputFiles, glob("$sInputDir*$sPattern*.csv"));  
        }
        echo "$sDateStamp [$sUser]: Using filename pattern option in predefined folder $sInputDir \n";
        if($aInputFiles){
            echo "$sDateStamp [$sUser]: List of files to be processed: \n";
            foreach($aInputFiles as $sInputFilePath){
                $aFileNaming = explode('_',basename($sInputFilePath));
                if(preg_match('/\d/',$aFileNaming[0]) && preg_match('/\d/',$aFileNaming[1]))
                    continue;
                    echo "\t".basename($sInputFilePath)." \n";
            }
            foreach($aInputFiles as $sInputFilePath){
                $aFileNaming = explode('_',basename($sInputFilePath));
                if(preg_match('/\d/',$aFileNaming[0]) && preg_match('/\d/',$aFileNaming[1]))
                    continue;

                if(preg_match('/KMS/',strtoupper($sInputFilePath)))
                {
                    $bFileMoved = copy($sInputFilePath, $sKMSpreProdDir.basename($sInputFilePath));     
                }
                DataPrepToMRF($sInputFilePath, $sOutputDir, $sProcessedDir);
            }
        }
        else
        {
            echo "$sDateStamp [$sUser]: There are no files to be processed in directory. The directory does not contain customer files. Directory: $sInputDir\n";
        }
    }

    else{
    echo "$sDateStamp [$sUser]: Using option to process files from predefined directory automatically. Directory: $sInputDir \n";
    $aInputFiles = glob("$sInputDir*.csv");
        if($aInputFiles){
            echo "$sDateStamp [$sUser]: List of files to be processed: \n";
            foreach($aInputFiles as $sInputFilePath){
                $aFileNaming = explode('_',basename($sInputFilePath));
                if(preg_match('/\d/',$aFileNaming[0]) && preg_match('/\d/',$aFileNaming[1]))
                    continue;
                    echo "\t".basename($sInputFilePath)." \n";
            }
            foreach($aInputFiles as $sInputFilePath){
                $aFileNaming = explode('_',basename($sInputFilePath));
                if(preg_match('/\d/',$aFileNaming[0]) && preg_match('/\d/',$aFileNaming[1]))
                    continue;

                if(preg_match('/KMS/',strtoupper($sInputFilePath)))
                {
                    $bFileMoved = copy($sInputFilePath, $sKMSpreProdDir.basename($sInputFilePath));     
                }
                DataPrepToMRF($sInputFilePath, $sOutputDir, $sProcessedDir);
            }
        }
        else
        {
            echo "$sDateStamp [$sUser]: There are no files to be processed in directory. The directory does not contain customer files. Directory: $sInputDir\n";
        }

    }

    /*@DataPrepToMRF function process DataPrep output csv file and transform it to Machine Readable File. 
        $inputDir - string path to the file that will be processed
        $outputDir - string path to the directory where file will be saved
    */

    if(isset($aErrors))
    {
        if($aErrors!=null)
        {
        echo "\n$sDateStamp [$sUser]: ERRORS during processing, to take action is needed: \n";
        foreach($aErrors as $sErrorMessage)
        {
            echo  $sErrorMessage;
        }
        }
    }

            echo "$sDateStamp [$sUser]: Ending Script";

} 
else 
{
    $sScriptName = basename($sScriptFullName);
    echo "\n$sDateStamp [$sUser]: The another instance of the script $sScriptName is running...  \n";
}

function DataPrepToMRF($inputDir, $outputDir, $processedDir){ 
    global $sDateStamp;
    global $sUser;
    global $sCustomerName;
    global $sProductName;
    global $sBIN;
    global $aBINs;
    global $sKMC;
    global $sKMCKCV;
    global $sISD;
    global $sISDKCV;
    global $sImagesDir;
    global $sImagesFileExtension;
    global $sImagesDirMachineLevel;
    global $sImagesDirRevolut;
    global $sImagesDirMachineLevelRevolut;
    global $iMax;
    global $aErrors;
    global $iNoErrorRecs;
    
    
    $sProcessedFilename = basename($inputDir);
    
    

    echo "\n$sDateStamp [$sUser]: Processing file: $inputDir \n";

    //$sFileName = str_replace("MS_DATAPREP", "MRF", basename($inputDir, "csv"))."xml";
    //$sJobName = preg_replace('/^(\w+)?DATAPREP_\d+_/', "", basename($inputDir, "csv"));
    $sFileName = str_replace("MS_DATAPREP", "MRF", basename($inputDir, "csv"))."xml";
    $sJobName = preg_replace('/^(\w+)?DATAPREP_\d+_/', "", basename($inputDir, "csv"));

    $sJobName = str_replace(".","",$sJobName);
    $aInputFile = file($inputDir, FILE_SKIP_EMPTY_LINES);
    $aCSVCol = explode(",", $aInputFile[0]);
    //$sBatchID = str_replace("/","-",$aCSVCol[0]);
    $sBIN = substr($aCSVCol[3],1,6);
    $sBINExtended = substr($aCSVCol[3],1,8);
    
    $iNoErrorRecs = 0;
    $iNoOfRecordsFromFile = count($aInputFile);

   
    if(isset($aBINs[0][$sBIN]['Customer']))
    {
        $sCustomerName = $aBINs[0][$sBIN]['Customer'];
        $sProductName =  $aBINs[0][$sBIN]['Profile'];
    }
    else if(isset($aBINs[0][$sBINExtended]['Customer']))
    {
        
        $sBIN = $sBINExtended;
        $sCustomerName = $aBINs[0][$sBIN]['Customer'];
        $sProductName =  $aBINs[0][$sBIN]['Profile'];
    }
    else if(isset($aBINs[$sBINExtended]['Customer']))
    {
        $sBIN = $sBINExtended;
        $sCustomerName = $aBINs[$sBIN]['Customer'];
        $sProductName = $aBINs[$sBIN]['Profile'];
    }
    else
    {
        $sCustomerName = $aBINs[$sBIN]['Customer'];
        $sProductName = $aBINs[$sBIN]['Profile'];
    }
  

    $sBatchID = explode("/", $aCSVCol[0])[0];



 if($sBIN == "553742")
    {
        if(strstr($sProcessedFilename, '_SLV_'))
        {
            $sProductName = "STIFEL_SLV_MC_20_94_NXP_P71";
        }
        if(strstr($sProcessedFilename, '_ROSE_'))
        {
            $sProductName = "STIFEL_ROSE_MC_20_94_NXP_P71";
        }
    };

 if($sBIN == "412055")
    {
        if(strstr($sProcessedFilename, '_PRO_'))
        {
            $sProductName = "LILI_PRO_VISA_D1";
        }
    };

    if($sBIN == "558345")
        {
            if(strstr($sProcessedFilename, '_CR_MT_BRS_'))
            {
                $sProductName = "GLORIFI_CR_MT_BRS_MC_20_94_NXP_P71";
            }
            if(strstr($sProcessedFilename, '_CR_PL_BLU_'))
            {
                $sProductName = "GLORIFI_CR_PL_BLU_MC_20_94_SECORA_S";
            }
            if(strstr($sProcessedFilename, '_CR_PL_CNST_'))
            {
                $sProductName = "GLORIFI_CR_PL_CNST_MC_20_94_SECORA_S";
            }
            if(strstr($sProcessedFilename, '_CR_MT_BLU_'))
            {
                $sProductName = "GLORIFI_CR_MT_BLU_MC_20_94_SECORA_S";
            }
            if(strstr($sProcessedFilename, '_CR_MT_CNST_'))
            {
                $sProductName = "GLORIFI_CR_MT_CNST_MC_20_94_SECORA_S";
            }
        
        };


    if($sBIN == "555740")
        {
            if(strstr($sProcessedFilename, '_DI_'))
            {
                $sProductName = "FOUND_MC_20_94_ST_TOPAZ_122_V01_DI";
            }
            if(strstr($sProcessedFilename, '_CT_'))
            {
                $sProductName = "FOUND_MC_20_ST_TOPAZ_122_CT";
            }
        };

        if($sBIN == "522419")
        {
            if(strstr($sProcessedFilename, '_PAY_TGTHR_'))
            {
                $sProductName = "PAY_TGTHR_MC_20_94_ST_TOPAZ_122";
            }
            if(strstr($sProcessedFilename, '_WEDGE_'))
            {
                $sProductName = "WEDGE_MC_20_94_ST_TOPAZ_122";
            }
        };

    


        if($sBIN == "525355" || $sBIN == "521747" || $sBIN == "516529"|| $sBIN == "406000")
        {

            global $sMordorProductsConfig;
            global $sAltMordorProductsConfig;

            if(!file_exists($sMordorProductsConfig))
                $$aErrors[]  = "$sDateStamp [$sUser]: ERROR: The $sMordorProductsConfig mordor configuration file could not be found, please update path to the current one. \n";
          
                $sMordorProducts = file($sMordorProductsConfig, FILE_SKIP_EMPTY_LINES);

                   
                    $sImageIdMaxLength = 0;
                    foreach($sMordorProducts as $sMordorProduct)
                    {
               
                        {
                            $sMordorProductNoComments = trim(preg_replace("/#(.*)$/", "", $sMordorProduct));
                            $aProductRecord = explode(":",$sMordorProductNoComments);
                
                            if(count($aProductRecord)==2){

                            $sImageID = $aProductRecord[0];
                            $sProduct = $aProductRecord[1];
                            
                            
                                if(strstr($sProcessedFilename, '_'.$sImageID.'_'))
                                {
                                    $sImageIdLength = strlen($sImageID);
                                    if($sImageIdMaxLength < $sImageIdLength)
                                    {
                                        $sImageIdMaxLength = $sImageIdLength;
                                        $sTrimName = preg_replace("/(\"P_)|(_v14\d\")/", "", $sProduct);
                                        $sProductName = trim(preg_replace("/(REVOLUT)/","REVOLUT_".$sImageID,$sTrimName));
                                        if(strstr($sProcessedFilename, '_IMG_'))
                                        {
                                            $sProductName .= "_IMG";
                                        }
                                    }
                                }
                            }

                        }

                    }
           
        };

        if($sBIN == "409177")
        {
            if(strstr($sProcessedFilename, '_FIRE_'))
            {
                $sProductName = "OXYGENE_FIRE_VISA_D5_NXP_P71_V01_v143";
            }
            if(strstr($sProcessedFilename, '_AIR_'))
            {
                $sProductName = "OXYGENE_AIR_VISA_D5_NXP_P71_V01_v143";
            }
            if(strstr($sProcessedFilename, '_WATER_'))
            {
                $sProductName = "OXYGENE_WATER_VISA_D5_NXP_P71_V01_v143";
            }

            if(strstr($sProcessedFilename, '_EARTH_'))
            {
            // $sProductName = "OXYGENE_EARTH_VISA_D5_SECORA_S_V01_143";
        $sProductName = "OXYGENE_EARTH_VISA_D5_ST_TOPAZ_123__V01";
            }
        };

        if($sBIN == "546854")
        {
            if(strstr($sProcessedFilename, '_WHT_'))
            {
                $sProductName = "POMELO_WHT_MC_20_94_ST_TOPAZ_122_V01";
            }
            if(strstr($sProcessedFilename, '_PNK_'))
            {
                $sProductName = "POMELO_PNK_MC_20_94_ST_TOPAZ_122_V01";
            }
            if(strstr($sProcessedFilename, '_GRN_'))
            {
                $sProductName = "POMELO_GRN_MC_20_94_ST_TOPAZ_122_V01";
            }
        };

      

        
        if(isset($aBINs[0][$sBIN]['TagProduct'])){
                
            $sTagProduct =$aBINs[0][$sBIN]['TagProduct'];
            foreach($sTagProduct as $ProductID => $sProductName)
            {
                    if(strstr($sProcessedFilename, $ProductID))
                    {
                        $sProductName = $sProductName;
                        break;
                    }
                    else if($ProductID=='STD'&& !strstr($sProcessedFilename, "_".$ProductID."_"))
                    {
                        $sProductName = $sProductName;
                    }
            }
        }
    
        echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n";
        echo "$sDateStamp [$sUser]: ProductName: $sProductName \n";
        echo "$sDateStamp [$sUser]: Batch of the file: $sBatchID \n";
        
        
        //$sBOM = 0xEF 0xBB 0xBF;
        $sXMLHeader = "
        <InputData>
            <Units>
                <Unit Name=\"".$sCustomerName."_".$sBatchID."_".$sJobName."\" Type=\"Job\" Priority=\"1\">
                <Comment/>
                <Product>".$sProductName."</Product>
                <CustomerUnitData InputFormat=\"Hex\"/>";
        $sXMLFooter = "
                </Unit>
            </Units>
        </InputData>";
        $sXMLBody = "";

        $iCounter = 0;
        foreach($aInputFile as $aLine)
        {
            $aData = str_getcsv($aLine, ",", '"');
            //$aData = explode(",", $aLine);

            //INITIALIZE VALUES
            $Token = "";
            $sLastName = "";
            $sFirstName = "";
            $sFullName = "";
            $sTrack1 = "";
            $sTrack2 = "";
            $sTrack3 = "";
            $sPAN = "";
            $sPAN1_2 = "";
            $sPAN2_2 = "";
            $sPAN1_4 = "";
            $sPAN2_4 = "";
            $sPAN3_4 = "";
            $sPAN4_4 = "";
            $sExpDate = "";
            $sEMBName = "";
            $aNames = "";
            $sEMBNameLine1 = "";
            $sEMBNameLine2 = "";
            $sEMBNameLine3 = "";
            $sEMBNameLine4 = "";
            $sName1= "";
            $sName2= "";
            $sName3= "";
            $sName4= "";
            $sName5= "";
            $sName6= "";
            $sCompanyName = "";
            $sCVV2 = "";
            $sQRCode = "";
            $sCIDNo = "";
            $sCID = "";
            $sChipData = "";
            $sPrepersoChipData ="";
            $sLogo = "";
            $sLogoFullPath = "";
            $sPRN = "";
            $sREV_EMB3 = "";
            $sREV_EMB4_Line1 = "";
            $sREV_EMB4_Line2 = "";
            $sREV_EMB3_LIMIT_HOR = "";
            $sREV_EMB4_LIMIT_HOR = "";
            $sAdditionalEmbossField = "";
        
            if(preg_match('/ALT/',$aData[0]))
            {         
                if($sBIN == "525355" || $sBIN == "521747" || $sBIN == "516529"|| $sBIN == "406000")
                {

                    if(!file_exists($sMordorProductsConfig))
                        $$aErrors[]  = "$sDateStamp [$sUser]: ERROR: The $sMordorProductsConfig mordor configuration file could not be found, please update path to the current one. \n";
                
                        
                          
                            $sMordorProducts = file($sAltMordorProductsConfig, FILE_SKIP_EMPTY_LINES);
                            
                            $sImageIdMaxLength = 0;
                            foreach($sMordorProducts as $sMordorProduct)
                            {
                            /* if(preg_match("/#/",$sMordorProduct, $comments))
                                {
                                    echo "Nothing to do, it's a comment";
                                }
                                else*/
                                {
                                    $sMordorProductNoComments = trim(preg_replace("/#(.*)$/", "", $sMordorProduct));
                                    $aProductRecord = explode(":",$sMordorProductNoComments);
                        
                                    if(count($aProductRecord)==2){

                                    $sImageID = $aProductRecord[0];
                                    $sProduct = $aProductRecord[1];
                                    
                                    
                                        if(strstr($sProcessedFilename, '_'.$sImageID.'_'))
                                        {
                                            $sImageIdLength = strlen($sImageID);
                                            if($sImageIdMaxLength < $sImageIdLength)
                                            {
                                                $sImageIdMaxLength = $sImageIdLength;
                                                $sTrimName = preg_replace("/(\"P_)|(_v14\d\")/", "", $sProduct);
                                                $sProductName = trim(preg_replace("/(REVOLUT)/","REVOLUT_".$sImageID,$sTrimName));
                                                if(strstr($sProcessedFilename, '_IMG_'))
                                                {
                                                    $sProductName .= "_IMG";
                                                }
                                            }
                                        }
                                    }

                                }

                            }
                
                };

                $sXMLHeader = "
                <InputData>
                    <Units>
                        <Unit Name=\"".$sCustomerName."_".$sBatchID."_".$sJobName."\" Type=\"Job\" Priority=\"1\">
                        <Comment/>
                        <Product>".$sProductName."</Product>
                        <CustomerUnitData InputFormat=\"Hex\"/>";
                $sXMLFooter = "
                        </Unit>
                    </Units>
                </InputData>";
            }


            $sToken = strtok($aData[2], "\^");
            $sToken = strtok("\^");

            $sLastName=trim(strtok($sToken, "\/"));
            $sFirstName=trim(strtok("\/"));
            $sFullName = "$sFirstName $sLastName";

            $sTrack1 = trim(substr($aData[2],1,strlen($aData[2])-2));
            $sTrack2 = trim(substr($aData[3],1,strlen($aData[3])-2));
            $sPAN = trim(substr($aData[3],1,4)." ".substr($aData[3],5,4))." ".substr($aData[3],9,4)." ".substr($aData[3],13,4);
            $sPAN1_2 = trim(substr($aData[3],1,4)." ".substr($aData[3],5,4));
            $sPAN2_2 = trim(substr($aData[3],9,4)." ".substr($aData[3],13,4));
            $sPAN1_4 = trim(substr($aData[3],1,4));
            $sPAN2_4 = trim(substr($aData[3],5,4));
            $sPAN3_4 = trim(substr($aData[3],9,4));
            $sPAN4_4 = trim(substr($aData[3],13,4));
            $sExpDate = trim(substr($aData[3],20,2)."/".substr($aData[3],18,2));
            //$sEMBName = trim(str_replace(array('"',"&","<",">","'",),array("&quot;","&amp;","&lt;","&gt;","&apos;"),$aData[6]));

            $sEMBName = trim($aData[6]);
            // echo"BEFORE sEMBName $sEMBName\n";
            //$sEMBName = utf8_decode($sEMBName);
            //$sEMBName = iconv('Windows-1252', 'UTF-8//IGNORE', $sEMBName);

            // $Bin = bin2hex($sEMBName);
            //echo"AFTER HEX $Bin\n";
            //$sEMBNAME = '<![CDATA['.$sEMBName.']]>';
            //echo"AFTER sEMBName $sEMBName\n";

            $aNames =explode(" ", $sEMBName);

            $sName1= (empty($aNames[0])) ? "" : $aNames[0];
            $sName2= (empty($aNames[1])) ? "" : $aNames[1];
            $sName3= (empty($aNames[2])) ? "" : $aNames[2];
            $sName4= (empty($aNames[3])) ? "" : $aNames[3];
            $sName5= (empty($aNames[4])) ? "" : $aNames[4];
            $sName6= (empty($aNames[5])) ? "" : $aNames[5];

            $sEMBNameLine1 =  splitCHN($sEMBName,$sFirstName,$sLastName)[0];
            $sEMBNameLine2 =  splitCHN($sEMBName,$sFirstName,$sLastName)[1];
            $sEMBNameLine3 =  shortenCHNwithDot($sEMBName);
            $sEMBNameLine4 =  shortenCHN($sEMBName);


        // $sCompanyName = (empty($aData[4])) ? '': trim(str_replace(array(";","?",'"',"&","<",">","'",),array("","","","&amp;","&lt;","&gt;","&apos;"),$aData[4]));
            $sCompanyName = (empty($aData[4])) ? '': trim($aData[4]);


            //  echo"BEFORE sCompanyName $sCompanyName\n";
            // // //$sEMBName = utf8_decode($sEMBName);
            // // //$sEMBName = iconv('Windows-1252', 'UTF-8//IGNORE', $sEMBName);
            // // //$sCompanyName = utf8_decode($sCompanyName);
            //  $Bin = bin2hex($sCompanyName);
            // echo"AFTER HEX $Bin\n";
            // //$sEMBNAME = '<![CDATA['.$sEMBName.']]>';
            // echo"AFTER sCompanyName $sCompanyName\n";

            //$sCompanyName = '<![CDATA['.$sCompanyName .']]>';

        if(empty($sCompanyName))
        {
                $sREV_EMB3 = "";
                $sREV_EMB4_Line1 = "";
                $sREV_EMB4_Line2 =  $sEMBName;
                $sREV_EMB3_LIMIT_HOR = "";
                $sREV_EMB4_LIMIT_HOR = $sEMBNameLine4;
        }
        else
        {
                $sREV_EMB3 = $sEMBName;
                $sREV_EMB3_LIMIT_HOR = $sEMBNameLine4;
                if(preg_match('/\^/',$sCompanyName))
                {
                    $aCompanyNameSplit = explode("^", trim($sCompanyName));
                    if(!empty($aCompanyNameSplit[0]))
                    {
                        $sREV_EMB4_Line1 = $aCompanyNameSplit[0];
                        $sREV_EMB4_Line2 = $aCompanyNameSplit[1]; 
                        $sREV_EMB4_LIMIT_HOR =  $aCompanyNameSplit[0];
                    }
                    else
                    {
                        $sREV_EMB4_Line1 = "";
                        $sREV_EMB4_Line2 = $aCompanyNameSplit[1];
                        $sREV_EMB4_LIMIT_HOR = $sCompanyName;
                    }
                
                }
                else
                {
                    $sREV_EMB4_Line1 = trim($sCompanyName);
                }
        }

        if($sBIN == "520690") //VAULT
        {
            $bIsDelimiterPresent = preg_match("/|/",$sCompanyName);
            if(empty($sCompanyName))
            {
                $sCompanyName = "";
                $sTrack3 = "";
                $sAdditionalEmbossField = "";
            }
            else if($bIsDelimiterPresent)
            {
                $dataSplit = explode("|",$sCompanyName);
                if((empty($dataSplit[0])) && (isset($aData[0])))
                {
                        $sCompanyName = "";   
                }
                else
                {
                    $sCompanyName =  $dataSplit[0];
                }
                if((empty($dataSplit[1])) && (isset($aData[1])))
                {
                        $sTrack3 = "";   
                }
                else
                {
                        $sTrack3 = $dataSplit[1];
                }
                if((empty($dataSplit[2])) && (isset($aData[2])))
                {
                        $sAdditionalEmbossField = "";   
                }
                else
                {
                        $sAdditionalEmbossField = trim($dataSplit[2]);
                }
                
            }
        }

        if($sBIN == "418016")
        {
                $sCompanyName  = "";
                $sQRCode = (empty($aData[4])) ? "": trim(str_replace("&","&amp;",str_replace([";","?"],"",$aData[4])));
        }
        if($sBIN == "412407")//MESH
        {
                $sCompanyName = $sEMBName;
            if(preg_match('/\|/',$aData[4]))
            {
                $aDataSplit = explode("|",$aData[4]);
                $sCIDNo =  (empty($aDataSplit[0])) ? "": trim(str_replace("&","&amp;",str_replace([";","?"],"",$aDataSplit[0])));
                $sLogoIndicator = $aDataSplit[1];
                if($sLogoIndicator==1)
                {
                    $aLogoFiles = glob("$sImagesDir*".$sCompanyName."*");
                    if($aLogoFiles)
                    {
                    echo "$sDateStamp [$sUser]: Logo file with ID: ".$sCompanyName." is found in: ".$aLogoFiles[0]."\n";
                    $sLogoFullPath = $sImagesDirMachineLevel.basename($aLogoFiles[0]);
                    $sLogo = basename($aLogoFiles[0]);
                    }
                    else 
                    {
                    $sError = "$sDateStamp [$sUser]: ERROR: Logo file with ID: ".$sCompanyName." is not found in: ".$sImagesDir.". Please check directory and make sure that logo named as logo ID is in directory.\n";
                    $aErrors[] = $sError;
                    echo $sError;
                    $sLogoFullPath = "LOGO NOT FOUND, CHECK DIRECTORY";
                    $sLogo = $sCompanyName;
                    }

                }
                else
                {
                    $sLogo = "";
                    $sLogoFullPath = "";
                }
                
            }
            else
            {
                    $sCIDNo =  (empty($aData[4])) ? "": trim(str_replace("&","&amp;",str_replace([";","?"],"",$aData[4])));
            }
            $sCID = "CID: $sCIDNo\t$sExpDate";
        }

   


        if($sBIN == "411172" || $sBIN =="519369")//Mercantile,Glide
        {
            $bIsDelimiterPresent = preg_match("/|/",$sCompanyName);
            if(empty($sCompanyName))
            {
                $sLogo = "";
                $sQRCode = "";
                $sCompanyName = "";
            }
            else if($bIsDelimiterPresent)
            {
                $dataSplit = explode("|",$sCompanyName);
                if((empty($dataSplit[0])) && (isset($aData[0])))
                {
                        $sLogo = "";   
                }
                else
                {

                        //CHECK if logo file exists
                        $aLogoFiles = glob("$sImagesDir*".$dataSplit[0]."*");
                        if($aLogoFiles)
                        {
                            echo "$sDateStamp [$sUser]: Logo file with ID: ".$dataSplit[0]." is found in: ".$aLogoFiles[0]."\n";
                            $sLogoFullPath = $sImagesDirMachineLevel. basename($aLogoFiles[0]);
                            $sLogo = $dataSplit[0];
                        }
                        else 
                        {
                            $sError = "$sDateStamp [$sUser]: ERROR: Logo file with ID: ".$dataSplit[0]." is not found in: ".$sImagesDir.". Please check directory and make sure that logo named as logo ID is in directory.\n";
                            $aErrors[] = $sError;
                            echo $sError;
                            $sLogoFullPath = "LOGO NOT FOUND, CHECK DIRECTORY";
                            $sLogo = $dataSplit[0];
                        }

            
                        
                }
                if((empty($dataSplit[1])) && (isset($aData[1])))
                {
                        $sQRCode = "";   
                }
                else
                {
                        $sQRCode = $dataSplit[1];
                }
                if((empty($dataSplit[2])) && (isset($aData[2])))
                {
                        $sCompanyName = "";   
                }
                else
                {
                        $sCompanyName = trim(str_replace("&","&amp;",str_replace([";","?"],"",$dataSplit[2])));
                }
                
            }
        }

        if($sBIN == "52403218")//Power
        {
            $bIsDelimiterPresent = preg_match("/|/",$sCompanyName);
            if(empty($sCompanyName))
            {
                $sLogo = "";
                $sQRCode = "";
                $sCompanyName = "";
            }
            else if($bIsDelimiterPresent)
            {
                $dataSplit = explode("|",$sCompanyName);
                if((empty($dataSplit[0])) && (isset($aData[0])))
                {
                        $sAdditionalEmbossField = "";   
                }
                else
                {
                        $sAdditionalEmbossField = $dataSplit[0];      
                }
                if((empty($dataSplit[1])) && (isset($aData[1])))
                {
                        $sQRCode = "";   
                }
                else
                {
                        $sQRCode = $dataSplit[1];
                }
                if((empty($dataSplit[2])) && (isset($aData[2])))
                {
                        $sCompanyName = "";   
                }
                else
                {
                        $sCompanyName = trim(str_replace("&","&amp;",str_replace([";","?"],"",$dataSplit[2])));
                }
                
            }
        }

            if($sBIN == "525355" || $sBIN == "521747" || $sBIN == "516529"|| $sBIN == "406000")//Revolut
            {
                if(strstr($sProcessedFilename, '_IMG_'))
                {
                $sLogo = trim(substr($sTrack2,25,9));
                //CHECK if logo file exists
                $aLogoFiles = glob("$sImagesDirRevolut*".$sLogo."*");
                        if($aLogoFiles)
                        { 
                            echo "$sDateStamp [$sUser]: Logo file with ID: ".$sLogo." is found in: ".$aLogoFiles[0]."\n";
                            $sLogoFullPath = $sImagesDirMachineLevelRevolut. basename($aLogoFiles[0]);
                            $sLogo = basename($aLogoFiles[0]);
                            
                        }
                        else 
                        {
                            $sError = "$sDateStamp [$sUser]: ERROR: Logo file with ID: ".$sLogo." is not found in: ".$sImagesDirRevolut.". Please check directory and make sure that logo named as logo ID is in directory.\n";
                            $aErrors[] = $sError;
                            echo $sError;
                        
                            $sLogoFullPath = "LOGO NOT FOUND, CHECK DIRECTORY";
                            $sLogo = $sLogo;
                        }
                }
            }

            if($sBIN == "544292")//FLEXIA
            {
                    $sPRN =  (empty($aData[4])) ? "": trim(str_replace("&","&amp;",str_replace([";","?"],"",$aData[4])));
                    if($aData[6]=="CARDHOLDER NAME")
                    {
                        $sEMBName = "";
                        $sName1= "";
                        $sName2= "";
                        $sName3= "";
                        $sName4= "";
                        $sName5= "";
                        $sName6= "";
                        $sEMBNameLine1 = "";
                        $sEMBNameLine2 = "";
                    }
                    $sQRCode = $sLastName;
            }

            $sCVV2 = trim($aData[5]);
            $sChipData = base64_encode($aData[7]);
            $sPrePersoChipData = base64_encode("<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <START-MSG>
            <PLUGIN-MSG sequenceNumber=\"1\" id=\"net.tagsystems.chipperso.plugin.prepGP\">
                <DATA>
                <!-- ATR TOPAZ-->
                <ATR>3B7F9600003101F1564011001900000000000000</ATR>
                <DBAUDIT>NO</DBAUDIT>
                <KEYS>
                    <KEY name=\"ISD\" type=\"0\" kcv=\"".$sISDKCV."\">".$sISD."</KEY>
                    <KEY name=\"ENC\" type=\"0\" kcv=\"C6A32E\">HSMARLIB.KEY.ST.ISD.CP</KEY>
                    <KEY name=\"MAC\" type=\"0\" kcv=\"C6A32E\">HSMARLIB.KEY.ST.ISD.CP</KEY>
                    <KEY name=\"DEK\" type=\"0\" kcv=\"C6A32E\">HSMARLIB.KEY.ST.ISD.CP</KEY>
                    <KEY derivation=\"YES\"></KEY>
                    <KEY name=\"KMC\" type=\"0\" kcv=\"".$sKMCKCV."\">".$sKMC."</KEY>
                </KEYS>
                <!-- Configuración para la grabación del CPLC en tarjetas como IVORY. Sólo incluir en aquellas tarjetas que lo indique ST porque no graben correctamente la información de preperso con el 9F67 -->
                <!-- TAG con el que grabar los datos de preperso -->
                <CPLC_CMD>9F6B</CPLC_CMD>
                <!-- Datos de relleno por delante de la información de preperso -->
                <CPLC_PREDATA>0000000000000000</CPLC_PREDATA>      
                        <DGIs>
                            <DGI id=\"8FFC\" description=\"RF protocol and UID configuration\" comment=\"DISABLE Contactless interface\">00</DGI>
                        </DGIs>
                </DATA>
            </PLUGIN-MSG>
            </START-MSG>");
        
        
            
        
            $sXMLBody .=
            "          
            <Unit Name=\"Card_".++$iCounter."_".$sFirstName."_".$sLastName."_".substr($aData[3],13,4)."\" Type=\"Card\" Priority=\"1\">    
                        <UnitMatching Encoding=\"ASCII\" InputFormat=\"Text\">".$sTrack1."</UnitMatching>
                        <DataFields>
                            <DataField Name=\"Sequence\">
                                <Value InputFormat=\"Text\">$iCounter</Value>
                            </DataField>
                            <DataField Name=\"Track1\">
                                <Value InputFormat=\"Text\">".$sTrack1."</Value>
                            </DataField>
                            <DataField Name=\"Track2\">
                                <Value InputFormat=\"Text\">".$sTrack2."</Value>
                            </DataField>
                            <DataField Name=\"Track3\">
                                <Value InputFormat=\"Text\">".$sTrack3."</Value>
                            </DataField>
                            <DataField Name=\"EMB1\">
                                <Value InputFormat=\"Text\">".$sPAN."</Value>
                            </DataField>
                            <DataField Name=\"PAN1\">
                                <Value InputFormat=\"Text\">".$sPAN1_2."</Value>
                            </DataField> 
                            <DataField Name=\"PAN2\">
                                <Value InputFormat=\"Text\">".$sPAN2_2."</Value>
                            </DataField>  
                            <DataField Name=\"PAN1_4\">
                                <Value InputFormat=\"Text\">".$sPAN1_4."</Value>
                            </DataField>  
                            <DataField Name=\"PAN2_4\">
                                <Value InputFormat=\"Text\">".$sPAN2_4."</Value>
                            </DataField>  
                            <DataField Name=\"PAN3_4\">
                                <Value InputFormat=\"Text\">".$sPAN3_4."</Value>
                            </DataField>  
                            <DataField Name=\"PAN4_4\">
                                <Value InputFormat=\"Text\">".$sPAN4_4."</Value>
                            </DataField>     
                            <DataField Name=\"EMB2\">
                                <Value InputFormat=\"Text\">".$sExpDate."</Value>
                            </DataField>
                            <DataField Name=\"EMB3\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sEMBName]]>"."</Value>
                            </DataField>
                            <DataField Name=\"EMBNameLine1\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sEMBNameLine1]]>"."</Value>
                            </DataField>
                            <DataField Name=\"EMBNameLine2\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sEMBNameLine2]]>"."</Value>
                            </DataField>
                            <DataField Name=\"EMBNameLine3\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sEMBNameLine3]]>"."</Value>
                            </DataField>
                            <DataField Name=\"EMBNameLine4\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sEMBNameLine4]]>"."</Value>
                            </DataField>
                            <DataField Name=\"FNAME_T1\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sFirstName]]>"."</Value>
                            </DataField>
                            <DataField Name=\"LNAME_T1\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sLastName]]>"."</Value>
                            </DataField>
                            <DataField Name=\"FNAME_LNAME_T1\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sFullName]]>"."</Value>
                            </DataField>
                            <DataField Name=\"NAME1\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sName1]]>"."</Value>
                            </DataField>
                            <DataField Name=\"NAME2\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sName2]]>"."</Value>
                            </DataField>
                            <DataField Name=\"NAME3\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sName3]]>"."</Value>
                            </DataField>
                            <DataField Name=\"NAME4\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sName4]]>"."</Value>
                            </DataField>
                            <DataField Name=\"NAME5\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sName5]]>"."</Value>
                            </DataField>
                            <DataField Name=\"NAME6\">
                            <Value InputFormat=\"Text\">"."<![CDATA[$sName6]]>"."</Value>
                        </DataField>
                            <DataField Name=\"EMB4\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sCompanyName]]>"."</Value>
                            </DataField>
                            <DataField Name=\"REV_EMB3\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sREV_EMB3]]>"."</Value>
                            </DataField>
                            <DataField Name=\"REV_EMB4_Line1\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sREV_EMB4_Line1]]>"."</Value>
                            </DataField>
                            <DataField Name=\"REV_EMB4_Line2\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sREV_EMB4_Line2]]>"."</Value>
                            </DataField>
                            <DataField Name=\"REV_EMB3_LIMIT_HOR\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sREV_EMB3_LIMIT_HOR]]>"."</Value>
                            </DataField>
                            <DataField Name=\"REV_EMB4_LIMIT_HOR\">
                                    <Value InputFormat=\"Text\">"."<![CDATA[$sREV_EMB4_LIMIT_HOR]]>"."</Value>
                            </DataField>
                        
                            <DataField Name=\"EMB5\">
                                <Value InputFormat=\"Text\">".$sCVV2."</Value>
                            </DataField>
                            <DataField Name=\"EMB6\">
                                <Value InputFormat=\"Text\">"."<![CDATA[$sAdditionalEmbossField]]>"."</Value>
                            </DataField>
                            <DataField Name=\"QRCode\">
                                <Value InputFormat=\"Text\">$sQRCode</Value>
                            </DataField>
                            <DataField Name=\"LogoID\">
                                <Value InputFormat=\"Text\">$sLogo</Value>
                            </DataField>
                            <DataField Name=\"LogoFullPath\">
                                <Value InputFormat=\"Text\">$sLogoFullPath</Value>
                            </DataField>
                            <DataField Name=\"CID\">
                                <Value InputFormat=\"Text\">$sCID</Value>
                            </DataField>
                            <DataField Name=\"PRN\">
                                <Value InputFormat=\"Text\">$sPRN</Value>
                            </DataField>
                            <DataField Name=\"CardType\">
                                <Value InputFormat=\"Text\">ISO14443A</Value>
                        </DataField>
                            <DataField Name=\"ChipData\">
                                <Value InputFormat=\"Text\">".$sChipData."</Value>
                            </DataField>
                            <DataField Name=\"PrePersoChipData\">
                                <Value InputFormat=\"Text\">".$sPrepersoChipData."</Value>
                            </DataField>
                        </DataFields>
                        </Unit>";       
        }

        //echo($sPrePersoChipData);
        $sXMLOutput = $sXMLHeader.$sXMLBody.$sXMLFooter;
    // $sOutputFile = $outputDir.$sCustomerName."_".$sFileName;
        $sOutputFile = $outputDir.$sCustomerName."_".$sFileName;

        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = FALSE;
        $dom->formatOutput = true;
        $dom->loadXML($sXMLOutput); 
        
        
        
        
        echo "$sDateStamp [$sUser]: Writing Machine Readable File to $sOutputFile \n";
        if($dom->save($sOutputFile))
        {
            echo "$sDateStamp [$sUser]: File succesfully written to: $sOutputFile \n";

            $inputDir; 
            $bFileMoved = rename($inputDir, $processedDir.$sProcessedFilename);
            if($bFileMoved)
            {
                echo "$sDateStamp [$sUser]: Processed File succesfully moved to: $processedDir$sProcessedFilename \n";
                echo "$sDateStamp [$sUser]: Total Number of Records: $iCounter in file: $sFileName \n\n"; 

            }
            else 
            {
                echo "$sDateStamp [$sUser]: Processed File failed to be moved to: $ $processedDir$sProcessedFilename \n";
            }

        }
        else
        {
            echo "$sDateStamp [$sUser]: Writing file failed\n";
        }
}

function splitCHN($fullName, $track1FNAME, $track2LNAME){

    $iMaxChars = MAX_CHARS_VERTICAL_CARD;
    $sPrintLine1= "";
    $sPrintLine2= "";
    $aPrint = [];
   
    $aNameComps =explode(" ", trim($fullName));
    $aFNAMEComps = explode(" ", trim($track1FNAME));
    $aLNAMEComps = explode(" ", trim($track2LNAME)); 
  

    $iFNAMELen = strlen(trim($track1FNAME));
    $iLNAMELen = strlen(trim($track2LNAME));

    $iNoNameComps = count($aNameComps);
    $iNoFNameComps = count($aFNAMEComps);

    
    $iCounter = $iNoFNameComps;

    $isFNAMELonger = ($iFNAMELen>$iMaxChars);
    
    if($iNoNameComps == 2)
    {
        if(strlen($aNameComps[0])>$iMaxChars)
            {
                $aPrint[] = mb_substr($aNameComps[0],0,1,"UTF-8");
            }
        else
            {
                $aPrint[] = $aNameComps[0];
            }
        
        if(strlen($aNameComps[1])>$iMaxChars)
        {
            $aPrint[] = mb_substr($aNameComps[1],0,12,"UTF-8");
        }
        else
        {
            $aPrint[] = $aNameComps[1];
        }
    }
    else if($iNoNameComps>2)
    {
        $sPrintLine1 = $track1FNAME;
        while($isFNAMELonger)
        { 
            $aFNAMEComps[$iCounter-1] = mb_substr($aFNAMEComps[$iCounter-1],0,1,"UTF-8");
            $sPrintLine1 = implode(" ",$aFNAMEComps);
            if(strlen($sPrintLine1)>$iMaxChars)
            {
                $iCounter--;
                if($iCounter==0)
                {
                    $sPrintLine1 = mb_substr($sPrintLine1,0,12,"UTF-8");
                    $isFNAMELonger = false;
                }
            }
            else
            {
               //$sPrintLine1 = implode(" ",$aFNAMEComps);
               $isFNAMELonger = false;
            }
        
        }

        if($iLNAMELen > $iMaxChars)
            $sPrintLine2 = mb_substr($track2LNAME,0,12,"UTF-8");
        else 
            $sPrintLine2 = $track2LNAME;

        $aPrint[] = $sPrintLine1;
        $aPrint[] = $sPrintLine2;
    }
    else if($iNoNameComps < 2)
    {
        
        
        if(strlen($aNameComps[0])>$iMaxChars)
        {
            $aPrint[] = mb_substr($aNameComps[0],0,12,"UTF-8");
            $aPrint[] = "";
        }
        else
        {
            $aPrint[] = $aNameComps[0];
            $aPrint[] = "";
        }
    }
    
    return $aPrint;
  

}

function shortenCHNwithDot($fullName){

    $iMaxChars = MAX_CHARS_HORIZONTAL_CARD;
    $sShortenedEmbName = $fullName;
    $aNameComps =explode(" ", trim($fullName));
    $iNoNameComps = count($aNameComps);
    $iCounter = 0;

    
    while(strlen($sShortenedEmbName)>$iMaxChars)
    {
        if($iCounter < $iNoNameComps)
        {
            $aNameComps[$iCounter] = mb_substr($aNameComps[$iCounter],0,1,"UTF-8").".";

            $sShortenedEmbName = implode(" ",$aNameComps);
            $iCounter++;
        }
        else
        {
            $sShortenedEmbName = mb_substr($sShortenedEmbName,0,$iMaxChars,"UTF-8");
        }
        
    }
    return $sShortenedEmbName;
}

function shortenCHN($fullName){

    $iMaxChars = MAX_CHARS_HORIZONTAL_CARD;
    $sShortenedEmbName = $fullName;
    $aNameComps =explode(" ", trim($fullName));
    $iNoNameComps = count($aNameComps);
    $iCounter = 0;


    while(strlen($sShortenedEmbName)>$iMaxChars)
    {
        if($iCounter < $iNoNameComps)
        {
            $aNameComps[$iCounter] = mb_substr($aNameComps[$iCounter],0,1,"UTF-8");
            $sShortenedEmbName = implode(" ",$aNameComps);
            $iCounter++;
        }
        else
        {
            $sShortenedEmbName = mb_substr($sShortenedEmbName,0,$iMaxChars,"UTF-8");
        }
        
    }
    return $sShortenedEmbName;
}




function getProductsList($sProductConfigFile){
    $productsConfiguration = file($sProductConfigFile, FILE_SKIP_EMPTY_LINES);
    //print_r($productsConfiguration);
    foreach($productsConfiguration as $aProductDetails)
        {
            if(preg_match("/^#/",$aProductDetails, $comments))
                {
                    //COMMENT IN COFIGURATION FILE, GO TO NEXT
                    continue;
                }
                else
                {
                    $aProducts = str_getcsv($aProductDetails);
                
                    if(empty($aProducts[6]))
                    {
                        continue;
                    }
                    //	echo("PRODUCTS\n");
                    //	print_r($aProducts);
                    if(empty($aProducts[4]))
                    {
                        $aProducts[4] = "STD";
                    }
                    
                    $aBINs[$aProducts[1]]['Customer']=$aProducts[0];
                    $aBINs[$aProducts[1]]['Profile']=$aProducts[6];
                    $aBINs[$aProducts[1]]['TagProduct'][$aProducts[4]]= $aProducts[6];
                    //$aBINs[$aProducts[1]][$aProducts[4]]=$aProducts[6];
                    
                    
                
                }
        }   
      
        return $aBINs;
}

?> 
