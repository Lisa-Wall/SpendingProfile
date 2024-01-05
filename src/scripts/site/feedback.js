
function Feedback(sId)
{
	var self = document.getElementById(sId);

	self.init = function()
	{
		self.innerHTML = "<table class='label'><tr><td valign='top'><img src='" + UI.image + "icon=comment.gif' onload='UI.setTooltip(this, \"Send us your comments about the website.<br/>Questions? Problems? Suggestions? Let us know!\")' /></td><td valign='top'>Send us your feedback:</td><td><textarea class='textarea' style='width:400px'></textarea></td><td valign='top'><input class='button' type='button' value='Submit' /></td></tr></table>";
		self.oTextArea = self.firstChild.rows[0].cells[2].firstChild;

		self.firstChild.rows[0].cells[3].firstChild.onclick = self.onSubmit;

		return self;
	}

	self.setFocus = function()
	{
		self.oTextArea.focus();
		self.oTextArea.scrollIntoView();
	}

	self.onSubmit = function()
	{
		if (self.oTextArea.value.length == 0) return alert("Please write some feedback before submitting.");

		AJAX.call(XML.serialize(true, "User.sendFeedback", "Message", self.oTextArea.value), self.submit_Response);
	}

	self.submit_Response = function(oResponse, sResponse, bSuccess)
	{
		if (bSuccess == false || oResponse.getAttribute("Type") != "OK")
		{
			alert("An error occured while attempting to send feedback. Please try again later.");
		}
		else
		{
			alert("Thank you! Your feedback has been received.");
			self.oTextArea.value = "";
		}
	}

	return self.init();
}