<?php
/**
 * Copyright: (C) 2013 - 2023 José Conti
 *
 * @package WooCommerce Redsys Gateway.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Copyright: (C) 2013 - 2023 José Conti
 */
function redsys_return_currencies() {

	return array(
		'ALL' => 8,
		'DZD' => 12,
		'AOK' => 24,
		'MON' => 30,
		'AZM' => 31,
		'ARS' => 32,
		'AUD' => 36,
		'BSD' => 44,
		'BHD' => 48,
		'BDT' => 50,
		'AMD' => 51,
		'BBD' => 52,
		'BMD' => 60,
		'BTN' => 64,
		'BOP' => 68,
		'BAD' => 70,
		'BWP' => 72,
		'BRC' => 76,
		'BZD' => 84,
		'SBD' => 90,
		'BND' => 96,
		'BGL' => 100,
		'BUK' => 104,
		'BIF' => 108,
		'BYB' => 112,
		'KHR' => 116,
		'CAD' => 124,
		'CAD' => 124,
		'CVE' => 132,
		'LKR' => 144,
		'CLP' => 152,
		'CLP' => 152,
		'CNY' => 156,
		'CNH' => 157,
		'COP' => 170,
		'COP' => 170,
		'KMF' => 174,
		'ZRZ' => 180,
		'CRC' => 188,
		'CRC' => 188,
		'CUP' => 192,
		'CYP' => 196,
		'CSK' => 200,
		'CZK' => 203,
		'DKK' => 208,
		'DOP' => 214,
		'ECS' => 218,
		'SVC' => 222,
		'GQE' => 226,
		'ETB' => 230,
		'ERN' => 232,
		'FKP' => 238,
		'FJD' => 242,
		'DJF' => 262,
		'GEL' => 268,
		'GMD' => 270,
		'DDM' => 278,
		'GHC' => 288,
		'GIP' => 292,
		'GTQ' => 320,
		'GNS' => 324,
		'GYD' => 328,
		'HTG' => 332,
		'HNL' => 340,
		'HKD' => 344,
		'HUF' => 348,
		'ISK' => 352,
		'INR' => 356,
		'ISK' => 356,
		'IDR' => 360,
		'IRR' => 364,
		'IRA' => 365,
		'IQD' => 368,
		'ILS' => 376,
		'JMD' => 388,
		'JPY' => 392,
		'JPY' => 392,
		'KZT' => 398,
		'JOD' => 400,
		'KES' => 404,
		'KPW' => 408,
		'KRW' => 410,
		'KWD' => 414,
		'KGS' => 417,
		'LAK' => 418,
		'LBP' => 422,
		'LSM' => 426,
		'LVL' => 428,
		'LRD' => 430,
		'LYD' => 434,
		'LTL' => 440,
		'MOP' => 446,
		'MGF' => 450,
		'MWK' => 454,
		'MYR' => 458,
		'MVR' => 462,
		'MLF' => 466,
		'MTL' => 470,
		'MRO' => 478,
		'MUR' => 480,
		'MXP' => 484,
		'MXN' => 484,
		'MNT' => 496,
		'MDL' => 498,
		'MAD' => 504,
		'MZM' => 508,
		'OMR' => 512,
		'NAD' => 516,
		'NPR' => 524,
		'ANG' => 532,
		'AWG' => 533,
		'NTZ' => 536,
		'VUV' => 548,
		'NZD' => 554,
		'NIC' => 558,
		'NGN' => 566,
		'NOK' => 578,
		'PCI' => 582,
		'PKR' => 586,
		'PAB' => 590,
		'PGK' => 598,
		'PYG' => 600,
		'PEN' => 604,
		'PHP' => 608,
		'PLZ' => 616,
		'TPE' => 626,
		'QAR' => 634,
		'ROL' => 642,
		'RUB' => 643,
		'RWF' => 646,
		'SHP' => 654,
		'STD' => 678,
		'SAR' => 682,
		'SCR' => 690,
		'SLL' => 694,
		'SGD' => 702,
		'SKK' => 703,
		'VND' => 704,
		'SIT' => 705,
		'SOS' => 706,
		'ZAR' => 710,
		'ZWD' => 716,
		'YDD' => 720,
		'SSP' => 728,
		'SDP' => 736,
		'SDA' => 737,
		'SRG' => 740,
		'SZL' => 748,
		'SEK' => 752,
		'CHF' => 756,
		'CHF' => 756,
		'SYP' => 760,
		'TJR' => 762,
		'THB' => 764,
		'TOP' => 776,
		'TTD' => 780,
		'AED' => 784,
		'TND' => 788,
		'TRL' => 792,
		'PTL' => 793,
		'TMM' => 795,
		'UGS' => 800,
		'UAK' => 804,
		'MKD' => 807,
		'RUR' => 810,
		'EGP' => 818,
		'GBP' => 826,
		'TZS' => 834,
		'USD' => 840,
		'UYP' => 858,
		'UYP' => 858,
		'UZS' => 860,
		'VEB' => 862,
		'WST' => 882,
		'YER' => 886,
		'YUD' => 890,
		'YUG' => 891,
		'ZMK' => 892,
		'TWD' => 901,
		'TMT' => 934,
		'GHS' => 936,
		'RSD' => 941,
		'MZN' => 943,
		'AZN' => 944,
		'RON' => 946,
		'TRY' => 949,
		'TRY' => 949,
		'XAF' => 950,
		'XCD' => 951,
		'XOF' => 952,
		'XPF' => 953,
		'XEU' => 954,
		'ZMW' => 967,
		'SRD' => 968,
		'MGA' => 969,
		'AFN' => 971,
		'TJS' => 972,
		'AOA' => 973,
		'BYR' => 974,
		'BGN' => 975,
		'CDF' => 976,
		'BAM' => 977,
		'EUR' => 978,
		'UAH' => 980,
		'GEL' => 981,
		'PLN' => 985,
		'BRL' => 986,
		'BRL' => 986,
		'ZAL' => 991,
		'EEK' => 2333,
	);
}
