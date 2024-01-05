
function TransactionInput(oManager) //add, getVendors, getAccounts, getCategories, showVendorEditor, showAccountEditor, showCategoryEditor
{
	var self = this;

	self.oCalendar = new UI_Calendar();
	self.oCalculator = new UI_Calculator();

	self.oForm = document.transactionInput;
	self.oTable = document.getElementById("transaction/input/table");
	self.oExpander = document.getElementById("transaction/input/expander");

	self.init = function()
	{
		self.oTable.expanded = true;
		
		//Build uploader
		self.oUploader = new UI_Uploader("transaction/input/uploader", self.upload, self.removeUpload, self.cancelUpload);

		//Add the calendar onselect listener and the calculator listener.
		self.oCalendar.onSelect = function(iYear, iMonth, iDay){ self.oForm.date.value = CDate.format(iYear, iMonth, iDay); UI.hide(self.oCalendar); };
		self.oCalculator.onSelect = function(sValue){ self.oForm.amount.value = sValue; };

		//Create Tag input fields.
		self.buildDropdown("transaction/input/vendor", "vendor", self.getVendors, 7);
		self.buildDropdown("transaction/input/account", "account", self.getAccounts, 8);
		self.buildDropdown("transaction/input/category", "category", self.getCategories, 9);

		//Set default date as todays.
		self.oForm.date.value = CDate.now();
	}

	self.buildDropdown = function(sId, sName, fGetValues, iTabIndex)
	{
		var oDropdown = document.getElementById(sId).appendChild( UI_SmartDropdown(fGetValues, null, false, sName) );
		oDropdown.oInput.style.width = "200px";
		oDropdown.oInput.maxlength = "128";
		oDropdown.oInput.tabIndex = iTabIndex;
	}

	self.onSubmit = oManager.add;
	self.getVendors = oManager.getVendors;
	self.getAccounts = oManager.getAccounts;
	self.getCategories = oManager.getCategories;
	self.showVendorEditor = oManager.showVendorEditor;
	self.showAccountEditor = oManager.showAccountEditor;
	self.showCategoryEditor = oManager.showCategoryEditor;

	self.showCalendar = function(oRelativeTo)
	{
		var sDate = self.oForm.date.value;
		var bDate = CDate.validate(sDate);

		self.oCalendar.set(bDate ? sDate.toDate() : oManager.getSelectedDate());
		UI.showPopupRelativeTo(self.oCalendar, oRelativeTo, 20, -10)
	}

	self.showCalculator = function(oRelativeTo)
	{
		UI.showRelativeTo(self.oCalculator.oWindow, oRelativeTo, 20, -10);
	}

	self.submit = function()
	{
		if (self.validate())
		{
			if (!self.oUploader.bIsUploading || confirm(sUploadingInProgress))
			{
				self.onSubmit(self.getForm());
			}
		}

	}

	self.getForm = function()
	{
		var oForm = self.oForm;
		var sFixed = (oForm.fixed[0].checked ? "1" : "0");
		var sDebit = (oForm.debit[0].checked ? "1" : "0");
		var sReceipt = (self.oUploader.sMode == "UPLOADED" ? "1" : "0");
		return XML.serialize(true, "Transaction.add", "Fixed", sFixed, "Debit", sDebit, "Amount", oForm.amount.value, "Date", oForm.date.value, "Account", oForm.account.value, "Category", oForm.category.value, "Vendor", oForm.vendor.value, "Notes", oForm.notes.value, "Receipt", sReceipt);
	}

	self.validate = function()
	{
		var sMessage = "";
		if (!Validate.float(self.oForm.amount.value)) sMessage += "- Amount\n";
		if (!Validate.date(self.oForm.date.value)) sMessage += "- Date\n"

		if (sMessage.length > 0) alert("The following field(s) are invalid:\n" + sMessage);

		if (self.oForm.category.value.length == 0)
		{
			alert("The category cannot be blank. Please enter a category.");
			return false;
		}

		return (sMessage.length == 0);
	}

	self.clearForm = function()
	{
		//Store the value in the date field.
		var sDate = self.oForm.date.value;

		self.oForm.reset();
		self.oUploader.reset();

		//If it is not empty then add it back.
		self.oForm.date.value = sDate;
	}

	self.setExpanded = function(bExpanded)
	{
		self.oTable.expanded = bExpanded;
		self.oTable.style.display = (bExpanded ? "" : "none");
		self.oExpander.src = UI.image + "icon=tree_"+(bExpanded?"collapse":"expand")+".png";
	}



	self.upload = function()
	{
		self.oForm.request.value = XML.serialize(true, "Receipt.upload", "Name", "Receipt", "Field", "receipt", "Callback", "oTransactionInput.upload_Response");
		self.oForm.submit();
	}

	self.cancelUpload = function()
	{
		var oFrame = document.getElementById("transaction/input/uploaderframe");
		oFrame.document.close();
		oFrame.document.innerHTML = "";

		self.oUploader.cancel();
	}

	self.removeUpload = function()
	{
		AJAX.call("<Receipt.clearAll />", function(oR,sR,bS){ self.oUploader.remove(); });
	}

	self.upload_Response = function(sResponse, sFile, sSize)
	{
		if (sResponse == "1") self.oUploader.uploaded();
		else self.oUploader.error(sResponse);
	}



	self.toggleExpanded = function(){ self.setExpanded(!self.oTable.expanded); };

	self.init();
}

var sUploadingInProgress = "Receipt is still being upload. If you add transaction now the upload will be cancel.\nWould you like to continue?";