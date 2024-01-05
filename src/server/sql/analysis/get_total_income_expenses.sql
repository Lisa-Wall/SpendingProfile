SELECT

	YearMonth,
	SUM(Debit) AS TotalExpenses,
	SUM(Fixed) AS FixedExpenses,
	SUM(Variable) AS VariableExpenses,
	SUM(-1 * Credit) AS TotalIncome,
	SUM(Credit) AS TotalIncomeABS

FROM
	(SELECT

		STR_TO_DATE(CONCAT(YEAR(transactions.date), '-', MONTH(transactions.date), '-1'), '%Y-%m-%d') AS YearMonth,
		(CASE debit WHEN '1' THEN amount ELSE 0 END) AS Debit,
		(CASE debit WHEN '0' THEN amount ELSE 0 END) AS Credit,
		IF(debit = '1' AND fixed = '1', amount, 0)   AS Fixed,
		IF(debit = '1' AND fixed = '0', amount, 0)   AS Variable

	FROM transactions

	WHERE

		(transactions.user_id = %1) AND
		(transactions.date >= '%2' AND transactions.date <= '%3')) AS Analysis

GROUP BY

	YearMonth

ORDER BY

	YearMonth ASC