<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);

try {
    if ($SugarAPI->isAuthenticated()) {
        echo "Logged In: " . json_encode($SugarAPI->getAuth()->getToken(),JSON_PRETTY_PRINT) . "\n";

        $lead1 = $SugarAPI->module("Leads");
        $lead2 = $SugarAPI->module("Leads");
        $lead1['first_name'] = "Test";
        $lead1['last_name'] = "Lead";

        //Set same data on Lead 2
        $lead2->set($lead1->toArray());
        //Add 2 to second lead last name to differentiate
        $lead2['last_name'] .= "2";
        //By default a SugarBean Endpoint with no action and ID will do a CREATE API Request
        //To change the API used for the Model Endpoint, set the Action
        //$lead1->setCurrentAction(\MRussell\REST\Endpoint\ModelEndpoint::MODEL_ACTION_CREATE);
        $bulk = $SugarAPI->bulk();
        $bulk->setData([
            $lead1,
            $lead2,
        ]);
        echo "Bulk Request Payload: " . json_encode($bulk->getData()->toArray(), JSON_PRETTY_PRINT) . "\n";
        $bulk->execute();
        echo  "Bulk Response: " . json_encode($bulk->getResponseBody(), JSON_PRETTY_PRINT) . "\n";
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