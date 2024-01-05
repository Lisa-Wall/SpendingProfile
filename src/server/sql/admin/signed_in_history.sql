SELECT

	SignedInOn,
	SignedInMonth,
	count(SignedInOn) AS Total

FROM
	(
	SELECT 
	
		CONCAT(YEAR(last_login), ', ', MONTHNAME(last_login)) AS SignedInOn,
		STR_TO_DATE(CONCAT(YEAR(last_login), '-', MONTH(last_login), '-01'), "%Y-%m-%d") AS SignedInMonth
		
	FROM users
	
	) AS History
	
GROUP BY

	SignedInOn
	
ORDER BY

	SignedInMonth DESC