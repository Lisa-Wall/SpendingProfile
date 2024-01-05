
function UI_PushButton(sIcon, sStyle, fAction)
{
	pButton = document.createElement("IMG");

	pButton.src = UI.image + "image=ui/empty.png";
	pButton.disabled = false;
	pButton.className = sStyle;
	pButton.style.backgroundImage = "url(" + sIcon + ")";

	pButton.onclick     = function(){ this.className = sStyle; if (fAction) fAction() };
	pButton.onmouseup   = function(){ if (!this.disabled) this.className = sStyle + "_over"; };
	pButton.onmouseout  = function(){ if (!this.disabled) this.className = sStyle; };
	pButton.onmouseover = function(){ if (!this.disabled) this.className = sStyle + "_over"; };
	pButton.onmousedown = function(){ if (!this.disabled) this.className = sStyle + "_down"; };

	pButton.setDisabled = function(bDisabled)
	{
			pButton.disabled = bDisabled;
			pButton.className = sStyle + (bDisabled ? "_disabled" : "");
	};

	return pButton;
}