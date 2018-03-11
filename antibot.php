<?php
require_once 'telegram_class.php';

$tg = new TelegramBot;
$tg->update();
error_reporting(0);

$tg->setToken('Bot token');
$selfID = 'Id bot';

function updates($update)
{
    global $tg;
    global $selfID;
    $msg = isset($update["message"]["text"]) ? $update["message"]["text"] : '';
    $chatID = $update['message']['chat']['id'];
    $userID = $update['message']['from']['id'];
    $msgID = $update["message"]["message_id"];
    $parse_mode = 'HTML';
    
    if ($chatID < 0) {
        if (isset($update["message"]["new_chat_member"]) and isset($update['message']['new_chat_members']) and $update["message"]["new_chat_member"]['is_bot']) {
            $s       = $update['message']['message_id'];
            $idadder = $update['message']['from']['id'];
            $tg->kickChatMember($chatID, $idadder);
            foreach ($update['message']['new_chat_members'] as $bot) {
                $botBannati = $bot['id'];
                $tg->kickChatMember($chatID, $botBannati);
                //Ricordo che i bot non possono deletare messaggi di altri bot se non via reply. Se si vuole aggiungere quella funzione metterla al posto di questa stringa commentata.
            }
            
            $re = $tg->sendMessage($chatID, "Messaggio di servizio:<i>\nRimozione tastiera e pulizia aggiunte in corso.</i>", $parse_mode);
            foreach (range($s, ($re['result']['message_id'])) as $robo2) {
                $tg->deleteMessage($chatID, $robo2);
            }
        }
        
        $domini = array(".it", ".com", ".fr", ".eu", ".fr", ".me", ".net", ".top", ".ovh", ".info");
        $cosetg = array("t.me", "telegram.me", "telegram.dog");
        
        if (isset($update['message'])) {
            foreach ($cosetg as $isspam) {
                if (stripos($msg, $isspam)) {
                    $tg->deleteMessage($chatID, $msgID);
                }
            }
        }
        
        if (isset($update['message'])) {
            foreach ($domini as $isspam) {
                if (stripos($msg, $isspam)) {
                    $tg->deleteMessage($chatID, $msgID);
                }
            }
        }
    }
}

$tg->startThreads('updates', 20);

while (true) {
    sleep(10);
}