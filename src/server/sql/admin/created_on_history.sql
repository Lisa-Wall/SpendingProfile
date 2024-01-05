SELECT

	CreatedOn,
	CreatedOnMonth,
	count(CreatedOn) AS Total

FROM
	(
	SELECT 
	
		CONCAT(YEAR(created_on), ', ', MONTHNAME(created_on)) AS CreatedOn,
		STR_TO_DATE(CONCAT(YEAR(created_on), '-', MONTH(created_on), '-01'), "%Y-%m-%d") AS CreatedOnMonth
		
	FROM users
	
	) AS History
	
GROUP BY

	CreatedOn
	
ORDER BY

	CreatedOnMonth DESC