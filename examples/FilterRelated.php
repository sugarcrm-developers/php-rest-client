<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);
try {
    if ($SugarAPI->isAuthenticated()) {
        echo "Logged In: " . json_encode($SugarAPI->getAuth()->getToken(),JSON_PRETTY_PRINT) . "\n";
        $Accounts = $SugarAPI->list('Accounts');
        $Accounts->fetch();
        //Get first Account in Collection
        $Account = $Accounts->at(1);
        //Execute Related contacts count API call
        $Account->getRelated('contacts', true);
        echo "Related Contacts Count response: " . json_encode($Account->getResponseBody(),JSON_PRETTY_PRINT) . "\n";
        //Assuming there are >0 related contacts, filter them by first name contain an `s`
        $Filter = $Account->filterRelated('contacts')->contains('first_name', 's');
        echo "Filter Def for API: " . json_encode($Filter->compile(),JSON_PRETTY_PRINT) . "\n";
        $Filter->execute();
        echo "Response:" . json_encode($Account->getResponseBody(),JSON_PRETTY_PRINT) . "\n";
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
