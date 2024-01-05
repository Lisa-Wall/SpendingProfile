SELECT

  transactions.id   AS Id,
  vendors.name      AS Vendor,
  accounts.name     AS Account,
  categories.name   AS Category,
  date              AS Date,
  debit             AS Debit,
  fixed             AS Fixed,
  amount            AS Amount,
  notes             AS Notes,

  (CASE WHEN (receipt IS NULL) THEN 0 ELSE 1 END) AS Receipt

FROM

  transactions INNER JOIN
  vendors ON transactions.vendor_id=vendors.id LEFT OUTER JOIN
  accounts ON transactions.account_id=accounts.id LEFT OUTER JOIN
  categories ON transactions.category_id=categories.id

WHERE

  (transactions.user_id = %1) AND
  (transactions.date >= '%4' AND transactions.date <= '%5')

  %6

ORDER BY %2 %3