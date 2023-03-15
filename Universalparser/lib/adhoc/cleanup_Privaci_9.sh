#!/bin/bash

#mv /home/miso/Documents/repos/Universalparser/in/Privacy/xxx/xxxTestCanadaPrivacy9.csv /home/miso/Documents/repos/Universalparser/in/Privacy/xxxTestCanadaPrivacy9.csv
#cp /home/miso/Documents/repos/Universalparser/in/Privacy/xxx/XXXXX.csv /home/miso/Documents/repos/Universalparser/pooled-input/Privacy/
#cp $HOME/Documents/repos/Universalparser/Privacy_test.csv $HOME/Documents/repos/Universalparser/in/lithic/


srm /var/TSSS/DataPrep/in/*
srm /var/TSSS/Files/MAILMERGE/*
srm /var/TSSS/Files/Reports/*.csv
srm /var/TSSS/Files/Reports/galileo/waiting/*.csv
srm /var/TSSS/Files/USPS/*.csv
srm /var/TSSS/Files/FEDEX/*
srm /var/TSSS/Files/USPS/BULK/*.csv
srm /var/TSSS/Files/FEDEX/BULK/*
srm /var/TSSS/Files/*
srm /var/TSSS/Files/TAGPL/*

#mv /home/erutberg/Radovan/DataPrep/processed/highnote/* /home/erutberg/Radovan/DataPrep/IN/highnote/
#mv /home/erutberg/Radovan/DataPrep/processed/galileo/* /home/erutberg/Radovan/DataPrep/IN/galileo/
mv /home/erutberg/Radovan/DataPrep/processed/privacy/* /home/erutberg/Radovan/DataPrep/IN/privacy/
mv /home/erutberg/Radovan/DataPrep/processed/qrails/* /home/erutberg/Radovan/DataPrep/IN/qrails/
#mv /home/erutberg/Radovan/DataPrep/processed/gps/* /home/erutberg/Radovan/DataPrep/IN/gps/