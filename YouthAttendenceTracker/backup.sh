#!/bin/bash

mysqldump --user=root --password=paris1257 --host=localhost --databases attendencetracker > /Users/pastorjason/OneDrive\ -\ hk\ sar\ baomin\ inc/www/mysql_backup/$(date +%m_%d_%Y)_YAT_db_backup.sql
