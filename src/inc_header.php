<?
include_once("server/config.php");
include_once("inc_session.php");
$sUrl = URL;

function tab($sText, $sLink)
{
	$sSelected = ($sLink == getPageName() ? '_select' : '');
	return '<table class="tab'.$sSelected.'" cellpadding="0px" cellspacing="0px"><tr><td class="tab_left'.$sSelected.'"></td><td class="tab_center'.$sSelected.'"><a href="'.$sLink.'">'.$sText.'</a></td><td class="tab_right'.$sSelected.'"></td></tr></table>';
}

?>
<html>
	<head>
		<title>SpendingProfile.com - Track your finances online!</title>
		<meta name="description" content="Track your finances online at SpendingProfile.com" />
		<meta name="keywords" content="Personal Finance, Financial Planning, Financial Advisor, Budget, Budgeting, Spending, Spending Habits, Debt, Credit Card Debt, Financial Goals" />
		<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1" />
		<link rel="STYLESHEET" type="text/css" href="<?=$sUrl?>styles/index.php"/>
		<script src="<?=$sUrl?>scripts/index.php?page=<?=$_SERVER['SCRIPT_NAME']?>"></script>
		<script>
			var oSession = <?=getJScriptSession()?>;
			AJAX.url = oSession.server;
			UI.image = "<?=$sImage?>";
			UI.delimiter = oSession.delimiter;
		</script>
	</head>
	<body class="body" onload="Site.onLoad()">

		<table class="header" cellpadding="0" cellspacing="0" align="center" width="<?=$PAGE_WIDTH?>">
			<tr>
				<td><img src="<?=$sUrl?>styles/images/empty.png" class="header_left"/></td>
				<td><a href="index.php"><img src="<?=$sUrl?>styles/images/empty.png" class="header_logo"/></a></td>
				<td class="header_sp"><a href="index.php">$pending Profile</a></td>
				<td class="header_icon"><a href="aboutus.php"><img src="<?=$sUrl?>styles/images/empty.png" class="header_aboutus"/><br/>About Us</a></td>
				<td class="header_icon"><a href="/blog"><img src="<?=$sUrl?>styles/images/empty.png" class="header_blog"/><br/>Blog</a></td>
				<td class="header_icon"><a href="contact.php"><img src="<?=$sUrl?>styles/images/empty.png" class="header_contact"/><br/>Contact</a></td>
				<td class="header_icon" id="page/signin"><a href="signin.php"><img src="<?=$sUrl?>styles/images/empty.png" class="header_sign"/><br/>Sign In</a></td>
				<td class="header_icon" id="page/signout" style="display:none"><a href="javascript:Site.signOut()"><img src="<?=$sUrl?>styles/images/empty.png" class="header_sign"/><br/>Sign Out</a></td>
				<td><img src="<?=$sUrl?>styles/images/empty.png" class="header_right"/></td>
			</tr>
		</table>

		<!-- if (file_exists('ads/adsense/'.getPageName().'.topbanner')) { ?>
		<center style="padding-bottom:8px"> include('ads/adsense/'.getPageName().'.topbanner') ?> </center>
		 } -->

		<? if ($bDemo) { ?>
		<table align="center"class="basiclabel" style="margin-bottom:10px;background-color:yellow;border:solid 3px #77a6c7" width="<?=$PAGE_WIDTH?>">
			<tr>
				<td colspan="3" align="center"><b>D E M O</b></td>
			<tr>
				<td width="40%" valign="top" style="padding-left:10px">This is a fully-functional demo of the site. Try everything and don`t hesitate to change the information that is here. To create your own account, click <a href="createaccount.php">here</a>.</td>
				<td width="40%" valign="top" style="padding-left:10px">
					<b>Things to try:</b>
					1. Add a transaction 2. Choose a different month to view 3. Create a budget 4. View the graphs page
				</td>
				<td width="20%" valign="top" style="padding-left:10px">Want your own account?<br>Create one <a href="createaccount.php">here</a>.</td>
			</tr>
		</table>
		<? } ?>


		<table id="page/tabs" class="tabs" id="page/tabs" cellpadding="0px" cellspacing="0px" align="center" width="<?=$PAGE_WIDTH?>" style="display:none">
			<tr>
				<td style="padding-left:20"></td>
				<td><?=tab('Main', 'main.php');?></td>
				<td><?=tab('Graphs', 'graphs.php');?></td>
				<td><?=tab('Budget', 'budget.php');?></td>
				<td><?=tab('Import', 'import.php');?></td>
				<td><?=tab('Account', 'account.php');?></td>
				<td class="tab_welcome">Welcome, <span id="page/username"></span></td>
			</tr>
		</table>

		<table class="content" cellpadding="0" cellspacing="0" align="center" width="<?=$PAGE_WIDTH?>">
			<tr>
				<td class="content_top_left"><img src="<?=$sUrl?>styles/images/empty.png" class="content_corner"/></td>
				<td class="content_top"></td>
				<td class="content_top_right"><img src="<?=$sUrl?>styles/images/empty.png" class="content_corner"/></td>
			</tr>
			<tr>
				<td class="content_left"></td>
				<td class="content_body">