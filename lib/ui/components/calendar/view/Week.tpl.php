<table class = "xhtml-calendar-week-view">
    <tr>
        <th class = "xhtml-calendar-week-hour-column"><?php echo date('Y', $this->calendar->date); ?></th>
        <?php
        $offsetDate = $localTime = $this->calendar->date;
        while (date('w', $localTime))    {
            $offsetDate = $localTime -= 86400;
        }

        for ($i = 0; $i < 7; $i++) {
            print '<th';

            if (date('Ymd') == date('Ymd', $localTime)) {
        print ' class="xhtml-calendar-week-view-today"';
            }

            print '>' . date($this->calendar->weekHeaderFormat, $localTime) . '</th>';
            $localTime += 86400;
        }
        ?>
    </tr>
    <tr>
        <td class = "xhtml-calendar-week-hour-column">&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr class = "xhtml-calendar-seperator-row">
        <td colspan = "8">&nbsp;</td>
    </tr>
    <?php
        for ($hour = 0.0; $hour < 24; $hour += 0.25) {
            print '<tr>';
            print '<td class = "xhtml-calendar-week-hour-column">';

            if (floor($hour) === $hour) {
        if ($hour == 12) {
            print 'noon';
        }
        else {
            print $this->formatHour($this->calendar->weekHourFormat, $hour);
        }
            }
            else {
        print '&nbsp;';
            }

            print '</td>';

            $offsetDate += $hour;
            for ($i = 0; $i < 7; $i++) {
        print '<td';

        if (date('Ymd') == date('Ymd', $offsetDate)) {
            print ' class="xhtml-calendar-week-view-today"';
        }

        print '>' . $this->timeCallback($offsetDate) . '</td>';
        $offsetDate += 86400;
            }

            $offsetDate -= (86400 * 7);

            print '</tr>';
        }
    ?>
</table>
