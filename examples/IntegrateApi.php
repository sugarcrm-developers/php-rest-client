<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server, $credentials);
$logger = new \ColinODell\PsrTestLogger\TestLogger();
$SugarAPI->getAuth()->setLogger($logger);
try {
    if ($SugarAPI->isAuthenticated()) {
        echo "Logged In: " . json_encode($SugarAPI->getAuth()->getToken(), JSON_PRETTY_PRINT) . "\n";

        $account = $SugarAPI->module('Accounts');
        $account->set([
            'name' => 'Upsert Test Account',
            'sync_key' => 'foobar',
        ]);
        $account->upsert();
        echo "Record: " . json_encode($account->toArray(), JSON_PRETTY_PRINT) . "\n";

        $contact = $SugarAPI->module('Contacts');
        $contact->set([
            'first_name' => 'Upsert',
            'last_name' => 'Test Contact',
        ]);
        //Create a contact
        $contact->save();
        echo "Created Contact: " . $contact->getId() . "\n";

        $integrate = $SugarAPI->integrate();
        $integrate->fromBean($contact);
        $integrate->setSyncKey('uniqueSyncKey');
        if ($integrate->getResponse()->getStatusCode() == 200) {
            echo "Contact Sync Key set: " . $contact->getSyncKey() . "\n";
        }

        $samecontact = $SugarAPI->integrate('Contacts');
        $samecontact->getBySyncKey('uniqueSyncKey');
        echo "Retrieved Contact by Sync Key: " . $samecontact->getId() . "\n";

        $integrate->deleteBySyncKey();
        echo "Deleted Contact: " . json_encode($account->toArray(), JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Could not login.";
        print_r($logger->records);
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
