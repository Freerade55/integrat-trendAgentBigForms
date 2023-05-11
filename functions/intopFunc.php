<?php


    function getCompanyTaskTypeByCity($company_amo, $type_task_reserve)
    {
        $type_task = $type_task_reserve;

        if (!empty($company_amo) && $company_amo->cf("Локация-компания")) {
            switch ($company_amo->cf("Локация-компания")->getValue()) {
                case "Краснодар":
                case "Краснодарский край":
                    $type_task = 2696332;
                    break;
                case "Москва":
                    $type_task = 2696320;
                    break;
                case "Санкт-Петербург":
                    $type_task = 2696323;
                    break;
                case "Новосибирск":
                    $type_task = 2696326;
                    break;
                case "Ростов-на-Дону":
                    $type_task = 2696329;
                    break;
            }
        }

        return $type_task;
    }