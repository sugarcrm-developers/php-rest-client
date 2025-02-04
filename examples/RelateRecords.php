<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);
try {
    if ($SugarAPI->isAuthenticated()) {
        echo "Logged In: " . json_encode($SugarAPI->getAuth()->getToken(), JSON_PRETTY_PRINT) . "\n";
        $Account = $SugarAPI->module('Accounts')->set("name", "Relate Records Test");
        $Account->save();
        echo "Created Account {$Account['id']}: " . json_encode($Account->toArray(), JSON_PRETTY_PRINT) . "\n";

        $Opportunity = $SugarAPI->module('Opportunities');
        $Opportunity['name'] = 'Test Opportunity';
        $Opportunity['description'] = 'This opportunity was created via the SugarCRM PHP REST Client v1 to test creating relationships.';
        $Opportunity->save();
        echo "Created Opportunity {$Opportunity['id']}: " . json_encode($Opportunity->toArray(), JSON_PRETTY_PRINT) . "\n";

        echo "Relating Opportunity to Account: ";
        $Account->relate('opportunities', $Opportunity['id']);
        echo "Response: " . json_encode($Account->getResponseBody(), JSON_PRETTY_PRINT) . "\n";
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
