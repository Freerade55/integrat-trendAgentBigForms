<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/trendAgent/site/integration-amoCRM/vendor/autoload.php';

/*
 * @params array $inputData
 * @returm array leads
 * */
function parseInputData ( $inputData )
{
    $leads = [
        [
            'Name' => $inputData[ 'Client_1' ] ?? null,
            'Застройщик' => $inputData[ 'Застройщик_' ] ?? null,
            'Дата_подписания_договора' => $inputData[ 'Дата_подписания_договора_' ] ?? null,
            'Номер_договора' => $inputData[ 'Номер_договора' ] ?? null,
            'Стоимость_квартиры_по_договору' => $inputData[ 'Стоимость_квартиры_по_договору' ] ?? null,
            'Платёжное_поручение' => $inputData[ 'Платёжное_поручение_0' ] ?? null,
        ]
    ];

    for ( $i = 3;; $i++ )
    {
        if ( !isset( $inputData[ "Client_" . ( $i - 1 ) ] ) ) break;

        $leads[] = [
            'Name' => $inputData[ "Client_" . ( $i - 1 ) ] ?? null,

            'Застройщик' => $inputData[ "Застройщик__" . ( $i - 1 ) ] ?? null,

            'Дата_подписания_договора' => $inputData[ "Дата_подписания_договора__" . ( $i - 1 ) ] ?? null,

            'Номер_договора' => $inputData[ "Номер_договора_" . ( $i - 1 ) ] ?? null,

            'Стоимость_квартиры_по_договору' => $inputData[ "Стоимость_квартиры_по_договору_" . ( $i - 1 ) ] ?? null,

            'Платёжное_поручение' => $inputData[ "Платёжное_поручение_" . ( $i - 1 ) ] ?? null,
        ];
    }

    return $leads;
}

/*
 * @params array $postData
 * @returm array $note
 * */
function generateNote($postData){

    $note = [];
    foreach ($postData as $key => $value){
        if($key == 'COOKIES') continue;
        $note[] = $key.': '.$value;
        $note[] = '----------------------';
    }
    
    return $note;

}

/*
 * @params object $tasks, string $resp_id
 * @returm void
 * */
function changeRespOnTasks($tasks, $resp_id){

    foreach ($tasks->collection() as $task){

        if($task->is_completed == false){

            $task->responsible_user_id = $resp_id;
            $task->save();
        }
    }
}

/*
 * @params object $leads, string $resp_id
 * @returm void
 * */
function changeRespOnLeads($leads, $resp_id){

    foreach ($leads->collection() as $lead) {

        $lead->responsible_user_id = $resp_id;
        $lead->save();

        $tasks = $lead->tasks;
        changeRespOnTasks($tasks, $resp_id);
    }
}

/*
 * @params object $contacts, string $resp_id
 * @returm void
 * */
function changeRespOnContacts($contacts, $resp_id){

    foreach ($contacts->collection() as $contact){

        $contact->responsible_user_id = $resp_id;
        $contact->save();
        $contact_leads = $contact->leads;
        changeRespOnLeads($contact_leads, $resp_id);

        $tasks = $contact->tasks;
        changeRespOnTasks($tasks, $resp_id);

    }
}

/*
 * @params object $company, string $resp_id
 * @returm void
 * */
function changeRespOnCompany($company, $resp_id){

    $company->responsible_user_id = $resp_id;
    $company->save();
}

/*
 * @params object $entity, string $type_task, string $element_type,
 * @returm void
 * */
function addTask(
    $entity,
    $type_task,
    $element_type,
    $expireTime,
    $text = 'Заявка с сайта',
    $ruid = false
)
{
    $task = $entity->createTask( $type = $type_task );
    if (!$ruid) {
        $ruid = $entity->responsible_user_id;
    }
    $task->responsible_user_id = $ruid;
    $task->element_id = $entity->id;
    $task->element_type = $element_type;
    $task->complete_till_at = $expireTime;
    $task->text = $text;
    $task->save();
}

/*
 * @params object $amoInt, $lead
 * @returm object $company_amo
 * */
function checkCompanyOnLead($amoInt, $lead){

    $lead_amo = $amoInt->amoCRM->leads()->find($lead['lead_id']);
    $company_amo = $lead_amo->company;

    return $company_amo;
}

/*
 * @params object $DB, $company_id
 * @returm object $company_amo
 * */
function searchCompanyInDB($DB, $company_id){

    $company = $DB->DB->row("SELECT * FROM auto_tasks WHERE company_id = ?",[$company_id]);

    if (!$company){

        $fw = fopen("company_not_found.txt", "a");
        fwrite($fw, "\n".'['.date('Y-d-m H:i:s').'] => Уведомление из amoFunc'."\n");
        fwrite($fw, print_r('Компания с id: '.$company_id.' не найдена в БД', true));
        fclose($fw);
    }

    return $company;
}

/*
 * @params string - 2022-07-20 13:40:40 - $date_raw_lead, string - 1654305600 - $company_id
 * @returm object $diff
 * */
function getDiffBtwnDate($date_raw_lead, $date_lead_db){

    //создаем объект даты по дате новой сверки
    $date2 = DateTime::createFromFormat("Y-m-d H:i:s", $date_raw_lead);
    $date2->modify('last day of this month');

    //сравниваем месяц даты из поля БД с месяцем даты текущей сверки
    //дату сверки БД переводим из юникс в норм формат
    $date1 = date("Y-m-d H:i:s", $date_lead_db);
    //создаем объект из юникс даты
    $date1 = DateTime::createFromFormat("Y-m-d H:i:s", $date1);
    //берем первое число месяца из первой даты
    $date1->modify('first day of this month');

    $diff = $date2->diff($date1); // получаем разницу в виде объекта DateInterval

    return $diff;
}