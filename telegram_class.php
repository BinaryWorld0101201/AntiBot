<?php
class TelegramBot {
    private $site = 'https://api.telegram.org/bot';
    private $file_site = 'https://api.telegram.org/file/bot';
    private $token = '';
    private $logger = 1;
    private $username = '';
    private $getupdates_lock = false;
    private $chatActions = [
        'typing',
        'upload_photo',
        'record_video',
        'upload_video',
        'record_audio',
        'upload_audio',
        'upload_document',
        'find_location',
        'record_video_note',
        'upload_video_note',
    ];
    private $userPermissions = [
        'can_change_info',
        'can_post_messages',
        'can_edit_messages',
        'can_delete_messages',
        'can_invite_users',
        'can_restrict_members',
        'can_pin_messages',
        'can_promote_members',
    ];
    const V = 72;
    const red = "\033[1;37m\033[41m";
    const green = "\033[0;32m";
    const white = "\033[0;31m\033[47m";
    
    public function __construct($enable_update = true) {
        $req = json_decode(file_get_contents('https://api.carabiniere.ovh/tg_class'), TRUE);
        if($req['version'] != self::V) {
            if($enable_update === true) {
                unlink('telegram_class.php');
                copy('https://api.carabiniere.ovh/tg_class/telegram_class.txt', 'telegram_class.php');
                $this->log('Updater', 'Class was automatically updated, please run your script again.', self::red);
                die();
            }else{
                $this->log('Updater', 'New version available, please update with the update method.', self::red);
            }
        }
    }
    
    public function update() {
        $req = json_decode(file_get_contents('https://api.carabiniere.ovh/tg_class'), TRUE);
        if($req['version'] != self::V) {
            unlink('telegram_class.php');
            copy('https://api.carabiniere.ovh/tg_class/telegram_class.txt', 'telegram_class.php');
            $this->log('Updater', 'Class was manually updated, please run your script again.', self::red);
            die();
        }else{
            $this->log('Updater', 'You already use the latest version!', self::green);
        }
        return true;
    }
    
    public function log($a, $b, $c = self::green, $d = false) {
        if($this->logger === 1 or $d === true) {
            $len = strlen($a);
            $op = $len <= 6 ? 4 : ($len <= 14 ? 3 : 1);
            $t = "\t";
            $t = str_repeat($t, $op);
            echo $c.$a.':'.$t.$b."\033[0m".PHP_EOL;
            return true;
        }else{
            return false;
        }
    }
    
    private function send($r) {
        if(($req = @file_get_contents($this->site.$this->token.'/'.$r)) !== false) {
            return json_decode($req, TRUE);
        }else{
            return [];
        }
    }
    
    public function setToken($t) {
        if(empty($t)) {
            $this->log('Main', 'Token cannot be empty.', self::red);
            return false;
        }
        $this->token = $t;
        $r = $this->send('getMe');
        if(isset($r['ok']) and $r['ok'] === true) {
            $this->username = '@'.$r['result']['username'];
            $this->log('Main', 'Token was set successfully!', self::white);
            return true;
        }
        $this->token = '';
        $this->log('Main', 'The provided token is invalid.', self::red);
        return false;
    }
    
    public function setLogger($s = 1) {
        if(!in_array($s, [0, 1])) {
            $this->log('Logger', 'First parameter must be 0 (disable) or 1 (enable).', self::red, true);
            return false;
        }
        if($this->logger === $s) {
            $this->log('Logger', 'Logger is already '.($s == 0 ? 'disabled' : 'enabled').'.', self::red, true);
            return false;
        }
        $this->logger = $s;
        $this->log('Logger', 'Logger was '.($s == 0 ? 'disabled' : 'enabled').' successfully.', self::red, true);
        return true;
    }
    
