<?php
/***************************************************************************
 *   copyright				: (C) 2008, 2009 WeBid
 *   site					: http://www.webidsupport.com/
 ***************************************************************************/

/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version. Although none of the code may be
 *   sold. If you have been sold this script, get a refund.
 ***************************************************************************/

include 'includes/common.inc.php';
include $include_path . 'countries.inc.php';

// If user is not logged in redirect to login page
if (!$user->logged_in)
{
	header('location: user_login.php');
	exit;
}

// Retrieve users signup settings
$MANDATORY_FIELDS = unserialize($system->SETTINGS['mandatory_fields']);

function generateSelect($name = '', $options = array())
{
	global $selectsetting;
	$html = '<select name="' . $name . '">';
	foreach ($options as $option => $value)
	{
		if ($selectsetting == $option)
		{
			$html .= '<option value=' . $option . ' selected>' . $value . '</option>';
		}
		else
		{
			$html .= '<option value=' . $option . '>' . $value . '</option>';
		}
	}
	$html .= '</select>';
	return $html;
}

$TIMECORRECTION = array(
	'-23' => '-23 h',
	'-22' => '-22 h',
	'-21' => '-21 h',
	'-20' => '-20 h',
	'-19' => '-19 h',
	'-18' => '-18 h',
	'-17' => '-17 h',
	'-16' => '-16 h',
	'-15' => '-15 h',
	'-14' => '-14 h',
	'-13' => '-13 h',
	'-12' => '-12 h',
	'-11' => '-11 h',
	'-10' => '-10 h',
	'-9' => '-9 h',
	'-8' => '-8 h',
	'-7' => '-7 h',
	'-6' => '-6 h',
	'-5' => '-5 h',
	'-4' => '-4 h',
	'-3' => '-3 h',
	'-2' => '-2 h',
	'-1' => '-1 h',
	'0' => 'GMT',
	'+1' => '+1 h',
	'+2' => '+2 h',
	'+3' => '+3 h',
	'+4' => '+4 h',
	'+5' => '+5 h',
	'+6' => '+6 h',
	'+7' => '+7 h',
	'+8' => '+8 h',
	'+9' => '+9 h',
	'+10' => '+10 h',
	'+11' => '+11 h',
	'+12' => '+12 h',
	'+13' => '+13 h',
	'+14' => '+14 h',
	'+15' => '+15 h',
	'+16' => '+16 h',
	'+17' => '+17 h',
	'+18' => '+18 h',
	'+19' => '+19 h',
	'+20' => '+20 h',
	'+21' => '+21 h',
	'+22' => '+22 h',
	'+23' => '+23 h'
);

$query = "SELECT * FROM " . $DBPrefix . "gateways LIMIT 1";
$res = mysql_query($query);
$system->check_mysql($res, $query, __LINE__, __FILE__);
$gateway_data = mysql_fetch_assoc($res);

