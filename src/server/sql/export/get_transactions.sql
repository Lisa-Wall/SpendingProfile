SELECT

  vendors.name      AS Vendor,
  accounts.name     AS Account,
  categories.name   AS Category,
  date              AS Date,
  debit             AS Debit,
  fixed             AS Fixed,
  amount            AS Amount,
  notes             AS Notes

FROM

  transactions INNER JOIN
  vendors ON transactions.vendor_id=vendors.id LEFT OUTER JOIN
  accounts ON transactions.account_id=accounts.id LEFT OUTER JOIN
  categories ON transactions.category_id=categories.id

WHERE

  transactions.user_id = %1

ORDER BY transactions.id ASC