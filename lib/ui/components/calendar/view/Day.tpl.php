<?php $currentDay = date('Ymd') == date('Ymd', $this->calendar->date); ?>
<table class = "xhtml-calendar-day-view">
    <tr>
        <th class = "xhtml-calendar-day-hour-column"><?php echo date('Y', $this->calendar->date); ?></th>
        <?php
        print '<th';
        if ($currentDay) {
        print ' class="xhtml-calendar-day-view-today"';
        }
        print '>' . date($this->calendar->dayHeaderFormat, $this->calendar->date) . '</th>';
        ?>
    </tr>
    <tr>
        <td class = "xhtml-calendar-day-hour-column">&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr class = "xhtml-calendar-seperator-row">
        <td colspan = "2">&nbsp;</td>
    </tr>
    <?php
        $currentDate = $this->calendar->date;
        for ($hour = 0.0; $hour < 24; $hour += 0.25) {
            print '<tr>';
            print '<td class = "xhtml-calendar-day-hour-column">';

            if (floor($hour) === $hour) {
        if ($hour == 12) {
            print 'noon';
        }
        else {
            print $this->formatHour($this->calendar->dayHourFormat, $hour);
        }
            }
            else {
        print '&nbsp;';
            }

            print '</td>';

            $currentDate += $hour;

            print '<td';
            if ($currentDay) {
        print ' class="xhtml-calendar-day-view-today"';
            }
            print '>' . $this->timeCallback($currentDate) . '</td>';

            print '</tr>';
        }
    ?>
</table>
