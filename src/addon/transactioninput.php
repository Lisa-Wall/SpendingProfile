<div>
	<fieldset class="page_fieldset" style="padding-left:20px;padding-right:10px">
		<legend class="page_fieldset_title" onclick="oTransactionInput.toggleExpanded()" style="cursor:pointer"><img id="transaction/input/expander" src="<?=$sImage?>icon=tree_collapse.png" style="vertical-align:middle"/>Add Transaction</legend>
		<form name="transactionInput" action="<?=SERVER?>" target="transaction/input/uploaderframe" method="post" enctype="multipart/form-data">
			<!--input type="hidden" name="MAX_FILE_SIZE" value="262144" /-->
			<input type="hidden" name="request" value=""/>
			<table id="transaction/input/table" class="page_text" cellpadding="1" cellspacing="1" width="100%">
				<tr>
					<td width="75px"></td>
					<td width="275px">
						<input name="debit" type="radio" tabindex="1" checked="true"/><span onclick="this.previousSibling.click()" style="cursor:default">Expense</span>
						<input name="debit" type="radio" tabindex="2" /><span onclick="this.previousSibling.click()" style="cursor:default">Income</span></td>
					</td>
					<td id="ad" rowspan="10" style="padding-right:10px;height:100%" valign="top"></td>
				</tr>
				<tr>
					<td><font size='1' color='orange'><sup>*</sup></font>Amount:</td>
					<td>
						<input name="amount" type="text" tabindex="3" class="textfield" />
						<img src="<?=$sImage?>icon=calculator.png" onclick="oTransactionInput.showCalculator(this)" style="vertical-align:top" onload="UI.setTooltip(this, 'Calculator')"/>

						<img src="<?=$sImage?>icon=import.png" onclick="window.location='import.php'" style="vertical-align:top;cursor:pointer" onload="UI.setTooltip(this, 'Import transactions from your bank account or credit card.')"/>
					</td>
				</tr>
				<tr>
					<td><font size='1' color='orange'><sup>*</sup></font>Date:</td>
					<td>
						<input name="date" type="text" tabindex="4" class="textfield"/>
						<img src="<?=$sImage?>icon=calendar.png" onclick="oTransactionInput.showCalendar(this)" style="vertical-align:top" onload="UI.setTooltip(this, 'Calendar')"/> <span style="font-size:9;vertical-align:middle">(YYYY-MM-DD)</span>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input name="fixed" type="radio" tabindex="5" /><span onclick="this.previousSibling.click()" style="cursor:default">Fixed</span>
						<input name="fixed" type="radio" tabindex="6" checked="true"/><span onclick="this.previousSibling.click()" style="cursor:default">Variable</span>
						<img src="<?=$sImage?>icon=info.png" class="clickicon" onload="UI.setHelptip(this, 'Fixed / Variable', sFixVariableHelptip)"/>
					</td>
				</tr>
				<tr>
					<td>Vendor:</td>
					<td><span id="transaction/input/vendor"></span>&nbsp;<a href="javascript:oTransactionInput.showVendorEditor()" title="Rename or delete vendors.">Edit</a> <img src="<?=$sImage?>icon=info.png" class="clickicon" onload="UI.setHelptip(this, 'Select a Vendor', sVendorHelptip)"/></td>
				</tr>
				<tr>
					<td>Account:</td>
					<td><span id="transaction/input/account"></span>&nbsp;<a href="javascript:oTransactionInput.showAccountEditor()" title="Rename or delete accounts.">Edit</a> <img src="<?=$sImage?>icon=info.png" class="clickicon" onload="UI.setHelptip(this, 'Select a Account', sAccountHelptip)"/></td>
				</tr>
				<tr>
					<td><font size='1' color='orange'><sup>*</sup></font>Category:</td>
					<td><span id="transaction/input/category"></span>&nbsp;<a href="javascript:oTransactionInput.showCategoryEditor()" title="Rename or delete categories.">Edit</a> <img src="<?=$sImage?>icon=info.png" class="clickicon" onload="UI.setHelptip(this, 'Select a Category', sCategoryHelptip)"/></td>
				</tr>
				<tr>
					<td>Notes:</td>
					<td><input name="notes" type="text" tabindex="10" class="textfield" style="width:250px"  maxlength="100"/></td>
				</tr>
				<tr>
					<td>Receipt:</td>
					<td id="transaction/input/uploader"></td>
				</tr>
				<tr>
					<td></td>
					<td height="30px"><input name="add" type="button" class="button" tabindex="11" value="Add Transaction" onclick="oTransactionInput.submit()" style="width:200px"/> <!--input name="clear" class="button" type="button" tabindex="12" value="Clear" onclick="oTransactionInput.clearForm()"/--></td>
				</tr>
				<tr>
					<td colspan="2"><font size='1' color='orange'><sup>*</sup>Required field</font></td>
				</tr>
			</table>
		</form>
	</fieldset>
</div>

<iframe id="transaction/input/uploaderframe" name="transaction/input/uploaderframe" style="display:none"></iframe>
