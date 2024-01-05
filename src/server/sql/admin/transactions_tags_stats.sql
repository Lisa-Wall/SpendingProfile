SELECT

	*
	
FROM

	(SELECT count(id) AS Transactions FROM transactions) AS TransactionCount,

	(SELECT count(id) AS Vendors FROM vendors) AS VendorCount,
	(SELECT count(Name) AS UniqueVendors FROM (SELECT name AS Name FROM vendors GROUP BY name) AS AllUniqueVendor) AS UniqueVendorCount,

	(SELECT count(id) AS Accounts FROM accounts) AS AccountCount,
	(SELECT count(Name) AS UniqueAccounts FROM (SELECT name AS Name FROM accounts GROUP BY name) AS AllUniqueAccount) AS UniqueAccountCount,

	(SELECT count(id) AS Categories FROM categories) AS CategoryCount,
	(SELECT count(Name) AS UniqueCategories FROM (SELECT name AS Name FROM categories GROUP BY name) AS AllUniqueCategory) AS UniqueCategoryCount
	
	