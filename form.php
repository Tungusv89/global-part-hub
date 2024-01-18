<?php
include_once "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

if (!$_POST) {
    // header('Location: '.SITE_URL);
}?>
<pre><?php //var_dump($_POST); ?></pre>
<?php
# собираем данные из формы
$phone = isset($_POST["phone"]) ? htmlentities(trim(preg_replace("/[^,.0-9]/", '', $_POST["phone"]))) : '';
$name = isset($_POST["name"]) ? htmlentities(trim($_POST["name"])) : '';
$messanger = isset($_POST["messanger"]) ? htmlentities(trim($_POST["messanger"])) : '';
$messange = isset($_POST["messange"]) ? htmlentities(trim($_POST["messange"])) : '';
$number_part = isset($_POST["number_part"]) ? htmlentities($_POST["number_part"]) : '';
$error_message = '';

// if (isset($_POST['phone']) && ((int) mb_strlen($phone) < 6 || (int) mb_strlen($phone) > 60)) {
//     $error_message = 'phone';
// }

if (isset($_POST['email']) && ((int) mb_strlen($email) < 6 || (int) mb_strlen($email) > 60) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = 'email';
}

if (isset($_POST['name']) && ((int) mb_strlen($name) < 3 || (int) mb_strlen($name) > 60)) {
    $error_message = 'name';
}

if (isset($_POST['message']) && empty($message)) {
    $error_message = 'message';
}

if (!isset($_POST['phone']) && !isset($_POST['email'])) {
    $error_message = 'phone_email';
}

if ($error_message) {
    $result = json_encode(['status' => 'error', 'error' => $error_message]);
    echo $result;
    die();
}

// Формирование самого письма
$title = "Заявка с сайта";
$body = "
<h2>Новое письмо</h2>
<b>Имя:</b> $name<br>
<b>Телефон:</b> $phone<br>
";

if ($messanger) {
    $body = $body . "<b>Предпочитаемый способ связи:</b> $messanger <br>";
}

if ($number_part) {
    $body = $body . "<b>Название детали:</b> $number_part";
}

// Настройки PHPMailer
$mail = new PHPMailer();
try {
    $mail->isSMTP();
    $mail->CharSet = "UTF-8";
    $mail->SMTPAuth   = true;
    //$mail->SMTPDebug = 2;
    $mail->Debugoutput = function ($str, $level) {
        $GLOBALS['status'][] = $str;
    };

    // Настройки вашей почты
    $mail->Host       = 'smtp.beget.com'; // SMTP сервера вашей почты
    $mail->Username   = 'info@globalparthub.ru'; // Логин на почте
    $mail->Password   = 'lM&gu5up'; // Пароль на почте
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->setFrom('info@globalparthub.ru', 'Заявка с сайта'); // Адрес самой почты и имя отправителя

    // Получатель письма
    $mail->addAddress('sales@globalparthub.ru');

    // Отправка сообщения
    $mail->isHTML(true);
    $mail->Subject = $title;
    $mail->Body = $body;

    // Проверяем отравленность сообщения
    if ($mail->send()) {
        $result = "success";
    } else {
        $result = "error";
        echo "Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}";
    }
} catch (Exception $e) {
    $result = "error";
    $status = "Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}";
}

// Отображение результата
// echo json_encode(["result" => $result]);

// header('Location: http://globalparthub.ru/');


class Bitrix
{
    const API_KEY = BITRIX_API_KEY;
    const API_ID = BITRIX_API_ID;
    const URL_BX_PORTAL = BITRIX_URL;
    const CATEGORY = BITRIX_CATEGORY_GPH;


    private $phone;
    private $name;
    private $messanger;
    private $number_part;

    public function __construct($phone, $number_part, $name, $messanger)
    {
        $this->phone = $phone;
        $this->number_part = $number_part;
        $this->name = $name;
        $this->messanger = $messanger;
    }

    public function getContactId()
    {
        $contact_id = 0;

        $result_select_by_phone = $this->sendBitrix('crm.contact.list', array('filter' => array("PHONE" => $this->phone), 'select' => array('ID')));
        if ($result_select_by_phone['total'] > 0) {
            $contact_id = $result_select_by_phone['result'][0]['ID'];
        } else {
            $result_add_contact = $this->addNewContact();
            $contact_id = $result_add_contact['result'];
        }

        return $contact_id;
    }

    private function addNewContact()
    {
        $send_bitrix24_contact['fields'] = [
            'NAME' => !empty($this->name) ? $this->name : $this->phone,
            'LAST_NAME' => '',
            'ADDRESS' => '',
            'ADDRESS_POSTAL_CODE' => '',
            "CATEGORY_ID" => self::CATEGORY,
            'PHONE' => [
                [
                    'VALUE' => $this->phone,
                    'VALUE_TYPE' => "MOBILE",
                ]
            ],
            'COMMENTS' => [
                [
                    'VALUE' => $this->number_part
                ]
            ]
        ];

        $result = $this->sendBitrix('crm.contact.add', $send_bitrix24_contact);
        return $result;
    }

    private function sendBitrix($method, $data = array())
    {

        $url_param = http_build_query($data);

        $full_url = 'https://' . self::URL_BX_PORTAL . '/rest/' . self::API_ID . '/' . self::API_KEY . '/' . $method . '.json?' . $url_param;

        $file_content = @file_get_contents($full_url);

        if ($file_content === false) {
            http_response_code(403);
            $error = json_encode(['status' => 'error']);
            echo $error;

            exit;
            die();
        }

        $res = json_decode($file_content, 1);

        return $res;
    }

    public function addFormDeal()
    {
        $params = array(
            'fields' => array(
                "CATEGORY_ID" => self::CATEGORY,
                "TITLE" => 'Новая заявка с сайта  локалка' . SITE_NAME,
                "COMMENTS" => 'Заявка с сайта ' . SITE_NAME,
                "NAME" => $this->name,
                "PHONE" => array(
                    array(
                        'VALUE' => $this->phone,
                        'TYPE_ID' => "PHONE",
                    )
                ),
                "EMAIL" => array(
                    array(
                        'VALUE' => $this->number_part,
                        'TYPE_ID' => "EMAIL",
                    )
                ),
                "CONTACT_IDS" => array($this->getContactId()),
                "ORIGINATOR_ID" => "WEB",
                "SOURCE_ID" => "WEB",
            ),
        );

        $result_lead_add = $this->sendBitrix('crm.deal.add', $params);

        http_response_code(200);

        $success = json_encode(['status' => 'success']);
        echo $success;
        die();
    }
}
// echo $phone;
// echo $number_part;
// echo $name;
// echo $messanger;
(new Bitrix($phone, $number_part, $name, $messanger))->addFormDeal();

header('Location: http://globalparthub.ru/');