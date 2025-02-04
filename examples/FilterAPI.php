<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);
try {
    if ($SugarAPI->isAuthenticated()) {
        echo "Logged In: " . json_encode($SugarAPI->getAuth()->getToken(), JSON_PRETTY_PRINT) . "\n";

        $Accounts = $SugarAPI->list('Accounts');
        $Accounts->filter()->and()
            ->or()
            ->starts('name', 's')
            ->contains('name', 'test')
            ->endOr()
            ->equals('assigned_user_id', 'seed_max_id')
            ->endAnd();
        echo "Filtering Accounts that are assigned to User Max, and that either start with an S or contain 'test' in the name: \n"
            . json_encode($Accounts->filter()->compile());
        // Run count API with filter
        $Accounts->count();
        echo "Found " . $Accounts->getTotalCount() . " Accounts.\n";
        // Run filter request
        $Accounts->fetch();
        echo json_encode($Accounts->toArray(), JSON_PRETTY_PRINT) . "\n";
        //Clear out Collection
        $Accounts->clear();
        //Reset Filter definition
        $Accounts->filter(true);

        $Accounts->filter()->or()->date('date_entered')
            ->between(["2019-01-01", "2019-02-01"])
            ->endDate()
            ->date('date_entered')
            ->last7Days()
            ->endDate()
            ->endOr();
        echo "Filtering Accounts that are created between dates, or in the last 7 days: "
            . json_encode($Accounts->filter()->compile(), JSON_PRETTY_PRINT) . "\n";
        $Accounts->fetch();
        echo "Accounts: " . json_encode($Accounts->toArray(), JSON_PRETTY_PRINT) . "\n";
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
