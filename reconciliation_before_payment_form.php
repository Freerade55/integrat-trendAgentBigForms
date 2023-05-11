<?php

require_once __DIR__ . '/classes/integrationReconForm.php';
require_once __DIR__ . '/functions/amoFunc.php';

$postData = $_REQUEST;
$fw = fopen(__DIR__ . "/logs/recon_form_correct.txt", "a");
fwrite($fw, "\n".'['.date('Y-d-m H:i:s').'] => Correct form'."\n");
fwrite($fw, print_r($postData, true));
fclose($fw);

$amoInt = new IntegrationReconForm($postData);

$targetPipelineID = 4670839; // Продажи АН
$targetStatusID = 41787490;
$resp_id = 7311790; //Юля Машенкова
$task_time = strtotime(today) + 84000; // today = сегодня 00:00 - уже просроченная, + 23 часа 40 мин
$type_task = 2578015; //тип задачи Выяснить

$currentContact = $amoInt->getCurrentContact();

$leadList = parseInputData($postData);

if (!$currentContact) {  // don`t have contact

    $newContact = $amoInt->createContact();

    $note = [
        'Новая заявка с Сайта сверки!',
        '----------------------',
        ' Телефон : ' . $amoInt->phone,
        '----------------------',
        ' Название агенства: ' . $amoInt->name_agency,
        '----------------------',
        ' Email : ' . $amoInt->email,
        '----------------------',
        ' Имя клиента : ' . $amoInt->name_client,
        '----------------------',
        ' Застройщик : ' . $amoInt->developer,
        '----------------------',
        ' Дата подписания договора : ' . $amoInt->date_signature_the_agreement,
        '----------------------',
        ' Номер договора  : ' . $amoInt->number_of_contract,
        '----------------------',
        ' ИНН  : ' . $amoInt->INN,
        '----------------------',
        ' Стоимость квартиры по договору : ' . $amoInt->price_of_apartment,
        '----------------------',
        ' Платежное поручение : ' . $amoInt->payment_order,
        '----------------------',
//        ' Коммент по оплате  : ' . $amoInt->comment_on_payment,
//        '----------------------',
//        ' Feedback  : ' . $amoInt->feedback_backoffice,
//        '----------------------',
//        ' Коммент по работе ТА  : ' . $amoInt->comment_about_TA,
//        '----------------------',
    ];
    $textNote = implode("\n", $note);
    $amoInt->addNote($newContact, $textNote);

    $company = $amoInt->globalSearchCompanies();

    for ($leadListIndex = 0; $leadListIndex < count($leadList); $leadListIndex++) {

        $newLead = $amoInt->createLeadInContact($newContact, $leadList[$leadListIndex]);

//        $task = $amoInt->addTask(
//            $newLead,
//            $task_time,
//            null,
//            null,
//            $text = 'Клиент заполнил форму сверки'
//        );

        if ($company) {
            $amoInt->attachCompany($newLead, $company);
            $amoInt->attachCompany($newContact, $company);
            $newLead->attachTags(['Есть компания']);
            $newLead->save();

        } else {

            $task = $amoInt->addTask(
                $newLead,
                $task_time,
                $resp_id,
                $type_task,
                $text = 'Компанию с таким инн не удалось найти. Проверь ИНН, добавь его в соответствующую компанию
             и привяжи к данной сделке, а после закрой сделку в успешно'
            );
        }
    }
} else { // have contact

    $currentLead = null;
    //поиск сделок у контакта
    $leads = $amoInt->getListOfLeads($currentContact);
    // поиск компании на найденном контакте
    $currentCompanyOnContact = $amoInt->getCompany($currentContact);

    $note = [
        'Новая заявка с Сайта сверки!',
        '----------------------',
        ' Телефон : ' . $amoInt->phone,
        '----------------------',
        ' Название агенства: ' . $amoInt->name_agency,
        '----------------------',
        ' Email : ' . $amoInt->email,
        '----------------------',
        ' Имя клиента : ' . $amoInt->name_client,
        '----------------------',
        ' Застройщик : ' . $amoInt->developer,
        '----------------------',
        ' Дата подписания договора : ' . $amoInt->date_signature_the_agreement,
        '----------------------',
        ' Номер договора  : ' . $amoInt->number_of_contract,
        '----------------------',
        ' ИНН  : ' . $amoInt->INN,
        '----------------------',
        ' Стоимость квартиры по договору : ' . $amoInt->price_of_apartment,
        '----------------------',
        ' Платежное поручение : ' . $amoInt->payment_order,
        '----------------------',
    ];
    $textNote = implode("\n", $note);
    $amoInt->addNote($currentContact, $textNote);

    if ($currentCompanyOnContact) {

        $inn_currentCompany = $currentCompanyOnContact->cf('ИНН')->getValue();
        $id_currentCompany = $currentCompanyOnContact->id;
        $newInn = $amoInt->INN;
        $company = $currentCompanyOnContact;

    } else {

        // поиск компании по полю ИНН
        $company = $amoInt->globalSearchCompanies();

    }

    for ($leadListIndex = 0; $leadListIndex < count($leadList); $leadListIndex++) {
        $newLead = $amoInt->createLeadInContact($currentContact, $leadList[$leadListIndex]);

//        $task = $amoInt->addTask(
//            $newLead,
//            $task_time,
//            null,
//            null,
//            $text = 'Клиент заполнил форму сверки'
//        );

        if ($company) {

            $amoInt->attachCompany($newLead, $company);
            $amoInt->attachCompany($currentContact, $company);
            $newLead->attachTags(['Есть компания']);
            $newLead->save();

        } else {

            $task = $amoInt->addTask(
                $newLead,
                $task_time,
                $resp_id,
                $type_task,
                $text = 'Компанию с таким инн не удалось найти. Проверь ИНН, добавь его в соответствующую компанию
         и привяжи к данной сделке, а после закрой сделку в успешно'
            );
        }
    }
}

