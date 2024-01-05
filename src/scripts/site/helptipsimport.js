
var sGuessVendorHelptip =
"<div style='width:400px'>"+
"Let the program guess the most likely vendor based on patterns in your existing transactions.<br/><br/>" +
"Why? Because the vendors that appear in files imported from banks are often not very clear. Instead of a simple store name where you made a purchase, they add extra letters and numbers that make it hard to read.<br/><br/>" +
"ex: The imported vendor is ZELLERS STORE #246, but you would rather simply see Zellers.<br/><br/>" +
"Of course, you can change the vendor to a better name, and if the \"Guess Vendor\" option is turned on, the program will remember your preference the next time you do an import, and automatically show the vendor as you prefer to see it.<br/><br/>" +
"Note that the original vendor still appears in the drop down menu when you go to edit a vendor. So even if this option is turned on, you can still choose the original vendor if desired." +
"</div>";

var sFormatVendorHelptip =
"<div style='width:400px'>"+
"Format the vendor to be easier to read. Non-alphabetic characters are removed, and capitalization may be changed.<br/><br/>" +
"For example, if the original vendor is RED LOBSTER #81, it will be changed to Red Lobster";

var sFormatNotesHelptip =
"<div style='width:400px'>"+
"Format the notes to be easier to read. Non-alphabetic characters are removed, and capitalization may be changed.<br/><br/>" +
"For example, if the original vendor is MISCELLANEOUS PAYMENTS;48372DEERPARK, it will be changed to Miscellaneous Payments Deerpark";

var sImportTableHelptip =
"<div style='width:450px'>"+
"After uploading a bank file, preview the transactions in the table below." +
"<ul><li>Make sure the category, vendor, and account are correct for each transaction.</li>" +
"<li>Fill in any empty categories.</li>" +
"<li>Set the Fixed/Variable field and edit any other fields as required.</li></ul>" +
"Once you are satisfied with the list of transactions, save them to your account by clicking the button above the table.<br/><br/>" +
"Note that bank files do not contain values for some of the fields we use in Spending Profile. For example, there are no category, account, or fixed/variable values. We apply logic is to \"guess\" the most likely values for these fields,  based on the existing transactions in your account. This behaviour can be turned on or off through the options button on this page." +
"<div>";

var sClosestMatchHelptip = "<div style='width:200px'>These are the closest matches between the vendor in the imported file and your existing vendors.</div>";
var sOriginalValueHelptip = "<div style='width:200px'>This is the vendor exactly as it appears in the imported file. Choose this if you wish to use the vendor as-is with no changes or formatting.</div>"
var sFormattedValueHelptip = "<div style='width:200px'>This is the original vendor after formatting for readability.</div>";

var sBankFileHelptip =
"<div style='width:300px'><ol>"+
"<li>Log into your bank's website.</li>"+
"<li>Follow the links to download transactions.</li>"+
"<li>Pick the format for the download (we support Quicken, Microsoft Money, and Quick Books)</li>"+
"<li>Save the file to your computer and remember which folder it is in so that you can get back to it.</li>"+
"</ol></div>";
