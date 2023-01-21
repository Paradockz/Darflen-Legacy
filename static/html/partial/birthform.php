<div id="birthdate" class="birthdate-input" name="birthdate">
    <select name="year" title="Birthdate Year">
        <?php
        $years = date("Y") - 1900;
        echo "<option value='' selected>Year</option>";
        for ($index = 0; $index <= $years; $index++) {
            $year = 1900 + $years - $index;
            echo "<option value='$year'>$year</option>";
        }
        ?>
    </select>
    <select name="month" title="Birthdate Month">
        <?php
        $months = ["January", "Febuary", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        echo "<option value='' selected>Month</option>";
        for ($index = 1; $index <= 12; $index++) {
            $monthIndex = $index;
            $month = $months[$index - 1];
            if (strlen($index) < 2) {
                echo "<option value='0$monthIndex'>$month</option>";
            } else {
                echo "<option value='$monthIndex'>$month</option>";
            }
        }
        ?>
    </select>
    <select name="day" title="Birthdate Day">
        <?php
        echo "<option value='' selected>Day</option>";
        for ($index = 1; $index <= 31; $index++) {
            if (strlen($index) < 2) {
                echo "<option value='0$index'>0$index</option>";
            } else {
                echo "<option value='$index'>$index</option>";
            }
        }
        ?>
    </select>
</div>