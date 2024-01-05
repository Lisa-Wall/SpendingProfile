SELECT

	name AS Name,
	count(id) AS Count
	
FROM

	%1
	
GROUP BY 

	name
	
ORDER BY

	%2 %3