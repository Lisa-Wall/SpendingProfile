SELECT

	Total,
	SignedIn,
	(Total - SignedIn) AS NeverSignedIn,
	(SignedIn/Total*100) AS SignedInPercent,
	((Total - SignedIn)/Total*100) AS NeverSignedInPercent

FROM

	(SELECT count(id) AS Total FROM users) AS Total,
	(SELECT count(id) AS SignedIn FROM users WHERE last_login IS NOT NULL) AS SignedIn
