
var Site =
{
	onLoad:function()
	{
		if (oSession.email != null)
		{
			document.getElementById("page/tabs").style.display = "";
			document.getElementById("page/username").innerHTML = oSession.email;
			document.getElementById("page/signin").style.display = "none";
			document.getElementById("page/signout").style.display = "";
		}

		if (self.loadPage) loadPage();
	},

	signOut:function()
	{
		AJAX.call("<User.logout/>", function(){ window.location = "signin.php"; });
	}
}

