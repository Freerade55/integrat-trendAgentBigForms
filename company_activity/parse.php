<?php

require_once ('require.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL(null);

//вытаскиваем из таблицы компании с заполненной датой последней задачи и со статусом unset
$companies = [
    28133479,
    28132477,
    28132459,
    28132265,
    28131631,
];

var_dump(count($companies));
$i = 1;
$time_now = time();

foreach ($companies as $company){

    $company_amo = $amoInt->amoCRM->companies()->find($company);
    $company_name = $company_amo->name;
    $location_company = $company_amo->cf()->byId(838497)->getValue();
    if(empty($location_company)) $location_company = null;
    $created_at = $company_amo->created_at;
    $resp_id = $company_amo->responsible_user_id;
    $group_id = 228;

    $DB->DB->query("INSERT INTO auto_tasks (company_id, company_name, resp_id, group_id, last_task_with_result, last_lead_recon, company_location)
                    VALUES (:company_id, :company_name, :resp_id, :group_id, :last_task_with_result, :last_lead_recon, :company_location)",
        ['company_id' => $company,
            'company_name' => $company_name,
            'resp_id' => $resp_id,
            'group_id' => $group_id,
            'last_task_with_result' => $created_at,
            'last_lead_recon' => $created_at,
            'company_location' => $location_company,]
    );
}
$DB->DB->closeConnection();

/*
foreach ($companies as $company_db) {

    $company_amo = $amoInt->amoCRM->companies()->find($company_db['company_id']);

    if($company_amo){

        $location_company = $company_amo->cf()->byId(838497)->getValue();

        if(empty($location_company)) $location_company = null;
        else{

            $DB->DB->query("UPDATE auto_tasks SET company_location = ? WHERE company_id = ?",
                array($location_company, $company_db['company_id']));

            $i++;
        }

    }
    else{

        $DB->DB->query("DELETE FROM auto_tasks WHERE company_id = ?",
            array($company_db['company_id']));

        $i++;
    }
}

*/


