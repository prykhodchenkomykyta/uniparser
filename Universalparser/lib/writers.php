<?php
//////START 11 WRITING REPORT DATA TO FILE //

//@Data -> outdata array
//@File -> original filename
//@FileHeader -> header of CSV file
function writeReport($Data, $File ,$Header, $OutDir, $FileType, $FileSuffix)
{
    $sDateStamp = date('Y-m-d H:i:s', time());
    $sUser = get_current_user(); 
    global $sTmpDir;

    $sFileName = $File;
    echo "$sDateStamp [$sUser]: \n\n $FileType START \n\n";
    $sReportOutputFile = $OutDir.(preg_replace("/(\.).*/","",$sFileName))."$FileSuffix";
    $sTmpFile = $sTmpDir.(preg_replace("/(\.).*/","",$sFileName))."$FileSuffix";

    $fp = fopen($sTmpFile,"w");
    fputcsv($fp, $Header);
    //fwrite($fp,$Header).fwrite($fp, "\r\n");

    foreach($Data as $row)
    {
        //$bFileWriting1 =fwrite($fp, implode(",",$row)).fwrite($fp, "\r\n");
        $bFileWriting1 =fputcsv($fp, $row);
        $aFilesWritingStatus[] = $bFileWriting1;
    }
        if($bFileWriting1)
        {
            fclose($fp);

            $bFileMoved = rename($sTmpFile ,$sReportOutputFile);
            if($bFileMoved)
                echo "$sDateStamp [$sUser]: Report File for file $sFileName succesfully written as: $sReportOutputFile\n";


        }
        else 
        {
            echo "$sDateStamp [$sUser]: Writing Report file for file $sFileName failed\n";
            fclose($fp);
        }

}



function writeInternationalRecords($Data, $File)
{
    $sDateStamp = date('Y-m-d H:i:s', time());
    $sUser = get_current_user(); 
    global $sIntShipmentDir;
    global $sTmpDir;

    $sFileName = $File;
    echo "$sDateStamp [$sUser]: \n\n INTERNATIONAL SHIPMENT RECORDS \n\n";
    $sInternationalOutputFile = $sIntShipmentDir.(preg_replace("/(\.).*/","",$sFileName)).".TAGPL.csv";
    $sTmpFile = $sTmpDir.(preg_replace("/(\.).*/","",$sFileName)).".TAGPL.csv";
    $fp = fopen($sTmpFile,"w");
    foreach($Data as $row)
    {
    
        // $bFileWriting1 =fwrite($fp, implode(",",$row).PHP_EOL);
        $bFileWriting1 =fputcsv($fp, $row);
        $aFilesWritingStatus[] = $bFileWriting1;
    }
        if($bFileWriting1)
        {
            fclose($fp);
            $bFileMoved = rename($sTmpFile ,$sInternationalOutputFile);
            if($bFileMoved)
            echo "$sDateStamp [$sUser]: International File for file $sFileName succesfully written as: $sInternationalOutputFile\n";
            

        }
        else 
        {
            echo "$sDateStamp [$sUser]: Writing International file $sFileName failed\n";
            fclose($fp);
        
        }
}

//////END 11 WRITING REPORT DATA TO FILE //

?>