
 var AJAX =
{
	url: "",

	request:function(sURL, sRequest, fResponse, fStart, fStop, sMethod)
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
		//oHTTPRequest.setRequestHeader("content-length", sRequest.length);
		oHTTPRequest.send(sRequest);
		
		console.log("Request: " + sRequest);

		//If a start function was specified then call it.
		if (fStart) fStart(true);

		return (bSynchronous ? {xml: oHTTPRequest.responseXML, text: oHTTPRequest.responseText} : oHTTPRequest);
	},

	call:function(sRequest, fResponse, fLoader)
	{
		return AJAX.request(AJAX.url, sRequest, function(oResponse, sResponse){ AJAX.response(oResponse, sResponse, fResponse); }, fLoader, fLoader, "POST");
	},

	response:function(oResponse, sResponse, fCallback)
	{
		var bSuccess = true;

		if (fCallback == null)
		{
			return;
		}
		else if (oResponse == null)
		{
			bSuccess = AJAX.reportError("Null Response", sResponse);
		}
		else if (oResponse.documentElement == null)
		{
			bSuccess = AJAX.reportError("Empty Document", sResponse);
		}
		else
		{
			oResponse = oResponse.documentElement;

			//if (oResponse.getAttribute("Type") == "NOT_AUTHENTICATED") return Utility.location("signin.php");
			console.log("Response Type: " + oResponse.getAttribute("Type"));
/*
			//If document element is an ajax error then report.
			if (oResponse.nodeName.toLowerCase() == "error")
			{
				bSuccess = AJAX.reportError(oResponse.getAttribute("Type"), sResponse);
			}
			else if (oResponse.nodeName.toLowerCase() == "security")
			{
				var sType = oResponse.getAttribute("Type");

				if (sType == "NOT_LOGGED_IN")
				{
					Utility.login();
				}
				else
				{
					bSuccess = AJAX.reportError("Permission", sResponse);
				}
			}
*/
		}

		//Call callback function passing it false if error otherwise the document element.
		fCallback(oResponse, sResponse, bSuccess);
	},

	// Extra comments.
	reportError:function(sMessage, sResponse)
	{
		sMessage = sMessage;
		alert(sMessage + "\n" + sResponse);
		return false;
	}
};

