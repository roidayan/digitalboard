<?php

function hebcal_get_timezone_lang($cc) {
    $default = 'IL';
    $cc_defaults = [
	    'US' => ['s', 'America/New_York'],
	    'IL' => ['h', 'Asia/Jerusalem'],
	    'GB' => ['s', 'Europe/London'],
	    'CA' => ['s', 'America/Toronto'],
	    'AU' => ['s', 'Australia/Sydney'],
	    'ZA' => ['s', 'Africa/Johannesburg'],
	    'BR' => ['s', 'America/Sao_Paulo'],
	    'FR' => ['fr', 'Europe/Paris'],
	    'RU' => ['ru', 'Europe/Moscow'],
	    'PL' => ['pl', 'Europe/Warsaw'],
	    'FI' => ['fi', 'Europe/Helsinki'],
    ];

    if (!isset($cc_defaults[$cc]))
	$cc = $default;

    return $cc_defaults[$cc];
}


$hmstr_to_num = array(
	"Tishrei"  => 1,
	"Cheshvan" => 2,
	"Kislev"   => 3,
	"Tevet"    => 4,
	"Shvat"    => 5,
	"Adar1"    => 6,
	"Adar2"    => 7,
	"Nisan"    => 8,
	"Iyyar"    => 9,
	"Sivan"    => 10,
	"Tamuz"    => 11,
	"Av"       => 12,
	"Elul"     => 13,
);

$hnum_to_str = array_flip($hmstr_to_num);
$hmstr_to_num["Adar"] = 6;

$hmstr_to_num2 = array(
	1 => 'תשרי',
	2 => 'חשון',
	3 => 'כסלו',
	4 => 'טבת',
	5 => 'שבט',
	6 => 'אדר א',
	7 => 'אדר ב',
	8 => 'ניסן',
	9 => 'אייר',
	10 => 'סיוון',
	11 => 'תמוז',
	12 => 'אב',
	13 => 'אלול',
);

$hnum_to_str2 = array_flip($hmstr_to_num2);


function is_leap_year($hyear) {
	return (1 + ($hyear * 7)) % 19 < 7 ? true : false;
}
