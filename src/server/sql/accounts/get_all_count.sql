SELECT

  accounts.id       AS Id,
  accounts.name     AS Name,
  count(account_id) AS Count

FROM

  accounts LEFT OUTER JOIN transactions ON transactions.account_id = accounts.id

WHERE

  accounts.user_id=%1

GROUP BY accounts.id

ORDER BY accounts.name