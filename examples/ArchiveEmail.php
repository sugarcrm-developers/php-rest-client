<?php


require_once 'include.php';

$SugarAPI = new \Sugarcrm\REST\Client\SugarApi($server,$credentials);
try{
    if ($SugarAPI->login()){
        echo "Logged In: ";
        pre($SugarAPI->getAuth()->getToken());

        $eAddresses = $SugarAPI->list('EmailAddresses');
        $eAddresses->filter()->in('email_address',['mrussell@sugarcrm.com']);
        pre(json_encode($eAddresses->filter()->compile()));
        $eAddresses->execute();
        $from['email_address_id'] = $eAddresses->at(0)->id;
        pre($from);

        $Email = $SugarAPI->Email();
        $Email->set('name','Test Email 12');
        $Email->set('description_html','<img src="/rest/v11_25/Notes/dc1a77ee-407b-11ef-92e6-06242d89c115/file/filename?force_download=0&platform=base">');
        $Email->set('from',[
            'create' => [
                $from,
            ]
        ]);
        $Email->set('notes',[
            'add' => [
                "dc1a77ee-407b-11ef-92e6-06242d89c115",
                "dc18c688-407b-11ef-aa3a-06242d89c115",
            ]
        ]);
        $Email->save();
        pre($Email->toArray());
    } else {
        echo "Could not login.";
        pre($SugarAPI->getAuth()->getActionEndpoint('authenticate')->getResponse());
    }
}catch (Exception $ex){
    echo "Error Occurred: ";
    pre($ex->getMessage());
    pre($ex->getTraceAsString());
}