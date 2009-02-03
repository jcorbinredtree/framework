<table class = "xhtml-calendar-month">
<?php
    $daysInMonth = date('t', $this->calendar->date);
    $currentDay = date('j', $this->calendar->date);

    $scratchDay = $currentDay;
    $firstDay = $this->calendar->date;
    while ($scratchDay != 1) {
        $firstDay -= 86400;
        $scratchDay = date('j', $firstDay);
    }

    $currentDate = $firstDay;

    $index = -1 * date('w', $firstDay); /* negative offset */
    $counter = 0;

    if ($this->calendar->printMonthHeaders) {
        $headerDate = strtotime('2005-05-01'); /* just some time when sunday started the month */
        print '<tr>';
        for ($i = 0; $i < 7; $i++) {
            print '<th>' . date($this->calendar->monthHeaderFormat, ($headerDate + (86400 * $i))) . '</th>';
        }
        print '</tr>';
    }

    print '<tr>';
    for (; $index < $daysInMonth; $index++,$counter++) {
    $isCurrentMonth = false;
    $class = '';
        $printedIndex = (1 + $index);

        if ($counter && (($counter % 7) == 0)) {
            print '</tr><tr>';
        }

        print '<td ';
        if (($printedIndex > 0) && (date('Ymd') == date('Ymd', $currentDate))) {
            $class = "calendar-current-day";
            $isCurrentMonth = true;
        }
        elseif ($printedIndex < 1) {
            $class = "calendar-previous-month";
            $prevMonth = $firstDay - (86400 * (abs($printedIndex) + 1));
            $printedIndex = date('j', $prevMonth);
        }
        else {
            $class = "calendar-day";
            $isCurrentMonth = true;
        }

        /*
         * easy way to check if it's the current month
         */
        if ($isCurrentMonth) {
            if ($this->calendar->monthCellOver) {
                print ' onmouseover="' . $this->calendar->monthCellOver . '(this, ' . $currentDate . ');"';
            }

            if ($this->calendar->monthCellOut) {
                print ' onmouseout="' . $this->calendar->monthCellOut . '(this, ' . $currentDate . ');"';
            }

            if ($this->calendar->monthCellClicked) {
                print ' onclick="' . $this->calendar->monthCellClicked . '(this, ' . $currentDate . ');"';
            }
        }

    print '><div class = "calendar-index ' . $class . '">' . "$printedIndex</div>";

    if ($isCurrentMonth) {
        print $this->dateCallback($currentDate);
    }
    else {
        print '&nbsp;';
    }

    print "</td>";

    if ($isCurrentMonth) {
            $currentDate += 86400;
    }
    }

    $lastDay = $this->calendar->date;
    $scratchDay = $currentDay;
    while ($scratchDay != $daysInMonth) {
        $lastDay += 86400;
        $scratchDay = date('j', $lastDay);
    }

    $index = 6 - date('w', $lastDay);
    $counter = 1;
    while ($index--) {
        print '<td class = "calendar-next-month"><div class = "calendar-index">' . $counter++ . "</div></td>";
    }

    print '</tr>';
?>
</table>