$ERR = '';
if (isset($_POST['action']) && $_POST['action'] == 'update')
{
	// Check data
	if ($_POST['TPL_email'])
	{
		if (strlen($_POST['TPL_password']) < 6 && strlen($_POST['TPL_password']) > 0)
		{
			$TPL_err = 1;
			$ERR = $ERR_011;
		}
		elseif ($_POST['TPL_password'] != $_POST['TPL_repeat_password'])
		{
			$TPL_err = 1;
			$ERR = $ERR_109;
		}
		elseif (strlen($_POST['TPL_email']) < 5)
		{
			$TPL_err = 1;
			$ERR = $ERR_110;
		}
		elseif (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$", $_POST['TPL_email']))
		{
			$TPL_err = 1;
			$ERR = $ERR_008;
		}
		elseif (strlen($_POST['TPL_zip']) < 4 && $MANDATORY_FIELDS['zip'] == 'y')
		{
			$TPL_err = 1;
			$ERR = $ERR_616;
		}
		elseif (strlen($_POST['TPL_phone']) < 3 && $MANDATORY_FIELDS['tel'] == 'y')
		{
			$TPL_err = 1;
			$ERR = $ERR_617;
		}
		elseif ((empty($_POST['TPL_day']) || empty($_POST['TPL_month']) || empty($_POST['TPL_year'])) && $MANDATORY_FIELDS['birthdate'] == 'y')
		{
			$TPL_err = 1;
			$ERR = $ERR_5040;
		}
		elseif ((empty($_POST['TPL_day']) && empty($_POST['TPL_month']) && empty($_POST['TPL_year'])) || !checkdate($_POST['TPL_month'], $_POST['TPL_day'], $_POST['TPL_year']))
		{
			$TPL_err = 1;
			$ERR = $ERR_117;
		}
		else
		{
			$TPL_birthdate = $_POST['TPL_year'] . $_POST['TPL_month'] . $_POST['TPL_day'];

			$query = "UPDATE " . $DBPrefix . "users SET email='" . $system->cleanvars($_POST['TPL_email']) . "',
					birthdate = '" . (empty($TPL_birthdate)) ? 0 : $TPL_birthdate . "',
					address = '" . $system->cleanvars($_POST['TPL_address']) . "',
					city = '" . $system->cleanvars($_POST['TPL_city']) . "',
					prov = '" . $system->cleanvars($_POST['TPL_prov']) . "',
					country = '" . $system->cleanvars($_POST['TPL_country']) . "',
					zip = '" . $system->cleanvars($_POST['TPL_zip']) . "',
					phone = '" . $system->cleanvars($_POST['TPL_phone']) . "',
					timecorrection = '" . $system->cleanvars($_POST['TPL_timezone']) . "',
					emailtype = '" . $system->cleanvars($_POST['TPL_emailtype']) . "',
					nletter = '" . $system->cleanvars($_POST['TPL_nletter']) . "'";

			if ($gateway_data['paypal_active'] == 1)
			{
				$query .= ", paypal_email = '" . $system->cleanvars($_POST['TPL_pp_email']) . "'";
			}

			if ($gateway_data['authnet_active'] == 1)
			{
				$query .= ", authnet_id = '" . $system->cleanvars($_POST['TPL_authnet_id']) . "',
							authnet_pass = '" . $system->cleanvars($_POST['TPL_authnet_pass']) . "'";
			}

			if (strlen($_POST['TPL_password']) > 0)
			{
				$query .= ", password = '" . md5($MD5_PREFIX . addslashes($_POST['TPL_password'])) . "'";
			}

			$query .= " WHERE id = " . $user->user_data['id'];
			$system->check_mysql(mysql_query($query), $query, __LINE__, __FILE__);
			$ERR = $MSG['183'];
		}
	}
	else
	{
		$ERR = $ERR_112;
	}
}

// Retrieve user's data
$query = "SELECT * FROM " . $DBPrefix . "users WHERE id = " . $user->user_data['id'];
$result = mysql_query($query);
$system->check_mysql($result, $query, __LINE__, __FILE__);
$USER = mysql_fetch_array($result);
if ($USER['birthdate'] != 0)
{
	$TPL_day = substr($USER['birthdate'], 6, 2);
	$TPL_month = substr($USER['birthdate'], 4, 2);
	$TPL_year = substr($USER['birthdate'], 0, 4);
}
else
{
	$TPL_day = '';
	$TPL_month = '';
	$TPL_year = '';
}

$country = '';
foreach ($countries as $code => $name)
{
	$country .= '<option value="' . $name . '"';
	if ($name == $USER['country'])
	{
		$country .= ' selected';
	}
	$country .= '>' . $name . '</option>' . "\n";
}
$dobmonth = '<select name="TPL_month">
		<option value=""></option>
		<option value="01"' . (($TPL_month == '01') ? ' selected' : '') . '>' . $MSG['MON_001E'] . '</option>
		<option value="02"' . (($TPL_month == '02') ? ' selected' : '') . '>' . $MSG['MON_002E'] . '</option>
		<option value="03"' . (($TPL_month == '03') ? ' selected' : '') . '>' . $MSG['MON_003E'] . '</option>
		<option value="04"' . (($TPL_month == '04') ? ' selected' : '') . '>' . $MSG['MON_004E'] . '</option>
		<option value="05"' . (($TPL_month == '05') ? ' selected' : '') . '>' . $MSG['MON_005E'] . '</option>
		<option value="06"' . (($TPL_month == '06') ? ' selected' : '') . '>' . $MSG['MON_006E'] . '</option>
		<option value="07"' . (($TPL_month == '07') ? ' selected' : '') . '>' . $MSG['MON_007E'] . '</option>
		<option value="08"' . (($TPL_month == '08') ? ' selected' : '') . '>' . $MSG['MON_008E'] . '</option>
		<option value="09"' . (($TPL_month == '09') ? ' selected' : '') . '>' . $MSG['MON_009E'] . '</option>
		<option value="10"' . (($TPL_month == '10') ? ' selected' : '') . '>' . $MSG['MON_010E'] . '</option>
		<option value="11"' . (($TPL_month == '11') ? ' selected' : '') . '>' . $MSG['MON_011E'] . '</option>
		<option value="12"' . (($TPL_month == '12') ? ' selected' : '') . '>' . $MSG['MON_012E'] . '</option>
	</select>';
$dobday = '<select name="TPL_day">
		<option value=""></option>';
for ($i = 1; $i <= 31; $i++)
{
	$j = (strlen($i) == 1) ? '0' . $i : $i;
	$dobday .= '<option value="' . $j . '"' . (($TPL_day == $j) ? ' selected' : '') . '>' . $j . '</option>';
}
$dobday .= '</select>';

$selectsetting = $USER['timecorrection'];
$time_correction = generateSelect('TPL_timezone', $TIMECORRECTION);

$template->assign_vars(array(
		'COUNTRYLIST' => $country,
		'NAME' => $USER['name'],
		'NICK' => $USER['nick'],
		'EMAIL' => $USER['email'],
		'YEAR' => $TPL_year,
		'ADDRESS' => $USER['address'],
		'CITY' => $USER['city'],
		'PROV' => $USER['prov'],
		'ZIP' => $USER['zip'],
		'PHONE' => $USER['phone'],
		'DATEFORMAT' => ($system->SETTINGS['datesformat'] == "USA") ? $dobmonth . ' ' . $dobday : $dobday . ' ' . $dobmonth,
		'TOMEZONE' => $time_correction,

		//payment stuff
		'PP_EMAIL' => $USER['paypal_email'],
		'AN_ID' => $USER['authnet_id'],
		'AN_PASS' => $USER['authnet_pass'],

		'NLETTER1' => ($USER['nletter'] == 1) ? ' checked="checked"' : '',
		'NLETTER2' => ($USER['nletter'] == 2) ? ' checked="checked"' : '',
		'EMAILTYPE1' => ($USER['emailtype'] == 'html') ? ' checked="checked"' : '',
		'EMAILTYPE2' => ($USER['emailtype'] == 'text') ? ' checked="checked"' : '',

		'B_NEWLETTER' => ($system->SETTINGS['newsletter'] == 1),
		'B_PAYPAL' => ($gateway_data['paypal_active'] == 1),
		'B_AUTHNET' => ($gateway_data['authnet_active'] == 1)
		));

$TMP_usmenutitle = $MSG['509'];
include 'header.php';
include 'includes/user_cp.php';
$template->set_filenames(array(
		'body' => 'edit_details.tpl'
		));
$template->display('body');
include 'footer.php';
?>