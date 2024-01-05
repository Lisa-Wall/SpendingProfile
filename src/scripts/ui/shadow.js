
function UI_Shadow(sBorderImage, sBorderStyle)
{
	var self = document.createElement("TABLE");

	sBorderStyle = UI.defaultValue(sBorderStyle, "ui_shadow");
	sBorderImage = UI.defaultValue(sBorderImage, UI.image + "image=ui/shadow/");

	self.className = "ui_shadow";
	self.cellPadding = 0;
	self.cellSpacing = 0;

	var oTop = self.insertRow(-1);
	var oMiddle = self.insertRow(-1);
	var oButton = self.insertRow(-1);

	oTop.insertCell(-1).innerHTML = "<img src='"+sBorderImage+"top_left.png' class='"+sBorderStyle+"_corner'/>";
	oTop.insertCell(-1).className = sBorderStyle+"_top";
	oTop.insertCell(-1).innerHTML = "<img src='"+sBorderImage+"top_right.png' class='"+sBorderStyle+"_corner'/>";

	oMiddle.insertCell(-1).className = sBorderStyle+"_left";
	oMiddle.insertCell(-1).className = sBorderStyle+"_middle";
	oMiddle.insertCell(-1).className = sBorderStyle+"_right";

	oButton.insertCell(-1).innerHTML = "<img src='"+sBorderImage+"bottom_left.png' class='"+sBorderStyle+"_corner'/>";
	oButton.insertCell(-1).className = sBorderStyle+"_bottom";
	oButton.insertCell(-1).innerHTML = "<img src='"+sBorderImage+"bottom_right.png' class='"+sBorderStyle+"_corner'/>";

	self.shadowPane = oMiddle.cells[1];

	return self;
}
