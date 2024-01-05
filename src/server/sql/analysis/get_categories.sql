SELECT

	id AS Id,
	name AS Name

FROM

	categories

WHERE

	(user_id = %1) AND (id IN (%2))