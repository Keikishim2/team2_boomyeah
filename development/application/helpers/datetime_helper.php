<?php
    function time_ago($datetime) {
        $timezone     = new DateTimeZone("Asia/Taipei");
        $current_time = new DateTime("now", $timezone);
        $timestamp    = new DateTime($datetime, $timezone);
        $interval     = $current_time->diff($timestamp);
        $suffix       = ($interval->invert == 1) ? " ago" : "";

        if ($interval->m >= 2) {
            return date_format($timestamp, "M j, Y");
        }
        else if ($interval->m > 0) {
            $time = $interval->m > 1 ? "%m months" : "%m month";
        }
        else if ($interval->d > 0) {
            $time = $interval->d > 1 ? "%d days" : "%d day";
        }
        else if ($interval->h > 0) {
            $time = $interval->h > 1 ? "%h hours" : "%h hour";
        }
        else if ($interval->i > 0) {
            $time = $interval->i > 1 ? "%i minutes" : "%i minute";
        }
        else {
            return "Just Now";
        }

        return $interval->format($time.$suffix);
    }
?>