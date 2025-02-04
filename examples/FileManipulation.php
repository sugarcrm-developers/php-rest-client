<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

use GuzzleHttp\Middleware;

require_once 'include.php';
$file = __DIR__ . '/test.txt';

if (file_exists($file) && is_readable($file)) {
    $SugarAPI = new \Sugarcrm\REST\Client\SugarAPI($server, $credentials);
    try {
        if ($SugarAPI->isAuthenticated()) {
            echo "Logged In: " . json_encode($SugarAPI->getAuth()->getToken(), JSON_PRETTY_PRINT) . "\n";

            $Note = $SugarAPI->module('Notes')->set("name", "Test");
            //Create a note with subject
            $Note->save();
            echo "Saved Note ID: {$Note['id']}<br>";
            echo "Attempting to attach $file...";
            $Note->attachFile('filename', $file, true, 'testtest.txt');
            echo "File uploaded: " . json_encode($Note->getResponseBody(), JSON_PRETTY_PRINT) . "\n";

            $Note = $SugarAPI->module('Notes');
            echo "Uploading temp file for new note...";
            $Note->tempFile('filename', $file);
            echo "Temp File uploaded: " . json_encode($Note->getResponseBody(), JSON_PRETTY_PRINT) . "\n";
            $Note->set('name', 'This is a test');
            $Note->save();
            echo "Note ID: {$Note['id']}\n";
        } else {
            echo "Could not login.";
            $oauthEndpoint = $SugarAPI->getAuth()->getActionEndpoint('authenticate');
            $statusCode = $oauthEndpoint->getResponse()->getStatusCode();
            echo "[$statusCode] - " . $oauthEndpoint->getResponse()->getBody()->getContents();
        }
    } catch (Exception $ex) {
        echo "Exception Occurred: " . $ex->getMessage();
        echo $ex->getTraceAsString();
    }
} else {
    if (!file_exists($file)) {
        echo "Test file does not exist. Create/upload $file";
    }
    if (!is_readable($file)) {
        echo "Test file is not readable for upload. Fix permissions and try again.";
    }
}
