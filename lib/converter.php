<?php

require("common.php");
require("hebnum.php");

function g2h($gm, $gd, $gy) {
	global $hnum_to_str;
	$jd = gregoriantojd($gm, $gd, $gy);
	$hebdate = jdtojewish($jd);
	list($hmnum, $hd, $hy) = explode("/", $hebdate, 3);
	$hm = $hnum_to_str[$hmnum];
	$hebdate = jdtojewish($jd, true, CAL_JEWISH_ADD_GERESHAYIM);
	$hebdate = iconv('WINDOWS-1255', 'UTF-8', $hebdate);
	list($hd, $hm2, $hy) = explode(" ", $hebdate, 3);
	$hm = hebrew_month_prefix($hm);
	return "$hd $hm $hy";
}

function g2h_today() {
	return g2h(date('m'), date('d'), date('Y'));
}

function heb_str_to_num($value) {
	global $hebnum_ltrs;
	$c = 0;
	for ($i=0; $i < mb_strlen($value); $i++) {
		$ltr = mb_substr($value, $i, 1);
		if (array_key_exists($ltr, $hebnum_ltrs))
			$c += $hebnum_ltrs[$ltr];
	}
	return $c;
}

function heb_month_to_num($hm, $hy) {
	global $hnum_to_str2;
	if (!array_key_exists($hm, $hnum_to_str2))
		return false;
	$num = $hnum_to_str2[$hm];
	if ($num == 6 && !is_leap_year($hy))
		$num = 7;
	return $num;
}

function heb_year_today() {
	$jd = gregoriantojd(date('m'), date('d'), date('Y'));
	$hebdate = jdtojewish($jd);
	list($hm, $hd, $hy) = explode("/", $hebdate, 3);
	return $hy;
}

function multibyte_trim($rx, $str)
{
	return preg_replace("/(^$rx)|($rx$)/u", "", $str);
}

function h2g($hebdate, $hy2='') {
	$heb_ltrs = 'אבגדהוזחטיכלמנסעפצקרשתךףןץ ';
	$hebdate = preg_replace('/[^'.$heb_ltrs.']/u', "", $hebdate);
//	print_r($hebdate); print "<br>";
	$ex = explode(' ', $hebdate, 4);
	if (count($ex) == 3) {
		list($hd, $hm, $hy) = $ex;
	} else if (count($ex) == 4) {
		list($hd, $hm, $hm2, $hy) = $ex;
		$hm .= " $hm2";
	} else {
		return false;
	}
//	print $hd . " " . $hm . " " . $hy . "<br>";
	$hd = heb_str_to_num($hd);
	if ($hy2) {
		$hy = $hy2;
	} else {
		$hy = heb_str_to_num($hy);
		if ($hy < 1000)
			$hy+=5000;
	}
	$in_month = 'ב';
	$hm = multibyte_trim($in_month, $hm);
	$hm = heb_month_to_num($hm, $hy);
//	print "d:$hd m:$hm y:$hy <br>";
	if (!$hm)
		return false;
//	print $hd . " " . $hm . " " . $hy . "<br>";
	$jd = jewishtojd($hm, $hd, $hy);
	$greg = jdtogregorian($jd);
	return date('Y-m-d', strtotime($greg));
}

function h2g_year($hebdate, $hy) {
	return h2g($hebdate, $hy);
}

function h2g_next($hebdate) {
	$hy = heb_year_today();
	$ctime = h2g_year($hebdate, $hy);
	if (!$ctime)
		return false;

	$dt = new DateTime($ctime);
	$today = new DateTime(date('Y-m-d'));
	if ($dt < $today)
		$difference = -1;
	else
		$difference = $dt->diff($today)->days;
//	print "<br>".$difference."<br>";

	if ($difference < 0) {
		$hy++;
		$ctime = h2g_year($hebdate, $hy);
	}

	return $ctime;
}

function seconds_to_days($seconds) {
	$secondsInAMinute = 60;
	$secondsInAnHour  = 60 * $secondsInAMinute;
	$secondsInADay    = 24 * $secondsInAnHour;
	return floor($seconds / $secondsInADay);
}
