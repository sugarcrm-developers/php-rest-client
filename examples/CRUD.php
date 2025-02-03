<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);

try {
    if ($SugarAPI->isAuthenticated()) {
        echo "Logged In: " . json_encode($SugarAPI->getAuth()->getToken(),JSON_PRETTY_PRINT) . "\n";
        // Create an Account called Test with a phone number
        $Account = $SugarAPI->module('Accounts');
        // You can set data via Array Access, Object Access, or set methods
        $Account->set("name", "Test")
            ->set("phone_office", "555-555-5555");
        $Account['account_type'] = 'Prospect';
        $Account->email1 = "test@sugar.dev";
        $Account->save();
        echo "Saved Account ID: {$Account['id']}\n";
        //Update Account
        $Account->set('employees', '100');
        $Account['shipping_address_city'] = 'Indianapolis';
        $Account->save();
        echo "Account Updated: " . json_encode($Account->toArray(),JSON_PRETTY_PRINT) . "\n";

        //Retrieve the Account in a new Object
        $Account2 = $SugarAPI->module('Accounts', $Account['id']);
        $Account2->retrieve();
        echo "Retrieved Account: " . json_encode($Account2->toArray(),JSON_PRETTY_PRINT) . "\n";
        //Delete the Account
        $Account2->delete();
        echo "Deleted Response: " . json_encode($Account2->getResponseBody(),JSON_PRETTY_PRINT) . "\n";
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
