<?php

require_once ('require.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL(null);

$post = $_REQUEST;
$fw = fopen("hook_task.txt", "a");
fwrite($fw, "\n".'['.date('Y-d-m H:i:s').'] => Correct form'."\n");
fwrite($fw, print_r($post, true));
fclose($fw);

$task = $post['task']['update'][0];

if ($task['task_type'] == 2682907) exit();

if ($task['status'] == 1){

    $DB->DB->query("INSERT INTO hook_task (task_id, task_type, task_resp, element_id, is_close, result_text)
                    VALUES (:task_id, :task_type, :task_resp, :element_id, :is_close, :result_text)",
        ['task_id'     => $task['id'],
            'task_type'   => $task['task_type'],
            'task_resp'   => $task['responsible_user_id'],
            'element_id'  => $task['element_id'],
            'is_close'    => $task['status'],
            'result_text' => $task['result']['text'],]
    );
    $DB->DB->closeConnection();
}



//$DB->DB->query(
//    "CREATE TABLE IF NOT EXISTS `hook_task` (
//                    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//                    `task_id` int(15),
//                    `task_type` int(15),
//                    `task_resp` int(15),
//                    `element_id` int(15),
//                    `is_close` int(15),
//                    `result_text` varchar (250),
//                    `task_status` varchar (10) DEFAULT 'unset'
//                )"
//);

