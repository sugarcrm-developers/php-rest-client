<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);
try {
    if ($SugarAPI->isAuthenticated()) {
        echo "Logged In: ";
        pre($SugarAPI->getAuth()->getToken());
        $Accounts = $SugarAPI->list('Accounts');
        $Accounts->filter()->and()
            ->or()
            ->starts('name', 's')
            ->contains('name', 'test')
            ->endOr()
            ->equals('assigned_user_id', 'seed_max_id')
            ->endAnd();
        echo "Filtering Accounts that are assigned to User Max, and that either start with an S or contain 'test' in the name: ";
        pre($Accounts->filter()->compile());
        foreach ($Accounts as $id => $account) {
            $Account = $Accounts->get($id);
            $Account['jfdksafjsdf'] = '';
            $Account->save();
        }
        $Accounts->count();
        echo "Running Count Request: ";
        pre($Accounts->getResponse()->getBody());
        echo "Running Filter Request: ";
        $Accounts->filter()->execute();
        echo "Accounts: ";
        pre($Accounts->toArray());
        $Accounts->clear();
        $Accounts->filter(true);
        echo "Filtering Accounts that are created between dates, or in the last 7 days: ";
        $Accounts->filter()->or()->date('date_entered')
            ->between(["2019-01-01", "2019-02-01"])
            ->endDate()
            ->date('date_entered')
            ->last7Days()
            ->endDate()
            ->endOr();
        pre($Accounts->filter()->compile());
        $Accounts->filter()->execute();
        echo "Accounts: ";
        pre($Accounts->toArray());
    } else {
        echo "Could not login.";
        pre($SugarAPI->getAuth()->getActionEndpoint('authenticate')->getResponse());
    }
} catch (Exception $ex) {
    echo "Error Occurred: ";
    pre($ex->getMessage());
    pre($ex->getTraceAsString());
}
