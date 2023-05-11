<?php

require_once ('require.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL(null);

$post = $_REQUEST;
$fw = fopen("hook_company.txt", "a");
fwrite($fw, "\n".'['.date('Y-d-m H:i:s').'] => Correct form'."\n");
fwrite($fw, print_r($post, true));
fclose($fw);


$company = $post['contacts']['add'][0];
$company_id = $company['id'];
$company_name = $company['name'];
$resp_id = $company['responsible_user_id'];
$group_id = $company['group_id'];
$time_now = time();

$company_amo = $amoInt->amoCRM->companies()->find($company_id);
if($company_amo){

    $location_company = $company_amo->cf()->byId(838497)->getValue();
    if(empty($location_company)) $location_company = null;
}

$DB->DB->query("INSERT INTO auto_tasks (company_id, company_name, resp_id, group_id, last_task_with_result, last_lead_recon, company_location)
                    VALUES (:company_id, :company_name, :resp_id, :group_id, :last_task_with_result, :last_lead_recon, :company_location)",
        ['company_id' => $company_id,
        'company_name' => $company_name,
        'resp_id' => $resp_id,
        'group_id' => $group_id,
        'last_task_with_result' => $time_now,
        'last_lead_recon' => $time_now,
        'company_location' => $location_company,]
);

$DB->DB->closeConnection();


//$response = $amoInt->amoCRM->ajax()->get('/api/v4/companies', [ 'page' => 19, 'limit' => 250 ]);
//$list = $response->_embedded->companies;
//
//foreach ($list as $company){
//
//    $company_id = $company->id;
//    $company_name = $company->name;
//    $resp_id = $company->responsible_user_id;
//    $group_id = $company->group_id;
//
//}

//$DB->DB->query(
//    "CREATE TABLE IF NOT EXISTS `auto_tasks` (
//                    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//                    `company_id` int(15),
//                    `company_name` varchar (200),
//                    `resp_id` int(15),
//                    `group_id` int(15),
//                    `last_task_with_result` int(30),
//                    `last_task_status` varchar (10) DEFAULT 'unset',
//                    `last_lead_recon` int(30),
//                    `last_lead_recon_status` varchar (10) DEFAULT 'unset',
//                    `first_lead_recon` int(30),
//                    `second_lead_recon` int(30),
//                    `two_month_lead_recon_status` varchar (10) DEFAULT 'unset'
//                )"
//);
