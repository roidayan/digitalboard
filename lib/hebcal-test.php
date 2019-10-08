<?php

require_once('hebcal.php');

$hebcal = new Hebcal();
//print_r( $hebcal->shabbat() );
//print_r( $hebcal->candles() );
$gy = date('Y');
$gm = date('m');
$gd = date('d');
//print_r( $hebcal->date_convert($gy, $gm, $gd) );
//print_r( $hebcal->calendar( $gm ) );
//print_r( $hebcal->calendar_today('holiday','major') );
//print_r( $hebcal->calendar_today('holiday') );
print_r( $hebcal->calendar_today_major_holiday() );
