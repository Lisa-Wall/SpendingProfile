<?
include_once("server/config.php");
include_once("inc_session.php");
?>
<html>
	<head>
		<title>SpendingProfile.com - Track your finances online!</title>
		<script src="scripts/index.php?page=<?=$_SERVER['SCRIPT_NAME']?>"></script>
		<style>
		
			
		
		</style>
	</head>
	<body>
	
		<style>
			.clickable{ cursor:pointer; color:blue; text-decoration:underline }
		</style>
	
		<script>
			var oSession = <?=getJScriptSession()?>;
			
			function Hash()
			{
			}
			
			function showData(sRequest)
			{
				var oResponse = request(oSession.server, sRequest, response, progressStart, progressStop);
			
				//var sRequest = oSession.server + "?request=" + sRequest + "&SID=" + Math.random();
				//alert(sRequest);
				//document.getElementById("admin/frame").src = sRequest;
			}
			
			function response(oResponse, sResponse)
			{
				var sHtml = "";
				oResponse = oResponse.documentElement;
			
				if (oResponse.firstChild == null)
				{
					sHtml += drawHeader(oResponse);
					sHtml += drawRow(oResponse);
				}
				else
				{
					// Draw Header.
					sHtml += drawHeader(oResponse.firstChild);
					for (var oResult = oResponse.firstChild; oResult != null; oResult = oResult.nextSibling) sHtml += drawRow(oResult);
				}

				document.getElementById("admin/frame").innerHTML = "<table>" + sHtml + "</table>";
			}
			
			function drawHeader(oElement)
			{
				var sHeader = "";
				var iIndex = 0;
				var oAttributes = oElement.attributes;
				while (iIndex < oAttributes.length)
				{
					var oAttribute = oAttributes.item(iIndex++);							
					sHeader += "<td>" + oAttribute.nodeName + "</td>";							
				}
				return "<thead><tr>" + sHeader + "</tr></thead>";
			}
			
			function drawRow(oElement)
			{
				var sRow = "";
				var iIndex = 0;
				var oAttributes = oElement.attributes;
				while (iIndex < oAttributes.length)
				{
					var oAttribute = oAttributes.item(iIndex++);							
					sRow += "<td>" + oAttribute.nodeValue + "</td>";							
				}
				
				return "<tr>" + sRow + "</tr>";
			}
			
			
			function getTags(sName, sOrderBy, sOrderIn)
			{
				showData("<Admin.getTags Tag='"+sName+"' OrderBy='"+sOrderBy+"' OrderIn='"+sOrderIn+"' />");
			}
			
			function getUsers(sOrderBy, sOrderIn)
			{
				showData("<Admin.getUsers OrderBy='"+sOrderBy+"' OrderIn='"+sOrderIn+"' />");
			}
			
			function progressStart()
			{
				document.getElementById("admin/frame").innerHTML = "<img src='styles/icons/loader.gif'/>";
			}
			
			function progressStop()
			{
				//document.getElementById("admin/frame").innerHTML = "";
			}
			
			function request(sURL, sRequest, fResponse, fStart, fStop, sMethod)
			{
				var bSynchronous = (fResponse == null);

				//Create a browsers HTTP request object.
				var oHTTPRequest = (window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP"));

				if (!bSynchronous)
				{
					//Add the change event listener function.
					oHTTPRequest.onreadystatechange = function()
					{
						if (oHTTPRequest.readyState == 4 || oHTTPRequest.readyState == "complete")
						{
							if (fResponse != null) fResponse(oHTTPRequest.responseXML, oHTTPRequest.responseText);
							if (fStop != null) fStop(false);
						}
					};
				}

				//Open the request socket, set the http header and send the data.
				oHTTPRequest.open((sMethod == null? "POST" : sMethod), sURL+"?SID=" + Math.random(), !bSynchronous);

				//oHTTPRequest.setRequestHeader("content-encoding", "gzip");
				oHTTPRequest.setRequestHeader("content-type", "text/xml; charset=utf-8");
				oHTTPRequest.setRequestHeader("content-length", sRequest.length);
				oHTTPRequest.send(sRequest);

				//If a start function was specified then call it.
				if (fStart) fStart(true);
				return (bSynchronous ? {xml: oHTTPRequest.responseXML, text: oHTTPRequest.responseText} : oHTTPRequest);
			}
			
		</script>

		<table width="100%" height="100%" border="1">
			<tr>
				<td colspan="2"><a href="main.php"><img src="<?=$sImage?>image=banner/logo_white.png" border="0"/></a></td>
			</tr>
			<tr height="100%">
				<td width="250px" valign="top">
<ul>
	<li><a href="javascript:showData('<Admin.getUsers />')">Users</a>
		<ul>
			<li>Id (<span class="clickable" onclick="getUsers('Id', 'ASC')">Asc</span>, <span class="clickable" onclick="getUsers('Id', 'DESC')">Desc</span>)</li>
			<li>Email (<span class="clickable" onclick="getUsers('Email', 'ASC')">Asc</span>, <span class="clickable" onclick="getUsers('Email', 'DESC')">Desc</span>)</li>
			<li>CreatedOn (<span class="clickable" onclick="getUsers('CreatedOn', 'ASC')">Asc</span>, <span class="clickable" onclick="getUsers('CreatedOn', 'DESC')">Desc</span>)</li>
			<li>LastSignIn (<span class="clickable" onclick="getUsers('LastSignIn', 'ASC')">Asc</span>, <span class="clickable" onclick="getUsers('LastSignIn', 'DESC')">Desc</span>)</li>
			<li>Vendors (<span class="clickable" onclick="getUsers('Vendors', 'ASC')">Asc</span>, <span class="clickable" onclick="getUsers('Vendors', 'DESC')">Desc</span>)</li>
			<li>Accounts (<span class="clickable" onclick="getUsers('Accounts', 'ASC')">Asc</span>, <span class="clickable" onclick="getUsers('Accounts', 'DESC')">Desc</span>)</li>
			<li>Categories (<span class="clickable" onclick="getUsers('Categories', 'ASC')">Asc</span>, <span class="clickable" onclick="getUsers('Categories', 'DESC')">Desc</span>)</li>
			<li>Transactions (<span class="clickable" onclick="getUsers('Transactions', 'ASC')">Asc</span>, <span class="clickable" onclick="getUsers('Transactions', 'DESC')">Desc</span>)</li>
		</ul>	
	</li>

	<li><a href="javascript:showData('<Admin.getUserCount />')">User Count</a></li>
	<li><a href="javascript:showData('<Admin.getSignedInHistory />')">Signed In History</a></li>
	<li><a href="javascript:showData('<Admin.getCreatedOnHistory />')">Created Account History</a></li>
	<li><a href="javascript:showData('<Admin.getUserStats />')">User Statistics</a></li>
	<li><a href="javascript:showData('<Admin.getTransactionsAndTagsStats />')">Transactions and Tags</a></li>

	<li><a href="javascript:getTags('vendors', 'Count', 'DESC')">Vendors</a>
		<ul>
			<li>Name (<span class="clickable" onclick="getTags('vendors', 'Name', 'ASC')">Asc</span>, <span class="clickable" onclick="getTags('vendors', 'Name', 'DESC')">Desc</span>)</li>
			<li>Count (<span class="clickable" onclick="getTags('vendors', 'Count', 'ASC')">Asc</span>, <span class="clickable" onclick="getTags('vendors', 'Count', 'DESC')">Desc</span>)</li>
		</ul>
	</li>
	<li><a href="javascript:getTags('accounts', 'Count', 'DESC')">Accounts</a>
		<ul>
			<li>Name (<span class="clickable" onclick="getTags('accounts', 'Name', 'ASC')">Asc</span>, <span class="clickable" onclick="getTags('accounts', 'Name', 'DESC')">Desc</span>)</li>
			<li>Count (<span class="clickable" onclick="getTags('accounts', 'Count', 'ASC')">Asc</span>, <span class="clickable" onclick="getTags('accounts', 'Count', 'DESC')">Desc</span>)</li>
		</ul>
	</li>
	<li><a href="javascript:getTags('categories', 'Count', 'DESC')">Categories</a>
		<ul>
			<li>Name (<span class="clickable" onclick="getTags('categories', 'Name', 'ASC')">Asc</span>, <span class="clickable" onclick="getTags('categories', 'Name', 'DESC')">Desc</span>)</li>
			<li>Count (<span class="clickable" onclick="getTags('categories', 'Count', 'ASC')">Asc</span>, <span class="clickable" onclick="getTags('categories', 'Count', 'DESC')">Desc</span>)</li>
		</ul>
	</li>
	<li><a href="javascript:showData('<Admin.getRequests />')">Requests</a></li>
	<li><a href="javascript:showData('<Admin.getRequestStats />')">Request Statistics</a></li>
</ul>
				</td>
				<td id="admin/contentPanel">
					<div id="admin/frame" width="100%" height="100%"></div>
				</td>
			</tr>
		</table>



	</body>
</html>
