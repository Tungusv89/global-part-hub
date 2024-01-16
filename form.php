<?php
include_once "config.php";
require 'PHPMailer/PHPMailer';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

if(!$_POST){
    // header('Location: '.SITE_URL);
}

# собираем данные из формы
$phone = isset($_POST["phone"]) ? trim(preg_replace("/[^,.0-9]/", '', $_POST["phone"])) : '';
$name = isset($_POST["name"]) ? trim($_POST["name"]) : '';
$email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
$messager = isset($_POST["messanger"]) ? trim($_POST["messanger"]) : '';
$message = isset($_POST["message"]) ? trim($_POST["message"]) : '';

$error_message = '';

if (isset($_POST['phone']) && ((int) mb_strlen($phone) < 6 || (int) mb_strlen($phone) > 60)) {
    $error_message = 'phone';
}

if (isset($_POST['email']) && ((int) mb_strlen($email) < 6 || (int) mb_strlen($email) > 60) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = 'email';
}

if (isset($_POST['name']) && ((int) mb_strlen($name) < 3 || (int) mb_strlen($name) > 60)) {
    $error_message = 'name';
}

if (isset($_POST['message']) && empty($message)) {
    $error_message = 'message';
}

if (!isset($_POST['phone']) && !isset($_POST['email'])){
    $error_message = 'phone_email';
}

if($error_message){
    $result = json_encode(['status' => 'error', 'error' => $error_message]);
    echo $result;
    die();
}

// Формирование самого письма
$title = "Заголовок письма";
$body = "
<h2>Новое письмо</h2>
<b>Имя:</b> $name<br>
<b>Почта:</b> $email<br><br>
<b>Сообщение:</b><br>$text
";

// Настройки PHPMailer
$mail = new PHPMailer\PHPMailer\PHPMailer();
try {
    $mail->isSMTP();
    $mail->CharSet = "UTF-8";
    $mail->SMTPAuth   = true;
    //$mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {$GLOBALS['status'][] = $str;};

    // Настройки вашей почты
    $mail->Host       = 'smtp.beget.com'; // SMTP сервера вашей почты
    $mail->Username   = 'taz66moe'; // Логин на почте
    $mail->Password   = '%7kfFmfFseW3487##ns1239'; // Пароль на почте
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 25;
    $mail->setFrom('info@globalparthub.ru', 'Имя отправителя'); // Адрес самой почты и имя отправителя

    // Получатель письма
    $mail->addAddress('sales@globalparthub.ru');

// Отправка сообщения
$mail->isHTML(true);
$mail->Subject = $title;
$mail->Body = $body;

// Проверяем отравленность сообщения
if ($mail->send()) {$result = "success";}
else {$result = "error";}

} catch (Exception $e) {
    $result = "error";
    $status = "Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}";
}

// Отображение результата
echo json_encode(["result" => $result, "resultfile" => $rfile, "status" => $status]);




// class Bitrix
// {
//     const API_KEY = BITRIX_API_KEY;
//     const API_ID = BITRIX_API_ID;
//     const URL_BX_PORTAL = BITRIX_URL;

//     private $email;
//     private $phone;
//     private $name;
//     private $message;

//     public function __construct($phone, $email, $name, $message)
//     {
//         $this->phone = $phone;
//         $this->email = $email;
//         $this->name = $name;
//         $this->message = $message;
//     }

//     public function getContactId()
//     {
//         $contact_id = 0;

//         $result_select_by_phone = $this->sendBitrix('crm.contact.list', array('filter' => array("PHONE" => $this->phone), 'select' => array('ID')));
//         if ($result_select_by_phone['total'] > 0) {
//             $contact_id = $result_select_by_phone['result'][0]['ID'];
//         } else {
//             $result_add_contact = $this->addNewContact();
//             $contact_id = $result_add_contact['result'];
//         }

//         return $contact_id;
//     }

//     private function addNewContact()
//     {
//         $send_bitrix24_contact['fields'] = [
//             'NAME' => !empty($this->name) ? $this->name : $this->phone,
//             'LAST_NAME' => '',
//             'ADDRESS' => '',
//             'ADDRESS_POSTAL_CODE' => '',
//             'PHONE' => [
//                 [
//                     'VALUE' => $this->phone,
//                     'VALUE_TYPE' => "MOBILE",
//                 ]
//             ],
//             'EMAIL' => [
//                 [
//                     'VALUE' => $this->email,
//                     'TYPE_ID' => "EMAIL",
//                 ]
//             ]
//         ];

//         $result = $this->sendBitrix('crm.contact.add', $send_bitrix24_contact);
//         return $result;
//     }

//     private function sendBitrix($method, $data = array())
//     {

//         $url_param = http_build_query($data);

//         $full_url = 'https://' . self::URL_BX_PORTAL . '/rest/' . self::API_ID . '/' . self::API_KEY . '/' . $method . '.json?' . $url_param;

//         $file_content = @file_get_contents($full_url);

//         if ($file_content === false) {
//             http_response_code(403);
//             $error = json_encode(['status' => 'error']);
//             echo $error;

//             exit;
//             die();
//         }

//         $res = json_decode($file_content, 1);

//         return $res;
//     }

//     public function addFormDeal()
//     {
//         $params = array(
//             'fields' => array(
//                 "TITLE" => 'Новая заявка с сайта ' . SITE_NAME,
//                 "COMMENTS" => 'Заявка с сайта ' . SITE_NAME,
//                 "NAME" => $this->name,
//                 "PHONE" => array(
//                     array(
//                         'VALUE' => $this->phone,
//                         'TYPE_ID' => "PHONE",
//                     )
//                 ),
//                 "EMAIL" => array(
//                     array(
//                         'VALUE' => $this->email,
//                         'TYPE_ID' => "EMAIL",
//                     )
//                 ),
//                 "CONTACT_IDS" => array($this->getContactId()),
//                 "ORIGINATOR_ID" => "WEB",
//                 "SOURCE_ID" => "WEB",
//             ),
//         );

//         $result_lead_add = $this->sendBitrix('crm.deal.add', $params);

//         http_response_code(200);

//         $success = json_encode(['status' => 'success']);
//         echo $success;
//         die();
//     }
// }

// (new Bitrix($phone, $email, $name, $message))->addFormDeal();