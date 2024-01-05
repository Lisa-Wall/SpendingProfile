<?
include_once("server/config.php");
include_once("inc_session.php");
?>
<html>
	<head>
		<title>SpendingProfile.com - Track your finances online!</title>
		<meta name="description" content="Track your finances online at SpendingProfile.com" />
		<meta name="keywords" content="Personal Finance, Financial Planning, Financial Advisor, Budget, Budgeting, Spending, Spending Habits, Debt, Credit Card Debt, Financial Goals" />
		<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
		<link rel="STYLESHEET" type="text/css" href="styles/index.php"></link>
		<script src="scripts/index.php"></script>
	</head>
	<body class="body" left="0" topmargin="4" leftmargin="0">

		<!-- Instructions -->
		<div style="width:998" align="center"><input id="button" type="button" value="Back to screen shots" onclick="history.go(-1)"></div>

		<!-- Image -->
		<img src="<?=$sImage?>image=sample/<?=$_REQUEST['page']?>.png" border="0"/>
	</body>
</html>
