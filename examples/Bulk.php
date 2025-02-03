<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);
try {
    if ($SugarAPI->isAuthenticated()) {
        $lead1 = $SugarAPI->module("Leads");
        $lead2 = $SugarAPI->module("Leads");
        $lead1['first_name'] = "Test";
        $lead1['last_name'] = "Lead";

        //Set same data on Lead 2
        $lead2->set($lead1->toArray());
        //Add 2 to second lead last name to differentiate
        $lead2['last_name'] .= "2";
        //Set
        $lead1->setCurrentAction(\MRussell\REST\Endpoint\ModelEndpoint::MODEL_ACTION_CREATE);
        $lead2->setCurrentAction(\MRussell\REST\Endpoint\ModelEndpoint::MODEL_ACTION_CREATE);
        $bulk = $SugarAPI->bulk();
        $bulk->setData([
            $lead1,
            $lead2,
        ]);
        echo json_encode($bulk->getData()->toArray(), JSON_PRETTY_PRINT);
        $bulk->execute();
        echo json_encode($bulk->getResponseContent(), JSON_PRETTY_PRINT);
    } else {
        echo "Could not login.";
        pre($SugarAPI->getAuth()->getActionEndpoint('authenticate')->getResponse());
    }
} catch (Exception $ex) {
    echo "Error Occurred: ";
    pre($ex->getMessage());
    pre($ex->getTraceAsString());
}
