SELECT

	users.id AS Id,
	users.email AS Email,
	users.created_on AS CreatedOn,
	users.last_login AS LastSignIn,
	users.last_ip AS LastIp,
	
	Vendors,
	Accounts,
	Categories,
	Transactions
	
FROM

  users INNER JOIN
  
  ((SELECT count(vendors.id) AS Vendors, user_id AS UserId FROM vendors GROUP BY user_id) AS VendorsTable) ON VendorsTable.UserId=users.id LEFT OUTER JOIN
  ((SELECT count(accounts.id) AS Accounts, user_id AS UserId FROM accounts GROUP BY user_id) AS AccountsTable) ON AccountsTable.UserId=users.id LEFT OUTER JOIN
  ((SELECT count(categories.id) AS Categories, user_id AS UserId FROM categories GROUP BY user_id) AS CategoryTable) ON CategoryTable.UserId=users.id LEFT OUTER JOIN
  ((SELECT count(transactions.id) AS Transactions, user_id AS UserId FROM transactions GROUP BY user_id) AS TransactionTable) ON TransactionTable.UserId=users.id

	
ORDER BY

	%1 %2
	