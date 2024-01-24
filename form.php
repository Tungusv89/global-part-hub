<?php
include_once "config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

if (!$_POST) {
    // header('Location: '.SITE_URL);
};
?>
<pre><?php //var_dump($_GET) ?></pre>
<?php
# собираем данные из формы
$phone = isset($_POST["phone"]) ? htmlentities(trim(preg_replace("/[^,.0-9]/", '', $_POST["phone"]))) : '';
$name = isset($_POST["name"]) ? htmlentities($_POST["name"]) : '';
$messanger = isset($_POST["messanger"]) ? htmlentities(trim($_POST["messanger"])) : '';
$messange = isset($_POST["messange"]) ? htmlentities(trim($_POST["messange"])) : '';
$number_part = isset($_POST["number_part"]) ? htmlentities($_POST["number_part"]) : '';
$utm_source = isset($_GET['utm_source']) ? $_GET['utm_source'] : '';
$utm_campaign = isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : '';
$utm_medium = isset($_GET['utm_medium']) ? $_GET['utm_medium'] : '';
$utm_content = isset($_GET['utm_content']) ? $_GET['utm_content'] : '';
$utm_term = isset($_GET['utm_term']) ? $_GET['utm_term'] : '';
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




class Bitrix
{
    const API_KEY = BITRIX_API_KEY;
    const API_ID = BITRIX_API_ID;
    const URL_BX_PORTAL = BITRIX_URL;
    const CATEGORY = BITRIX_CATEGORY_GPH;
    const ASSIGNED_BY_ID = ASSIGNED_BY_ID;


    private $phone;
    private $name;
    private $messanger;
    private $number_part;
    private $category_id = BITRIX_CATEGORY_GPH;
    private $assigned_by = ASSIGNED_BY_ID;
    private $utm_source;
    private $utm_campaign;
    private $utm_medium;
    private $utm_content;
    private $utm_term;

    public function __construct($phone, $number_part, $name, $messanger,$utm_source, $utm_campaign, $utm_medium, $utm_content, $utm_term)
    {
        $this->phone = $phone;
        $this->number_part = $number_part;
        $this->name = $name;
        $this->messanger = $messanger;
        $this->utm_source = $utm_source;
        $this->utm_campaign = $utm_campaign;
        $this->utm_medium = $utm_medium;
        $this->utm_content = $utm_content;
        $this->utm_term = $utm_term;
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
            "ASSIGNED_BY_ID" => $this->assigned_by,
            'NAME' => !empty($this->name) ? $this->name : $this->phone,
            'LAST_NAME' => '',
            'ADDRESS' => '',
            'ADDRESS_POSTAL_CODE' => '',
            'PHONE' => [
                [
                    'VALUE' => $this->phone,
                    'VALUE_TYPE' => "MOBILE",
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
                "CATEGORY_ID" => $this->category_id,
                "ASSIGNED_BY_ID" => $this->assigned_by,
                "TITLE" => 'Новая заявка с сайта' . SITE_NAME,
                "COMMENTS" => $this->number_part,
                "NAME" => $this->name,
                "PHONE" => array(
                    array(
                        'VALUE' => $this->phone,
                        'TYPE_ID' => "PHONE",
                    )
                ),
                "CONTACT_IDS" => array($this->getContactId()),
                "ORIGINATOR_ID" => "WEB",
                "SOURCE_ID" => "WEB",
                "UTM_CAMPAIGN" => $this->utm_campaign,
                "UTM_CONTENT" => $this->utm_content,
                "UTM_MEDIUM" => $this->utm_medium,
                "UTM_SOURCE" => $this->utm_source,
                "UTM_TERM" => $this->utm_term
            ),
        );

        $result_lead_add = $this->sendBitrix('crm.deal.add', $params);

        http_response_code(200);

        $success = json_encode(['status' => 'success']);
        echo $success;
        die();
    }
}

(new Bitrix($phone, $number_part, $name, $messanger, $utm_source, $utm_campaign, $utm_medium, $utm_content, $utm_term))->addFormDeal();
