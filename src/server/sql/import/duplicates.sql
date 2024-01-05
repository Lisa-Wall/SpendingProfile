SELECT

  transactions.id     AS Id,
  transactions.fixed  AS Fixed,
  transactions.debit  AS Debit,
  transactions.amount AS Amount,
  transactions.notes  AS Notes,

  vendors.name        AS Vendor,
  accounts.name       AS Account,
  categories.name     AS Category

FROM

  transactions INNER JOIN
  vendors ON transactions.vendor_id=vendors.id LEFT OUTER JOIN
  accounts ON transactions.account_id=accounts.id LEFT OUTER JOIN
  categories ON transactions.category_id=categories.id

WHERE

  (transactions.user_id = %1) AND (transactions.debit = '%2') AND (transactions.amount = '%3') AND (transactions.date = '%4')

ORDER BY

  transactions.id ASC