<?php

require("common.php");
require("hebnum.php");

function g2h($gm, $gd, $gy) {
	global $hnum_to_str, $hebnum_months_inprefix;
	$jd = gregoriantojd($gm, $gd, $gy);
	$hebdate = jdtojewish($jd);
	list($hmnum, $hd, $hy) = explode("/", $hebdate, 3);
	$hm = $hnum_to_str[$hmnum];
	$hebdate = jdtojewish($jd, true, CAL_JEWISH_ADD_GERESHAYIM);
	$hebdate = iconv('WINDOWS-1255', 'UTF-8', $hebdate);
	list($hd, $hm2, $hy) = explode(" ", $hebdate, 3);
	$hm = $hebnum_months_inprefix[$hm];
	return "$hd $hm $hy";
}

function g2h_today() {
	return g2h(date('m'), date('d'), date('Y'));
}
