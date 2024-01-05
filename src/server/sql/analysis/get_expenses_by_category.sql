SELECT

	YearMonth,
	CategoryId,
	(SUM(Debit)-SUM(Credit)) AS Total,
	ABS(SUM(Debit)-SUM(Credit)) AS TotalABS

FROM
	(SELECT

		STR_TO_DATE(CONCAT(YEAR(transactions.date), '-', MONTH(transactions.date), '-1'), '%Y-%m-%d') AS YearMonth,
		(CASE debit WHEN '1' THEN amount ELSE 0 END) AS Debit,
		(CASE debit WHEN '0' THEN amount ELSE 0 END) AS Credit,
		IF(debit = '1' AND fixed = '1', amount, 0)   AS Fixed,
		IF(debit = '1' AND fixed = '0', amount, 0)   AS Variable,

		transactions.category_Id                     AS CategoryId

	FROM transactions

	WHERE

		(transactions.user_id = %1) AND
		(transactions.date >= '%2' AND transactions.date <= '%3') AND
		(transactions.category_id IN (%4))) AS Analysis

GROUP BY

	YearMonth, CategoryId

ORDER BY

	YearMonth ASC