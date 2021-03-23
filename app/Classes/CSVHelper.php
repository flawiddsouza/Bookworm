<?php

namespace App\Classes;

class CSVHelper
{
    // From: https://gist.github.com/jaywilliams/385876
    public static function readCsvFileIntoArray($csvFilePath)
    {
        $csvArray = [];
        $headers = [];
        $firstRow = true;

        if(($handle = fopen($csvFilePath, 'r')) !== false) {
            while(($row = fgetcsv($handle, 0, ',')) !== false) {
                if($firstRow) {
                    $headers = $row;
                    $firstRow = false;
                } else {
                    $csvArray[] = array_combine($headers, $row);
                }
            }
            fclose($handle);
        }

        return $csvArray;
    }

    public static function validate($csvRows, $requiredColumnHeaders)
    {
        if(count($csvRows) === 0) {
            return response('No data found', 400);
        }

        $receivedColumnHeaders = array_keys($csvRows[0]);
        $missingColumnHeaders = array_diff($requiredColumnHeaders, $receivedColumnHeaders);

        if(count($missingColumnHeaders) !== 0) {
            return response('Missing column headers: ' . implode(', ', $missingColumnHeaders), 400);
        }

        return 'valid';
    }
}
