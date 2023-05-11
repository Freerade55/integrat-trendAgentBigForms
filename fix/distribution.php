<?php

require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/classes/integrationDemoForm.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/functions/amoFunc.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/classes/connectionMySQL.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL($_REQUEST);

$unset_data = $DB->DB->query("SELECT * FROM csv_data_fix WHERE status=:status",['status'=>'unset']);


foreach ($unset_data as $key => $task_db){

    $resp_id = $task_db['resp_id'];
    $task_id = $task_db['task_id'];

    $task = $amoInt->amoCRM->tasks()->find($task_id);

    if(!empty($task)){

        $task->responsible_user_id = $resp_id;
        $task->save();

    }

    $DB->DB->query("UPDATE csv_data_fix SET status = :status WHERE id = :id", ["status"=>"set", "id"=>$task_db['id']]);
    sleep(1);
}

$DB->DB->closeConnection();

