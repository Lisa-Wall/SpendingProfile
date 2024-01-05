SELECT

	*
	
FROM

	(SELECT count(id) AS SignedIn FROM users WHERE last_login >= '%1') AS SignedInTable,
	(SELECT count(id) AS NewAccounts FROM users WHERE created_on >= '%1') AS NewAccountsTable,
	(SELECT count(id) AS Transactions FROM transactions WHERE entered_on >= '%1') AS TransactionsTable
	