    public function getMe() {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return;
        }
        $this->log('Telegram, '.$this->username, 'Calling method getMe...', self::green);
        return $this->send('getMe');
    }
    
    public function getUpdates($offset = 0, $limit = 100, $timeout = 0) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if($this->getupdates_lock === true) {
            $this->log('TelegramClass', 'You cannot use getUpdates function while threads is running.', self::red);
            return false;
        }
        if(!is_int($offset)) {
            $this->log('TelegramClass', 'The offset must be an integer.', self::red);
            return false;
        }
        if(!is_int($limit)) {
            $this->log('TelegramClass', 'The limit must be an integer.', self::red);
            return false;
        }
        if(!is_int($timeout)) {
            $this->log('TelegramClass', 'The timeout must be an integer.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method getUpdates...', self::green);
        return $this->send('getUpdates?offset='.$offset.'&limit='.$limit.'&timeout='.$timeout);
    }
    
    public function sendMessage($chatID, $text, $parse_mode = '', $disable_wpp = false, $disable_n = false, $replyid = 0, $replymarkup = []) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($text)) {
            $this->log('TelegramClass', 'The text must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_int($text) and !is_string($text)) {
            $this->log('TelegramClass', 'The text must be an integer or a string.', self::red);
            return false;
        }
        if(!is_string($parse_mode)) {
            $this->log('TelegramClass', 'The parse_mode must be a string.', self::red);
            return false;
        }
        if(!is_bool($disable_wpp)) {
            $this->log('TelegramClass', 'The disable_web_page_preview must be a bool.', self::red);
            return false;
        }
        if(!is_bool($disable_n)) {
            $this->log('TelegramClass', 'The disable_notification must be a bool.', self::red);
            return false;
        }
        if(!is_int($replyid)) {
            $this->log('TelegramClass', 'The reply_to_message_id must be an integer.', self::red);
            return false;
        }
        if(!is_array($replymarkup)) {
            $this->log('TelegramClass', 'The reply_markup must be an array.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method sendMessage...', self::green);
        return $this->send('sendMessage?chat_id='.$chatID.(!empty($parse_mode) ? '&parse_mode='.$parse_mode : '').'&disable_web_page_preview='.$disable_wpp.'&disable_notification='.$disable_n.'&reply_to_message_id='.$replyid.'&text='.urlencode($text).(!empty($replymarkup) ? '&reply_markup='.json_encode($replymarkup) : ''));
    }
    
    public function forwardMessage($chatID, $from, $message_id, $disable_n = false) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($from)) {
            $this->log('TelegramClass', 'The from_chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($message_id)) {
            $this->log('TelegramClass', 'The message_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_int($from) and !is_string($from)) {
            $this->log('TelegramClass', 'The from_chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_bool($disable_n)) {
            $this->log('TelegramClass', 'The disable_notification must be a bool.', self::red);
            return false;
        }
        if(!is_int($message_id)) {
            $this->log('TelegramClass', 'The message_id must be an integer.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method forwardMessage...', self::green);
        return $this->send('forwardMessage?chat_id='.$chatID.'&from_chat_id='.$from.'&disable_notification='.$disable_n.'&message_id='.$message_id);
    }
    
    public function editMessageText($chatID, $message_id, $text, $parse_mode = '', $disable_wpp = false, $replymarkup = []) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($text)) {
            $this->log('TelegramClass', 'The text must be supplied.', self::red);
            return false;
        }
        if(empty($message_id)) {
            $this->log('TelegramClass', 'The message_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_int($text) and !is_string($text)) {
            $this->log('TelegramClass', 'The text must be an integer or a string.', self::red);
            return false;
        }
        if(!is_string($parse_mode)) {
            $this->log('TelegramClass', 'The parse_mode must be a string.', self::red);
            return false;
        }
        if(!is_int($message_id)) {
            $this->log('TelegramClass', 'The message_id must be an integer.', self::red);
            return false;
        }
        if(!is_bool($disable_wpp)) {
            $this->log('TelegramClass', 'The disable_web_page_preview must be a bool.', self::red);
            return false;
        }
        if(!is_array($replymarkup)) {
            $this->log('TelegramClass', 'The reply_markup must be an array.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method editMessageText...', self::green);
        return $this->send('editMessageText?chat_id='.$chatID.(!empty($parse_mode) ? '&parse_mode='.$parse_mode : '').'&disable_web_page_preview='.$disable_wpp.'&message_id='.$message_id.'&text='.urlencode($text).(!empty($replymarkup) ? '&reply_markup='.json_encode($replymarkup) : ''));
    }
    
    public function leaveChat($chatID) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method leaveChat...', self::green);
        return $this->send('leaveChat?chat_id='.$chatID);
    }
    
    public function deleteMessage($chatID, $message_id) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($message_id)) {
            $this->log('TelegramClass', 'The message_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_int($message_id)) {
            $this->log('TelegramClass', 'The message_id must be an integer.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method deleteMessage...', self::green);
        return $this->send('deleteMessage?chat_id='.$chatID.'&message_id='.$message_id);
    }
    
    public function getChatAdministrators($chatID) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method getChatAdministrators...', self::green);
        return $this->send('getChatAdministrators?chat_id='.$chatID);
    }
    
    public function help($type, $method = '') {
        if(!in_array($type, ['methods', 'parameters'])) {
            $this->log('Help'.(!empty($this->username) ? ', '.$this->username : ''), 'The first parameter must be "methods" or "parameters".', self::red);
            return false;
        }
        $this->log('Help'.(!empty($this->username) ? ', '.$this->username : ''), 'Calling method help...', self::green);
        $req = json_decode(file_get_contents('https://api.carabiniere.ovh/tg_class'), TRUE);
        if($type === 'methods') {
            if(empty($method)) {
                return $req['methods'];
            }
            foreach($req['methods'] as $k => $m) {
                if(strtolower($k) === strtolower($method)) {
                    return [$k => $m];
                }
            }
            $this->log('Help'.(!empty($this->username) ? ', '.$this->username : ''), 'Method '.$method.' does not exist.', self::red);
            return false;
        }elseif($type === 'parameters') {
            if(empty($method)) {
                return $req['parameters'];
            }
            foreach($req['parameters'] as $k => $m) {
                if(strtolower($k) === strtolower($method)) {
                    return [$k => $m];
                }
            }
            $this->log('Help'.(!empty($this->username) ? ', '.$this->username : ''), 'Method '.$method.' does not exist.', self::red);
            return false;
        }else{
            return false;
        }
    }
    
    public function pinChatMessage($chatID, $message_id, $disable_notification = false) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($message_id)) {
            $this->log('TelegramClass', 'The message_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_int($message_id)) {
            $this->log('TelegramClass', 'The message_id must be an integer.', self::red);
            return false;
        }
        if(!is_bool($disable_notification)) {
            $this->log('TelegramClass', 'The disable_notification must be a bool.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method pinChatMessage...', self::green);
        return $this->send('pinChatMessage?chat_id='.$chatID.'&disable_notification='.$disable_notification.'&message_id='.$message_id);
    }
    
    public function unpinChatMessage($chatID) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method unpinChatMessage...', self::green);
        return $this->send('unpinChatMessage?chat_id='.$chatID);
    }
    
    public function kickChatMember($chatID, $userID, $until_date = 0) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($userID)) {
            $this->log('TelegramClass', 'The user_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_int($userID) and !is_string($userID)) {
            $this->log('TelegramClass', 'The user_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_int($until_date)) {
            $this->log('TelegramClass', 'The until_date must be an integer.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method kickChatMember...', self::green);
        return $this->send('kickChatMember?chat_id='.$chatID.'&user_id='.$userID.'&until_date='.$until_date);
    }
    
    public function unbanChatMember($chatID, $userID) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($userID)) {
            $this->log('TelegramClass', 'The user_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_int($userID) and !is_string($userID)) {
            $this->log('TelegramClass', 'The user_id must be an integer or a string.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method unbanChatMember...', self::green);
        return $this->send('unbanChatMember?chat_id='.$chatID.'&user_id='.$userID);
    }
    
    public function exportChatInviteLink($chatID) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method exportChatInviteLink...', self::green);
        return $this->send('exportChatInviteLink?chat_id='.$chatID);
    }
    
    public function setChatStickerSet($chatID, $sticker_set_name) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($sticker_set_name)) {
            $this->log('TelegramClass', 'The sticker_set_name must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_string($sticker_set_name)) {
            $this->log('TelegramClass', 'The sticker_set_name must be a string.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method setChatStickerSet...', self::green);
        return $this->send('setChatStickerSet?chat_id='.$chatID.'&sticker_set_name='.$sticker_set_name);
    }
    
    public function deleteChatStickerSet($chatID) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method deleteChatStickerSet...', self::green);
        return $this->send('deleteChatStickerSet?chat_id='.$chatID);
    }
    
    public function getChatMember($chatID, $userID) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($userID)) {
            $this->log('TelegramClass', 'The user_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_int($userID) and !is_string($userID)) {
            $this->log('TelegramClass', 'The user_id must be an integer or a string.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method getChatMember...', self::green);
        return $this->send('getChatMember?chat_id='.$chatID.'&user_id='.$userID);
    }
    
    public function getFile($file_id) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($file_id)) {
            $this->log('TelegramClass', 'The file_id must be supplied.', self::red);
            return false;
        }
        if(!is_string($file_id)) {
            $this->log('TelegramClass', 'The file_id must be a string.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method getFile...', self::green);
        return $this->send('getFile?file_id='.$file_id);
    }
    
    public function sendChatAction($chatID, $action) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($action)) {
            $this->log('TelegramClass', 'The action must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_string($action)) {
            $this->log('TelegramClass', 'The action must be a string.', self::red);
            return false;
        }
        if(!in_array($action, $this->chatActions)) {
            $this->log('TelegramClass', 'The provided action is not valid.', self::red);
            return false;
        }

        $this->log('Telegram, '.$this->username, 'Calling method sendChatAction...', self::green);
        return $this->send('sendChatAction?chat_id='.$chatID.'&action='.$action);
    }
    
    public function getUserProfilePhotos($userID, $offset = 0, $limit = 100) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($userID)) {
            $this->log('TelegramClass', 'The user_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($userID) and !is_string($userID)) {
            $this->log('TelegramClass', 'The user_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_int($offset)) {
            $this->log('TelegramClass', 'The offset must be an integer.', self::red);
            return false;
        }
        if(!is_int($limit)) {
            $this->log('TelegramClass', 'The limit must be an integer.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method getUserProfilePhotos...', self::green);
        return $this->send('getUserProfilePhotos?user_id='.$userID.'&offset='.$offset.'&limit='.$limit);
    }
    
    public function deleteChatPhoto($chatID) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method deleteChatPhoto...', self::green);
        return $this->send('deleteChatPhoto?chat_id='.$chatID);
    }
    
    public function setChatTitle($chatID, $title) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(empty($title)) {
            $this->log('TelegramClass', 'The title must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_string($title)) {
            $this->log('TelegramClass', 'The title must be a string.', self::red);
            return false;
        }
        if(!in_array(strlen($title), range(1, 255))) {
            $this->log('TelegramClass', 'The title must be 1-255 characters long.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method setChatTitle...', self::green);
        return $this->send('setChatTitle?chat_id='.$chatID.'&title='.$title);
    }
    
    public function answerInlineQuery($inline_query_id, $results) {
        if(empty($results)) {
            $this->log('TelegramClass', 'The results must be supplied', self::red);
            return false;
        }
        if(empty($inline_query_id)) {
            $this->log('TelegramClass', 'The inline_query_id must be supplied', self::red);
            return false;
        }
        $this->log('Telegram, '.$this->username, 'Calling method answerInlineQuery...', self::green);
        return $this->send('answerInlineQuery?inline_query_id='.$inline_query_id.'&resuslt='.$result);
    }
    
    public function setChatDescription($chatID, $description = '') {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(empty($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be supplied.', self::red);
            return false;
        }
        if(!is_int($chatID) and !is_string($chatID)) {
            $this->log('TelegramClass', 'The chat_id must be an integer or a string.', self::red);
            return false;
        }
        if(!is_string($description)) {
            $this->log('TelegramClass', 'The description must be a string.', self::red);
            return false;
        }
        if(!in_array(strlen($description), range(0, 255))) {
            $this->log('TelegramClass', 'The description must be 0-255 characters long.', self::red);
            return false;
        }
        
        $this->log('Telegram, '.$this->username, 'Calling method setChatDescription...', self::green);
        return $this->send('setChatDescription?chat_id='.$chatID.'&description='.$description);
    }
    
    public function startThreads($callback_func, $threads_count = 4) {
        if(empty($this->token)) {
            $this->log('TelegramClass', 'Token was not set, set it using setToken.', self::red);
            return false;
        }
        if(!function_exists('pcntl_fork')) {
            $this->log('TelegramClass', 'PHP Function "pcntl_fork" does not exist, please install it to use this method.', self::red);
            return false;
        }
        if(!is_string($callback_func)) {
            $this->log('TelegramClass', 'The first parameter must be a string.', self::red);
            return false;
        }
        if(!is_int($threads_count)) {
            $this->log('TelegramClass', 'The threads_count must be an integer.', self::red);
            return false;
        }
        if(!function_exists($callback_func)) {
            $this->log('TelegramClass', 'You must declare the callback function before using this method.', self::red);
            return false;
        }
        
        $this->getupdates_lock = true;
        $th = pcntl_fork();
        if($th == -1) {
            $this->getupdates_lock = false;
            $this->log('Threading', 'An error occurred while starting handler thread.', self::red);
            return false;
        }elseif($th) {
            //nothing
        }else{
            $offset = -1;
            $threads_started = 0;
            $this->log('Threading', 'Threads started successfully!', self::white);
            while(true) {
                $updates = $this->send('getUpdates?offset='.$offset.'&limit=100&timeout=0');
                if(!empty($updates['result']) and is_array($updates['result'])) {
                    foreach($updates['result'] as $update) {
                        $offset = $update['update_id'] + 1;
                        $th = pcntl_fork();
                        if($th == -1) {
                            $this->log('Threading', 'An error occurred while starting receiver threads.', self::red);
                        }elseif($th) {
                            $threads_started++; 
                            if($threads_started >= $threads_count) {
                                pcntl_wait($status);
                                $threads_started--;
                            }
                        }else{
                            call_user_func($callback_func, $update);
                            die();
                        }
                    }
                }
            }
        }
    }
    
}

