<?php

require_once ('require.php');

$amoInt = new IntegrationDemoForm(null);
$DB = new ConnectMySQL(null);

var_dump($amoInt->amoCRM->account->currentUser);
