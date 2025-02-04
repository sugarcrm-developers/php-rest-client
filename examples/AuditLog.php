<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarAPI($server, $credentials);
try {
    if ($SugarAPI->isAuthenticated()) {
        echo "Logged In: " . json_encode($SugarAPI->getAuth()->getToken(), JSON_PRETTY_PRINT) . "\n";
        // Due to core Bug on Sugar 11+, Audit Log API has a min version of 11_11, so I'm forcing it here
        $SugarAPI->setVersion('11_11');

        $Account = $SugarAPI->module('Accounts')->set("name", "Audit Log Test");
        $Account->save();
        echo "Created Account: {$Account['id']}\n";
        //Update the account to generate Audits
        $Account->set('phone_office', '555-555-5555');
        $Account['name'] = 'Audit Log Test - Updated';
        $Account['assigned_user_id'] = 'seed_max_id';
        $Account->save();
        echo "Account Updated: " . json_encode($Account->toArray(), JSON_PRETTY_PRINT) . "\n";
        $Account->audit();
        echo "Audit Log: " . json_encode($Account->getResponseBody(), JSON_PRETTY_PRINT) . "\n";
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
