#!/usr/bin/php
<?php 

/******************************
Author: Jean-Eric Pierre-Louis
Company: Pierre & Rady LLC
Date: 01/19/2022
Revision: 08/31/2022
Name: Radovan Jakus
Version: 3.1
Notes: Bulk Shipment, Mercury
******************************/
$sWorkdir = __DIR__ . '/';
require __dir__ . '/lib/globals.php';
require __dir__ . '/lib/config.php';
require __dir__ . '/lib/country_codes.php';
require __dir__ . '/lib/getproductlist.php';
require __dir__ . '/lib/inputs.php';
require __dir__ . '/lib/serialnumber.php';
require __dir__ . '/lib/progressbar.php';
require __dir__ . '/lib/getdetailoverview.php';
require __dir__ . '/lib/writers.php';
require __dir__ . '/lib/parser.php';
require __dir__ . '/lib/maskedpandata.php';
require __dir__ . '/lib/generateoutputdata.php';
require __dir__ . '/lib/mapdata.php';
require __dir__ . '/lib/initdata.php';


//LINQ NOT WORKING ONE
require __DIR__.'/lib/php-linq/linq/Linq.php';
require __DIR__.'/lib/php-linq/linq/LinqFactory.php';
require __DIR__.'/lib/php-linq/linq/factory/JoinFactory.php';
require __DIR__.'/lib/php-linq/linq/helper/IJoinHelper.php';
require __DIR__.'/lib/php-linq/linq/helper/JoinHelper.php';
require __DIR__.'/lib/php-linq/linq/helper/LeftJoinHelper.php';


date_default_timezone_set ("America/New_York");

echo "$sDateStamp [$sUser]: Starting Script \n";

$aConfigList = getProductsList(__DIR__.'/configurations/UniversalParser_SystemConfig.csv');
$startTime = hrtime(true);
echo "$sDateStamp [$sUser]: Using option to process files from predefined directories automatically. Directory configuration: UniversalParser_SystemConfig.csv\n";
//Loop solely for logging purpose            
                foreach($aConfigList as $aConfig)
                {
                        $sProcessor = $aConfig['Processor'];
                        $sInputDir = $aConfig['InputDirectory'];
                        $sInputFileSuffix =  $aConfig['InputFileSuffix'];
                        $aInputFiles = glob($sInputDir."*".$sInputFileSuffix, GLOB_NOSORT);
                    
                    if($aInputFiles){
                            echo "Processor: ".$sProcessor." \n";
                            echo "\t".($sInputDir)." \n";
                            foreach($aInputFiles as $sInputFilePath){
                            echo "\t\t".basename($sInputFilePath)." \n";
                            }
                        }
                }
                //Loop to process the data
                foreach($aConfigList as $aConfig)
                {
                        $sProcessor = trim($aConfig['Processor']);
                        $sInputDir = $aConfig['InputDirectory'];
                        $sShippingCodeConfiguration = $aConfig['ShippingCodeConfiguration'];
                        $sProcessedDir = $aConfig['ProcessFileDirectory'];
                        $sDataMapConfiguration = $aConfig['DataMapConfiguration'];
                        $sOutputFileConfigurationDirectory = $aConfig['OutputConfigurationDirectory'];
                        $aOutputFileConfiguration  = explode('|',$aConfig['OutputFilesToCreate']);
                        $sCompositeFieldReference1Dir = $aConfig['CompositeField'];
                        $sInputFileSuffix =  $aConfig['InputFileSuffix'];
                        $aInputFiles = glob($sInputDir."*".$sInputFileSuffix, GLOB_NOSORT);

                    if($aInputFiles)
                    {
                            $file = 0;
                            foreach($aInputFiles as $sInputFilePath){
                                echo "\n\n$sDateStamp [$sUser]: START PROCESSING FILE: $sInputFilePath \n\n";
                                progressBar(++$file,count($aInputFiles));
                                $startTime = hrtime(true);
                                require_once __dir__ . '/lib/customers/'.strtolower($sProcessor).'.php';

                                $ParsedData = Parser($sProcessor,$sInputFilePath, $sDataMapConfiguration);
                                
                                $sFunctionName =  'datamap_validation_'.strtolower($sProcessor);
                                $aMappedData = call_user_func($sFunctionName,$ParsedData,$sInputFilePath,$aConfig);
                                foreach($aOutputFileConfiguration as $sConfiguration)
                                {
                                     GenerateOutputData($aMappedData, $sOutputFileConfigurationDirectory,$sConfiguration);
                                }
                 
                                if(!empty($sSupplementalFileName))
                                {
                                    $sFileName = basename($sSupplementalFileName);
                                    $bFileMoved = rename($sSupplementalFileName , $sProcessedDir.$sFileName);
                                    if($bFileMoved)
                                        {
                                            echo "$sDateStamp [$sUser]: Processed Suplemental File succesfully moved to: $sProcessedDir$sFileName \n";
                                        }
                                        else 
                                        {
                                            echo "$sDateStamp [$sUser]: Processed Suplemental File failed to be moved to: $sProcessedDir$sFileName  \n";
                                        }
                                        $sSupplementalFileName = "";
                                }
                                $sFileName = basename($sInputFilePath);
                                $bFileMoved = rename($sInputFilePath , $sProcessedDir.$sFileName);
                               

                                if($bFileMoved)
                                  {
                                      echo "\n$sDateStamp [$sUser]: Processed File succesfully moved to: $sProcessedDir$sFileName \n";
                                      echo "$sDateStamp [$sUser]: END PROCESSING FILE: $sInputFilePath\n";

                                      $endTime = hrtime(true);            
                                      $executionTime = (($endTime-$startTime)/1e+6)/1000;        
                                      echo "$sDateStamp [$sUser]: Execution time per file: $executionTime sec \n";
                                  }
                                  else 
                                  {
                                      echo "$sDateStamp [$sUser]: Processed File failed to be moved to: $sProcessedDir$sFileName \n";
                                  }
                                  $sFileName = "";
                         }
                    }
                    else 
                    {
                        echo "$sDateStamp [$sUser]: There are no files to be processed in directory. The directory does not contain customer files. Directory: ".$sInputDir."\n";
                    }
                }





//////START 12 WRITING REPORT DATA TO FILE //
if(isset($aErrors))
{
    if($aErrors!=null)
    {
        echo "\n$sDateStamp [$sUser]: ERRORS during processing, to take action is needed: \n";
        foreach($aErrors as $sErrorMessage)
        {
            echo  $sErrorMessage."\n";
        }
    }
}
//////END 12 WRITING REPORT DATA TO FILE //
echo "\n$sDateStamp [$sUser]: Ending Script";


?> 
