
function UI_WindowPopup(sTitle)
{
	var self = UI_Shadow();

	self.contentPane = null;

	self.init = function()
	{
		self.shadowPane.className = "ui_window";
		self.shadowPane.innerHTML = "<table class='ui_window_title'><tr><td></td><td align='right'></td></tr><tr><td colspan='2'></td></tr></table>";

		var oWindow = self.shadowPane.firstChild;

		self.titleBar = oWindow.rows[0];
		self.contentPane = oWindow.rows[1].cells[0];

		//Get the titlbe bar cells.
		self.controls    = self.titleBar.cells[1];
		self.windowTitle = self.titleBar.cells[0];

		//Add the controlls if required.
		self.controls.appendChild(UI_PushButton(UI.image+"image=ui/window/button_close_simple.png", "ui_window_button", function(){ self.onClose() }));

		//Insure that if these events occur on the event they are not bouble to the body and window.
		self.onscroll = function(event){ return UI.cancelBubble(event); };
		self.onmousedown = function(event){ return UI.cancelBubble(event); };

		self.setTitle(UI.defaultValue(sTitle, ""));

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

	self.setTitle = function(sTitle)
	{
		self.windowTitle.innerHTML = sTitle;
	}

	return self.init();
}