<?php

require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/classes/integrationDemoForm.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/functions/amoFunc.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/classes/connectionMySQL.php');

$amoInt = new IntegrationDemoForm($_REQUEST);
$DB = new ConnectMySQL($_REQUEST);

//из таблицы users_amocrm нужно удалить всех текущих юзеров, а после запустить этот скрипт
//создание таблицы юзеров
// $DB->createTableUsers('users_amocrm');

//извлечение из амо актуального списка юзеров
$users = $amoInt->amoCRM->account->users;
//парсинг юзеров в БД
foreach ($users as $user){
    $DB->DB->query("INSERT INTO users_amocrm (user_name, user_id) VALUES (:user_name, :user_id)", array($user->name, $user->id));
}

$DB->DB->closeConnection();


//$response = $amoInt->amoCRM->ajax()->get('/api/v4/companies', [ 'page' => 5, 'limit' => 250 ]);
//$list = $response->_embedded->companies;
//
//foreach ($list as $company){
//
//    $company_id = $company->id;
//    $company = $amoInt->amoCRM->companies()->find( $company_id );
//    if(!empty($company->cf('Телефон')->values[0]->value)){
//
//        $company->cf('Телефон')->reset();
//        $company->save();
//    }
//
////    $company_name = $company->name;
////    $resp_id = $company->responsible_user_id;
////    $group_id = $company->group_id;
//
//}



