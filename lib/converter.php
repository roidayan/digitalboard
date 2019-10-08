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
		$c += $hebnum_ltrs[$ltr];
	}
	return $c;
}

function heb_month_to_num($hm, $hy) {
	global $hnum_to_str2;
	$num = $hnum_to_str2[$hm];
	if ($num == 6 && !is_leap_year($hy))
		$num = 7;
	return $num;
}

function h2g($hebdate) {
	$heb_ltrs = 'אבגדהוזחטיכלמנסעפצקרשתךףןץ ';
	$hebdate = preg_replace('/[^'.$heb_ltrs.']/', "", $hebdate);
	$ex = explode(' ', $hebdate, 4);
	if (count($ex) == 3) {
		list($hd, $hm, $hy) = $ex;
	} else {
		list($hd, $hm, $hm2, $hy) = $ex;
		$hm .= " $hm2";
	}
//	print $hd . " " . $hm . " " . $hy . "<br>";
	$hd = heb_str_to_num($hd);
	$hy = heb_str_to_num($hy);
	if ($hy < 1000)
		$hy+=5000;
	$hm = heb_month_to_num($hm, $hy);
//	print $hd . " " . $hm . " " . $hy . "<br>";
	$jd = jewishtojd($hm, $hd, $hy);
	$greg = jdtogregorian($jd);
	list($gm, $gd, $gy) = explode("/", $greg, 3);
	return "$gd-$gm-$gy";
}
