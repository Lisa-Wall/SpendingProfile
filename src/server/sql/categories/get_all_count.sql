SELECT

  categories.id       AS Id,
  categories.name     AS Name,
  count(category_id) AS Count

FROM

  categories LEFT OUTER JOIN transactions ON transactions.category_id = categories.id

WHERE

  categories.user_id=%1

GROUP BY categories.id

ORDER BY categories.name