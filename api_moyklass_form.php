<style>
    .form0 {
        margin: 20px;
    }
    .message {
        margin: 20px;
    }
    .form2 {
        margin: 20px;
    }

</style>

<?php
session_start();

/* Template Name: API-Template */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Smart Form (Moyklass Api) Template
 * @file           api_moyklass_form.php
 * @package        ROBX
 * @author         Dmitriy Spivak
 * @copyright      2020 ROBX
 * @version        Release: 0.1
 * @page https://robx.org/api-test/
 */

function get_token($api_key, $url_auth)
{
    $post_auth = array(
        'apiKey' => $api_key
    );
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_URL, $url_auth);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_auth));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $out = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($out, true);
    return $response['accessToken'];
}
function api_get($url, $access_token)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "x-access-token:" . $access_token,
        "Content-Type: application/json"
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $out = curl_exec($curl);
    curl_close($curl);
    $out = json_decode($out, true);
    return $out;
}
function api_post($data, $url, $access_token)
{
    $data = json_encode($data);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "x-access-token:" . $access_token,
        "Content-Type: application/json"
    ));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $out = curl_exec($curl);
    curl_close($curl);
    return $out;
}
function del_token($access_token)
{
    $url = 'https://api.moyklass.com/v1/company/auth/revokeToken';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "x-access-token:" . $access_token,
        "Content-Type: application/json"
    ));
    curl_close($curl);
}

get_header();

if (!isset($_SESSION['counter']) ) {
    include 'api_moyklass_repo/register1_form.php';
    $_SESSION['counter'] = 1;
}

elseif ($_SESSION['counter'] == 1) {
    # connect and get required information
    $api_key = ''; # api key
    $url_auth = 'https://api.moyklass.com/v1/company/auth/getToken';
    $access_token = get_token($api_key, $url_auth);
    $students = api_get('https://api.moyklass.com/v1/company/users', $access_token); # get students array
    $lessons = api_get('https://api.moyklass.com/v1/company/lessons', $access_token); # get lessons array
    $managers = api_get('https://api.moyklass.com/v1/company/managers', $access_token); #get managers array


    #record user info
    $_SESSION['uname'] = $_POST["uname"];
    $_SESSION['uphone'] = $_POST["uphone"];
    $_SESSION['token'] = $access_token;

    # calculate
    $userinfo = [];
    $counter = 0;
    $out = '';
    foreach ($students as $student) {
        foreach ($student as $set) {
            if ($set['phone'] == $_SESSION['uphone'] and $counter == 0) {
                $userinfo['userId'] = $set['id'];
                $userinfo['body'] = '(；⌣̀_⌣́) [ROBXBOT]' . ' Позвоните ' . $set['name'] . '. Он(а) повторно оставил(а) заявку на сайте';
                $userinfo['beginDate'] = date('c');
                $userinfo['endDate'] = date('c');
                $userinfo['isAllDay'] = true;
                $userinfo['filialId'] = $set['filials'][0];
                $userinfo['managerId'] = $managers[0]['id'];
                $counter += 1;
                $response = api_post($userinfo, 'https://api.moyklass.com/v1/company/tasks', $_SESSION['token']);
                break;

            }
        }
    }

    //if student was not found in CRM
    if ($counter == 0) {
        $userinfo['name'] = $_SESSION['uname'];
        $userinfo['phone'] = $_SESSION['uphone'];
        $new_student = api_post($userinfo, 'https://api.moyklass.com/v1/company/users', $_SESSION['token']);
        $_SESSION['uId'] = $new_student['id'];
        $_SESSION['counter'] = 2;
        include 'api_moyklass_repo/register2_form.php';

    }

    //if student was found in CRM
    if ($counter == 1)
    {
        include 'api_moyklass_repo/reply1_form.php';
        session_destroy();
        del_token($access_token);
    }

}

// if first two forms are set
elseif ($_SESSION['counter'] == 2)
{
    $_SESSION['uage']  = $_POST["uage"];
    $_SESSION['ubranch']  = $_POST["ubranch"];
    $_SESSION['counter'] = 3;
    include 'api_moyklass_repo/register3_form.php';
}

elseif ($_SESSION['counter'] == 3)
{
    $_SESSION['lesson'] = $_POST["uevent"];
    $enlist = [];
    $enlist['lessonId'] = $_SESSION['lesson'];
    $enlist['free'] = true;
    $enlist['userId'] = $_SESSION['uId'];
    $enlist_response = api_post($enlist, 'https://api.moyklass.com/v1/company/lessonRecords', $_SESSION['token']);

    include 'api_moyklass_repo/reply2_form.php';
    del_token($_SESSION['token']);
    session_destroy();
}

get_footer();
?>
