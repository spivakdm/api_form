<div id="age_filial_form" class = "form2">
    <form id = "age_filial" method="post">
        <label for="uage">age:</label><br>
        <input type="number" id="uage" name="uage"><br>
        <label for="ubranch">branch:</label><br>
        <select id ="ubranch" name="ubranch">
            <option selected="selected"></option>
            <?php
            $filial_list = api_get("https://api.moyklass.com/v1/company/filials", $_SESSION['token']);

            foreach($filial_list as $filial){
            ?>
            <option value="<?php echo $filial['id']; ?>"><?php echo $filial['name']; ?></option>
            <?php
            }
            ?>
        </select><br><br>
        <input type="submit" name = "submitform2">
    </form>

</div>
