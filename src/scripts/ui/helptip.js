
function UI_Helptip()
{
	var self = UI_Shadow();

	self.init = function()
	{
		self.shadowPane.className = "ui_helptip";
		self.shadowPane.innerHTML = "<table class='ui_helptip_title' cellpadding='0' cellspacing='0'><tr class='ui_helptip_titlebar'><td><img src='"+UI.image+"icon=help.gif' style='vertical-align:bottom'/>&nbsp;&nbsp;<span></span></td><td align='right'></td></tr><tr><td colspan='2' class='ui_helptip_pane'></td></tr></table>";

		var oWindow = self.shadowPane.firstChild;

		self.titleBar = oWindow.rows[0];
		self.contentPane = self.panel = oWindow.rows[1].cells[0];

		//Get the titlbe bar cells.
		self.controls    = self.titleBar.cells[1];
		self.windowTitle = self.titleBar.cells[0].lastChild;

		//Add the controlls if required.
		self.controls.appendChild(UI_PushButton(UI.image+"image=ui/window/button_close_simple.png", "ui_window_button", function(){ self.onClose() }));

		//Insure that if these events occur on the event they are not bouble to the body and window.
		self.onscroll = function(event){ return UI.cancelBubble(event); };
		self.onmousedown = function(event){ return UI.cancelBubble(event); };

		return self;
	}

	self.onClose = function()
	{
		UI.hide(self);
	}

	self.set = function(sTitle, sContent)
	{
		self.windowTitle.innerHTML = sTitle;
		self.contentPane.innerHTML = sContent;
	}

	return self.init();
}