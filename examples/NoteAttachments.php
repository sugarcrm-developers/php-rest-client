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
            echo "Logged In: " . json_encode($SugarAPI->getAuth()->getToken(),JSON_PRETTY_PRINT) . "\n";

            $Note = $SugarAPI->note()->set("name", "Test");
            echo "Creating Note with multiple attachments: ";
            $Note->multiAttach([
                //You can add files via filepath only
                $file,
                //OR You can add files via array, setting 'path' and the 'name' of the file you want it to have on upload
                [
                    'path' => $file,
                    'name' => 'foobar.txt',
                ],
                [
                    'path' => $file,
                    'name' => 'another.txt',
                ],
            ]);
            echo "Saved Note ID: {$Note['id']}\n";
            //Add attachment_list field to retrieve request so we can see uploaded files
            $Note->addField('attachment_list');
            $Note->retrieve();
            echo "Attachments: " . json_encode($Note->attachment_list,JSON_PRETTY_PRINT) . "\n";
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
