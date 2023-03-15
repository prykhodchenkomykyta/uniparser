<?php 
/******************************
Author: Radovan Jakus
Company: Pierre & Rady LLC
Date: 09/05/2022
Revision: 
Name: 
Version: 1
Notes: BANRESERVAS
******************************/


/*Production Environment*/
$sInputDir = "/home/erutberg/Radovan/DataPrep/IN/banreservas/";
$sOutputDir = "/var/TSSS/Files/";
$sProcessedDir = "/home/erutberg/Radovan/DataPrep/processed/banreservas/";

// $sInputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Banreservas\\in\\";
// $sOutputDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Banreservas\\out\\";
// $sProcessedDir = "D:\\Workspace\\TagSystem\\Parser_Plugin\\Banreservas\\in\\";   



define("MAX_CHARS_VERTICAL_CARD",12);
define("MAX_CHARS_HORIZONTAL_CARD",26);


$sCustomerName;
$sProductName;
$sBIN;
$aBINs['420462']=array("Customer"=>"BANRESERVAS",
                       "Profile"=>"Banreservas_Mag_Only");


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
                                                  


ob_start();
header('Content-Type: application/json');
date_default_timezone_set ("America/New_York");
$sDateStamp = date('Y-m-d H:i:s', time());
$sUser = get_current_user();

echo "$sDateStamp [$sUser]: Starting Script \n";

