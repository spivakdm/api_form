<?php
$lessons_get = api_get("https://api.moyklass.com/v1/company/lessons?includeRecords=true", $_SESSION['token']);
$lessons_list = $lessons_get['lessons'];
$today = date('Y-m-d');
#print_r($today);
$good_lessons = [];
foreach ($lessons_list as $lesson)
{
    $ages = explode(", ", $lesson['comment']);
    if ($lesson['filialId'] == $_SESSION['ubranch'] and $lesson['date'] > $today and sizeof($lesson['records']) < 10 and in_array($_SESSION['uage'], $ages) and $lesson['description'] == 'запись на мк')
    {
        $good_lessons[]  = $lesson;
    }
}
?>

<div id="pick_event_form" class = "form0">
    <form id = "pick_event" method="post">
        <label for="uevent">lesson:</label><br>
        <select id = "uevent" name = "uevent" >
            <option selected="selected"></option>
            <?php
            foreach($good_lessons as $lesson){
                ?>
                <option value="<?php echo $lesson['id']; ?>"><?php echo $lesson['beginTime'].''.$lesson['date']; ?></option>
                <?php
            }
            ?>
        </select><br><br>
        <input type="submit" name = "submitform3">
    </form>
</div>
