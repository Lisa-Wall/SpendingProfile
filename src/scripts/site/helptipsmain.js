
var sDelimiter = ":";

var sVendorHelptip =
"<div style='width:400px'>" +
"Specify a vendor for the trasaction. Which store, company, or person did you buy from? If it is not in the list, type a new one in the form field." +
"<blockquote>ex: WalMart, Starbucks, Joe the Butcher</blockquote>" +
"You can create sub-categories of vendor using the '" + sDelimiter + "' symbol. For example, if you buy coffe from two different branches of the same franchise, you could represent them as follows:" +
"<blockquote>Coffee Shop " + sDelimiter + " Centertown<br/>Coffee Shop " + sDelimiter + " By My Workplace</blockquote>" +
"You only need to create a vendor once. After that it will appear in the list and you can select it.<br/><br/>" +
"Tip: Do not confuse vendors with categries. Think of the vendor as the merchant who sold you the item, and the category as the class of transaction, such as food or clothing.<br/><br/>" +
"Tip: If you are entering income, you can enter the company or person who paid you as the vendor." +
"</div>";

var sAccountHelptip =
"<div style='width:400px'>" +
"Specify which account was used for the transaction. If it is not in the list, type a new one in the form field." +
"<blockquote>ex: Visa, Line of Credit, My Piggy Bank</blockquote>" +
"Accounts can be sub-divided with the '&gt;' sybmol. For example:" +
"<blockquote>Credit Card " + sDelimiter + " Visa<br/>Credit Card " + sDelimiter + " Mastercard</blockquote>" +
"Further sub-division is possible, such as" +
"<blockquote>Credit Card " + sDelimiter + " Visa " + sDelimiter + " Royal Bank<br/>Credit Card " + sDelimiter + " Visa " + sDelimiter + " Scotiabank</blockquote>" +
"You only need to create an account once. After that it will appear in the list and you can select it." +
"</div>";

var sCategoryHelptip =
"<div style='width:400px'>" +
"Specify a category for the transaction. If it is not in the list, type a new one in the form field.<br/><br/>" +
"Create sub-categories using the '" + sDelimiter + "' symbol. For example:" +
"<blockquote>ex: Car " + sDelimiter + " Insurance</blockquote>" +
"This creates a category called Car with a subcategory Insurance inside it.<br/><br/>" +
"You only need to create a category once. After that it will appear in the list and you can select it." +
"</div>";

var sTransactionTableHelptip =
"<div style='width:400px'>" +
"Click on values in the transaction list to edit them.<br/><br/>" +
"You can sort the transaction list by any column by clicking on the column header. For example, click on the \"Amount\" column header to sort the list from largest to smallest transaction, or vice versa.<br/><br/>" +
"Tip: If you accidentally enter a transaction as an expense when it should be income, edit the amount value in the table and add a plus symbol (+) in front of it. This will switch it to income." +
"</div>";

var sSearchHelptip =
"<div style='overflow:auto;height:400px;width:425px'>"+
"The search field is a powerful way to view a specific set of transactions in your account. Type a search term in the search box to search through all transactions in the current time period." +
"<blockquote>ex: coffee</blockquote>" +
"This will find all transactions that have the word coffee in any field. Note that only the transactions in the currently-selected time period will be searched. If you wish to search a different time period, such as an entire year, first set the period using the calendar at the top of the page, and then perform the search.<br/><br/>" +
"You can also search within a specific field. Add the field as a prefix before the search term, like this:" +
"<blockquote>Vendor=Starbucks</blockquote>" +
"This finds all transactions where the vendor is Starbucks. Transactions that contain the word Starbucks in other fields will be ignored.<br/><br/>" +
"You can have multiple search terms, such as" +
"<blockquote>Vendor=Starbucks and Amount&gt;10.00</blockquote>" +
"This finds all transactions where the vendor is Starbucks and the amount is greater than 10.00. Both statements must be true in order for the transaction to be included in the results.<br/><br/>" +
"You can also combine search terms without the word \"and\" between them:" +
"<blockquote>Vendor=Starbucks Amount>10.00</blockquote>" +
"This has a slightly different meaning. It finds all transactions where the vendor is Starbucks OR the amount is greater than 10.00. Each statement is evaluated individually, and only one or the other needs to be true for the transaction to be included in the search results." +
"</div>";

var sFixVariableHelptip = "<div style='width:400px'>The transaction type can be either fixed or variable. Fixed transactions repeat regularly, such as monthly rental payments. All other expenses are variable.</div>";