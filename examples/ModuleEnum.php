<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */
require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);

try {
    if ($SugarAPI->isAuthenticated()) {
        echo "Logged In: " . json_encode($SugarAPI->getAuth()->getToken(), JSON_PRETTY_PRINT) . "\n";

        $Enum = $SugarAPI->enum('Accounts', 'account_type')->execute();
        echo "Account Type options: " . json_encode($Enum->getResponseBody(), JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Could not login.";
        $oauthEndpoint = $SugarAPI->getAuth()->getActionEndpoint('authenticate');
        $response = $oauthEndpoint->getResponse();
        if ($response) {
            $statusCode = $oauthEndpoint->getResponse()->getStatusCode();
            echo "[$statusCode] - " . $oauthEndpoint->getResponse()->getBody()->getContents();
        }
    }
} catch (Exception $ex) {
    echo "Exception Occurred: " . $ex->getMessage();
    echo $ex->getTraceAsString();
}
