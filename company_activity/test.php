<?php
    
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL & ~E_NOTICE);
    
    //require_once("require.php");

    /*$amoInt = new IntegrationDemoForm(null);
    $company_id = 29959805;
    $company_amo = $amoInt->amoCRM->companies()->find($company_id);
    echo $company_amo->id;
    echo "<br>";
    if ($company_amo->cf("Локация-компания")) {
        print_r($company_amo->cf("Локация-компания")->getValue());
    }
    echo "<br>";
    if ($company_amo->cf("dasdasdasd")) {
        print_r($company_amo->cf("dasdasdasd")->getValue());
    }*/

    /*require_once __DIR__ . '/../classes/integrationDemoForm.php';
    require_once __DIR__ . '/../functions/amoFunc.php';

    const MAX_I = 40;
    $file_fail_name = __DIR__ . "/../logs/demo_form_re.json";
    $postData_array = json_decode(file_get_contents($file_fail_name), true);
    //die(json_encode($postData_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    //echo json_encode($post, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $i = 1;
    foreach ($postData_array as $postKey => $postData) {
        $_POST = $postData;


        if ($currentContact->id) {
            echo "<a href='https://trendagent.amocrm.ru/contacts/detail/{$currentContact->id}/'>https://trendagent.amocrm.ru/contacts/detail/{$currentContact->id}/</a><br>";
        } elseif ($newContact && $newContact->id) {
            echo "<a href='https://trendagent.amocrm.ru/contacts/detail/{$newContact->id}/'>https://trendagent.amocrm.ru/contacts/detail/{$newContact->id}/</a><br>";
        }
        //die(json_encode($postData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        unset($postData_array[$postKey]);
        $i++;
        if ($i > MAX_I) {
            break;
        }
    }

    file_put_contents($file_fail_name, json_encode($postData_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));*/

    //echo json_encode($_SERVER, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    require_once __DIR__ . '/../classes/integrationReconForm.php';
    require_once __DIR__ . '/../functions/amoFunc.php';

    $postData = [
        "Рекламное_название_вашего_агентства_недвижимости" => "ТСН-Ивантеевка",
        "ИНН_организации" => "5038122776",
        "name" => "Правдин Кирилл Маркович",
        "Phone" => "+7 (926) 181-04-57",
        "Email" => "pravdin.k@tsnnedv.ru",
        "Client" => "Никулина Анастасия Евгеньевна",
        "Phone_2" => "+7 (792) 957-05-89",
        "Застройщик_" => "Специализированный застройщик ЖК2/1",
        "Дата_подписания_договора_" => "02-12-2022",
        "Номер_договора" => "Кв-А-196-И",
        "Стоимость_квартиры_по_договору" => "30104790,84",
        "Checkbox" => "yes",
        "tranid" => "160160:4286315551",
        "formid" => "form278847618",
        "formname" => "MSK"
    ];

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

