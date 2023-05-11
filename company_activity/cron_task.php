<?php

require_once ('require.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL(null);

//вытаскиваем из таблицы задачи со статусом unset
$raw_tasks = $DB->DB->query("SELECT * FROM hook_task WHERE task_status = :task_status",['task_status' => 'unset']);

foreach ($raw_tasks as $task){

    //для каждой строки задачи которую обработали ставим set
    $DB->DB->query("UPDATE hook_task SET task_status = :status WHERE id = :id_task",
        array("status" => 'set', "id_task" => $task['id']));

    //если у задачи имеется текст-результат, то...
    if (!empty($task['result_text'])){

        //ищем строку (одну) в таблице компаний с company_id таким же как в element_id задачи
        $company = $DB->DB->row("SELECT * FROM auto_tasks WHERE company_id = :element_id",['element_id' => $task['element_id']]);

        if($company){

            //форматируем дату в unix
            $unix_task_date = strtotime($task['created_at']);

            //в столбец last_task_with_result ставим дату последней задачи с результатом, для той компании, id которой был указан в задаче
            $DB->DB->query("UPDATE auto_tasks SET last_task_with_result = ?, last_task_status = ?  WHERE company_id = ?",
                array($unix_task_date, 'unset', $task['element_id']));
        }
    }
}

$DB->DB->closeConnection();

//if ($task['status'] == 1){
//
//    $DB->DB->query("INSERT INTO hook_task (task_id, task_type, task_resp, company_id, is_close, result_text)
//                    VALUES (:task_id, :task_type, :task_resp, :company_id, :is_close, :result_text)",
//        ['task_id'     => $task['id'],
//            'task_type'   => $task['task_type'],
//            'task_resp'   => $task['responsible_user_id'],
//            'company_id'  => $task['element_id'],
//            'is_close'    => $task['status'],
//            'result_text' => $task['result']['text'],]
//    );
//}
