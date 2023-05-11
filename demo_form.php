<?php

require_once __DIR__ . '/classes/integrationDemoForm.php';
require_once __DIR__ . '/functions/amoFunc.php';

$rawData = json_decode(file_get_contents('php://input'), true);

$demoStatus = 'start_demo';

if ( $rawData['Demo_status'] == $demoStatus ){

    $postData = $rawData;
    $fw = fopen(__DIR__ . "/logs/demo_form_correct.txt", "a");
    fwrite($fw, "\n".'['.date('Y-d-m H:i:s').'] => Correct form'."\n");
    fwrite($fw, print_r($postData, true));
    fclose($fw);

}
else {
    exit();
}

$targetPipelineID = 4520776; // воронка подключение

$amoInt = new IntegrationDemoForm( $postData );

// поиск контакта по номеру
$currentContact = $amoInt->getCurrentContact();

if ( !$currentContact )
{   // не нашли контакт с этим номером

    $newContact = $amoInt->createContact();

    // формирование текста примечания
    $note = [
        'Новая заявка с Сайта!',
        '----------------------',
        ' Телефон : ' . $amoInt->phone,
        '----------------------',
        ' Email : ' . $amoInt->email,
        '----------------------',
        ' user_id : ' . $amoInt->user_id,
        '----------------------',
        ' Статус демо : ' . $amoInt->demo_status,
        '----------------------',
    ];
    $textNote = implode("\n", $note);
    $amoInt->addNote ( $newContact, $textNote );

    $newLead = $amoInt->createLeadInContact ( $newContact );

    // создание задачи на лиде, время выполнения 2 часа
//    $task = $amoInt->addTask (
//        $newLead,
//        time() + 7200,
//        $text = 'Клиент оставил заявку на форме демо-доступ' // FIXME
//    );

}
else // есть контакт с таким номером
{
    $note = [
        'Новая заявка с Сайта!',
        '----------------------',
        ' Телефон : ' . $amoInt->phone,
        '----------------------',
        ' Email : ' . $amoInt->email,
        '----------------------',
        ' user_id : ' . $amoInt->user_id,
        '----------------------',
        ' Статус демо : ' . $amoInt->demo_status,
        '----------------------',
    ];
    $textNote = implode("\n", $note);
    $amoInt->addNote ( $currentContact, $textNote );

    $currentLead = null;

    // получаем список сделок на контакте
    $leads = $amoInt->getListOfLeads ( $currentContact );

    if ( $leads )
    { // если сделки есть
        $ActiveNotExist = true;

        // перебираем каждую
        foreach ( $leads->collection()->all() as $lead )
        {
            if ( // проверка, закрыта ли сделка (142-143) и находится в нужной воронке (4520776)
                $lead->status_id !== 143 && // почему тут И, а не ИЛИ
                $lead->status_id !== 142 &&
                $lead->pipeline_id === $targetPipelineID
            )
            {
                // если сделка в первом или во втором этапе
                if ( $lead->status_id === 41735656 || $lead->status_id === 41735659 )
                {
                    // сменить этап на третий
                    $amoInt->changeStatusOfLead( $lead, 41735662 );
                }
                else {}

//                $task = $amoInt->addTask (
//                    $lead,
//                    time() + 7200,
//                    $text = 'Клиент оставил заявку на форме демо-доступ' // FIXME
//                );

                $ActiveNotExist = false;

                $currentLead = $lead;

                break;
            }
            else {}
        }

        if ( $ActiveNotExist )
        { // если активного лида нет на контакте

            $newLead = $amoInt->createLeadInContact ( $currentContact );

            $currentLead = $newLead;

            // TODO create a task in the lead *
//            $task = $amoInt->addTask (
//                $newLead,
//                time() + 7200,
//                $text = 'Клиент оставил заявку на форме демо-доступ' // FIXME
//            );
        }
    }
    else
    { // если лидов на контакте вообще нет

        $newLead = $amoInt->createLeadInContact ( $currentContact );

//        $task = $amoInt->addTask (
//            $newLead,
//            time() + 7200,
//            $text = 'Клиент оставил заявку на форме демо-доступ' // FIXME
//        );

        $currentLead = $newLead;
    }
}
