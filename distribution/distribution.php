<?php

require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/classes/integrationDemoForm.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/functions/amoFunc.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/classes/connectionMySQL.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL($_REQUEST);

$unset_data = $DB->DB->query("SELECT * FROM csv_data WHERE status=:status",['status'=>'unset']);


foreach ($unset_data as $key => $agency){

    $resp_data = $DB->DB->row("SELECT * FROM users_amocrm WHERE user_name=:user_name",['user_name'=>$agency['resp_name']]);

    $resp_id = $resp_data['user_id'];
    $company_id = $agency['company_id'];

    // работа с компанией
    $company = $amoInt->amoCRM->companies()->find($company_id);
    changeRespOnCompany($company, $resp_id);
    $tasks_company = $company->tasks;

    changeRespOnTasks($tasks_company, $resp_id);
    

    // работа с лидами компании
    $leads = $company->leads;
    changeRespOnLeads($leads, $resp_id);

    //работа с контактами компании
    $contacts = $company->contacts;
    changeRespOnContacts($contacts, $resp_id);

    $DB->DB->query("UPDATE csv_data SET status = :status WHERE id = :id", ["status"=>"set", "id"=>$agency['id']]);

}

$DB->DB->closeConnection();

