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
		<script>
			var oSession = <?=getJScriptSession()?>;
			AJAX.url = oSession.server;
			UI.image = "<?=$sImage?>";
		</script>
	</head>
	<body class="body">

		<p>
			<b>Day Calendar</b> <a id="calendar/day/show" href="javascript:showDayCalendar()">[show]</a> <span id="calendar/day/value"></span>
			<script>
				var oDayCalendar = new UI_Calendar();

				oDayCalendar.onSelect = function(iYear, iMonth, iDay)
				{
						document.getElementById("calendar/day/value").innerHTML = iYear + "-" + iMonth + "-" + iDay;

						UI.hide(oDayCalendar);
				}

				function showDayCalendar()
				{
						oDayCalendar.set(new Date());

						UI.showRelativeTo(oDayCalendar, document.getElementById("calendar/day/show"), 0, 0);
				}
			</script>
		</p>

		<p>
			<b>Month Calendar</b> <a id="calendar/month/show" href="javascript:showMonthCalendar()">[show]</a> <span id="calendar/month/value"></span>
			<script>
				var oMonthCalendar = new UI_Calendar();

				oMonthCalendar.onSelectMonth = function(iYear, iMonth, iDay)
				{
						document.getElementById("calendar/month/value").innerHTML = iYear + "-" + iMonth + "-" + iDay;

						UI.hide(oMonthCalendar);
				}

				function showMonthCalendar()
				{
						oMonthCalendar.setMonth(new Date());

						UI.showRelativeTo(oMonthCalendar, document.getElementById("calendar/month/show"), 0, 0);
				}
			</script>
		</p>


		<p>
			<b>Text Area Popup</b> <a id="textareapopup/show" href="javascript:showTextAreaPopup()">[show]</a> <span id="textareapopup/value"></span>
			<script>
				var oTextAreaPopup = new UI_TextAreaPopup()

				oTextAreaPopup.onEnter = function(sValue)
				{
						document.getElementById("textareapopup/value").innerHTML = sValue;

						UI.hide(oTextAreaPopup);
				}

				function showTextAreaPopup()
				{
						UI.showRelativeTo(oTextAreaPopup, document.getElementById("textareapopup/show"), 0, 0);

						oTextAreaPopup.set("Welcome to the text area popup!", "Text Area Popup");
				}

			</script>
		</p>

	</body>
</html>