$aOptions = getopt("p::n::");
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
else{
 echo "$sDateStamp [$sUser]: Using option to process files from predefined directory automatically. Directory: $sInputDir \n";
 $aInputFiles = glob("$sInputDir*.txt");
    if($aInputFiles){
        echo "$sDateStamp [$sUser]: List of files to be processed: \n";
        foreach($aInputFiles as $sInputFilePath){
                echo "\t".basename($sInputFilePath)." \n";
        }
        foreach($aInputFiles as $sInputFilePath){
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
    $sFileName = str_replace("MS_DATAPREP", "MRF", basename($inputDir, "txt"))."xml";
    $sJobName = preg_replace('/^(\w+)?DATAPREP_\d+_/', "", basename($inputDir, "txt"));

    $sJobName = str_replace(".","",$sJobName);
    $aInputFile = file($inputDir, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $aCSVCol = explode("|", $aInputFile[0]);
    //$sBatchID = str_replace("/","-",$aCSVCol[0]);
    $sBIN = substr($aCSVCol[4],0,6);
    $sBINExtended = substr($aCSVCol[4],0,8);



    $sCustomerName = $aBINs[$sBIN]['Customer'];
    $sProductName = $aBINs[$sBIN]['Profile'];
    
    $iNoErrorRecs = 0;
    $iNoOfRecordsFromFile = count($aInputFile);

   print_r($aInputFile);



    echo "$sDateStamp [$sUser]: Customer: $sCustomerName \n";
    echo "$sDateStamp [$sUser]: ProductName: $sProductName \n";
    echo "$sDateStamp [$sUser]: Number of Records: $iNoOfRecordsFromFile \n";
    
    
    //$sBOM = 0xEF 0xBB 0xBF;
    $sXMLHeader = "
    <InputData>
        <Units>
            <Unit Name=\"".$sCustomerName."_".$sJobName."\" Type=\"Job\" Priority=\"1\">
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
        $aData = str_getcsv($aLine, "|", '"');
        //$aData = explode(",", $aLine);

        //INITIALIZE VALUES
        $Token = "";
        $sLastName = "";
        $sFirstName = "";
        $sFullName = "";
        $sTrack1 = "";
        $sTrack2 = "";
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
      


        $sToken = strtok($aData[6], "\^");
        $sToken = strtok("\^");

        $sLastName=trim(strtok($sToken, "\/"));
        $sFirstName=trim(strtok("\/"));
        $sFullName = "$sFirstName $sLastName";

        $sTrack1 = trim(substr($aData[6],1,strlen($aData[6])-2));
        $sTrack2 = trim(substr($aData[7],1,strlen($aData[7])-2));
        $sPAN = trim($aData[5]);
        $sPAN1_2 = trim(substr($aData[4],0,4)." ".substr($aData[4],4,4));
        $sPAN2_2 = trim(substr($aData[4],8,4)." ".substr($aData[4],12,4));
        $sPAN1_4 = trim(substr($aData[4],0,4));
        $sPAN2_4 = trim(substr($aData[4],4,4));
        $sPAN3_4 = trim(substr($aData[4],8,4));
        $sPAN4_4 = trim(substr($aData[4],12,4));
        $sExpDate = trim(substr($aData[11],0,2)."/".substr($aData[11],-2));
        $sEMBName = trim(str_replace(array('"',"&","<",">","'",),array("&quot;","&amp;","&lt;","&gt;","&apos;"),$aData[2]));

        // $sEMBName = trim($aData[6]);
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
        $sCompanyName = (empty($aData[3])) ? '': trim($aData[3]);
        $sCompanyName = "<![CDATA[$sCompanyName]]>";


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
            $sREV_EMB4_Line1 = $sEMBName;
            $sREV_EMB4_Line2 = "";
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
                    $sREV_EMB4_Line1 = $aCompanyNameSplit[1];
                    $sREV_EMB4_Line2 = "";
                    $sREV_EMB4_LIMIT_HOR = $sCompanyName;
                }
               
            }
            else
            {
                $sREV_EMB4_Line1 = trim($sCompanyName);
            }
       }



        $sCVV2 = trim($aData[14]);
        $sChipData = "";
     
        
       
        $sXMLBody .=
        "          
        <Unit Name=\"Card_".++$iCounter."_".$sFirstName."_".$sLastName."_".substr($aData[4],13,4)."\" Type=\"Card\" Priority=\"1\">    
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
                            <Value InputFormat=\"Text\">".$sEMBName."</Value>
                        </DataField>
                        <DataField Name=\"EMBNameLine1\">
                            <Value InputFormat=\"Text\">".$sEMBNameLine1."</Value>
                        </DataField>
                        <DataField Name=\"EMBNameLine2\">
                            <Value InputFormat=\"Text\">".$sEMBNameLine2."</Value>
                        </DataField>
                        <DataField Name=\"EMBNameLine3\">
                             <Value InputFormat=\"Text\">".$sEMBNameLine3."</Value>
                        </DataField>
                        <DataField Name=\"EMBNameLine4\">
                             <Value InputFormat=\"Text\">".$sEMBNameLine4."</Value>
                        </DataField>
                        <DataField Name=\"FNAME_T1\">
                            <Value InputFormat=\"Text\">".$sFirstName."</Value>
                        </DataField>
                        <DataField Name=\"LNAME_T1\">
                            <Value InputFormat=\"Text\">".$sLastName."</Value>
                        </DataField>
                        <DataField Name=\"FNAME_LNAME_T1\">
                            <Value InputFormat=\"Text\">".$sFullName."</Value>
                         </DataField>
                        <DataField Name=\"NAME1\">
                            <Value InputFormat=\"Text\">".$sName1."</Value>
                        </DataField>
                        <DataField Name=\"NAME2\">
                            <Value InputFormat=\"Text\">".$sName2."</Value>
                        </DataField>
                        <DataField Name=\"NAME3\">
                            <Value InputFormat=\"Text\">".$sName3."</Value>
                        </DataField>
                        <DataField Name=\"NAME4\">
                            <Value InputFormat=\"Text\">".$sName4."</Value>
                        </DataField>
                        <DataField Name=\"NAME5\">
                            <Value InputFormat=\"Text\">".$sName5."</Value>
                        </DataField>
                        <DataField Name=\"NAME6\">
                        <Value InputFormat=\"Text\">".$sName6."</Value>
                    </DataField>
                        <DataField Name=\"EMB4\">
                            <Value InputFormat=\"Text\">$sCompanyName</Value>
                        </DataField>
                        <DataField Name=\"REV_EMB3\">
                             <Value InputFormat=\"Text\">$sREV_EMB3</Value>
                        </DataField>
                        <DataField Name=\"REV_EMB4_Line1\">
                             <Value InputFormat=\"Text\">$sREV_EMB4_Line1</Value>
                        </DataField>
                        <DataField Name=\"REV_EMB4_Line2\">
                             <Value InputFormat=\"Text\">$sREV_EMB4_Line2</Value>
                        </DataField>
                        <DataField Name=\"REV_EMB3_LIMIT_HOR\">
                            <Value InputFormat=\"Text\">$sREV_EMB3_LIMIT_HOR</Value>
                        </DataField>
                        <DataField Name=\"REV_EMB4_LIMIT_HOR\">
                                <Value InputFormat=\"Text\">$sREV_EMB4_LIMIT_HOR</Value>
                        </DataField>
                       
                        <DataField Name=\"EMB5\">
                            <Value InputFormat=\"Text\">".$sCVV2."</Value>
                        </DataField>
                        <DataField Name=\"EMB6\">
                            <Value InputFormat=\"Text\"></Value>
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
    $sOutputFile = $outputDir.$sCustomerName."_MRF_".$sFileName;

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







        echo "$sDateStamp [$sUser]: Ending Script";
?> 
