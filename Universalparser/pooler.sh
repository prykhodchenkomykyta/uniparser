#!/bin/bash
###############################
# Author: Michal Srogoncik 
# Company: Pierre & Rady LLC
# Date: 09/23/2022
# Revision: 09/23/2022
# Name: Michal Srogoncik
# Version: 1.0
# Notes: Pooler
###############################
## parameters:
# customer
# file
# extensionr/t
set -x
set -e
INPUTDIR="/home/erutberg/Radovan/DataPrep/IN/"
for processor in `ls $INPUTDIR`; do 
  for inputfile in `ls $INPUTDIR$processor`; do
    run-one ./P_PrivacyToCSV.php  $INPUTDIR$processor/$inputfile $processor;
  done
done;
