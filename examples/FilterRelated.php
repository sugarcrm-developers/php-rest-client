<?php

/**
 * ©[2024] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);
try {
    if ($SugarAPI->login()) {
        echo "<pre>";
        print_r($SugarAPI->getAuth()->getToken()->access_token);
        echo "</pre>";
        $Accounts = $SugarAPI->list('Accounts');
        $Accounts->fetch();
        $Account = $Accounts->at(1);
        $Account->getRelated('contacts', true);
        echo "<pre> Response:" . print_r($Account->getResponseBody(), true) . "</pre><br>";
        $Filter = $Account->filterRelated('contacts')->contains('first_name', 's');
        echo "<pre> Filter Contacts related to Account {$Account['id']} where first_name contains an 's': " . print_r($Filter->compile(), true) . "</pre><br>";
        $Filter->execute();
        echo "<pre> Response:" . print_r($Account->getResponseBody(), true) . "</pre><br>";
    } else {
        echo "Could not login.";
        pre($SugarAPI->getAuth()->getActionEndpoint('authenticate')->getResponse());
    }
} catch (Exception $ex) {
    echo "Error Occurred: ";
    pre($ex->getMessage());
}
