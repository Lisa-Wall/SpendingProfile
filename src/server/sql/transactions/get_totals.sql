SELECT

  SUM(CASE debit WHEN '1'THEN amount ELSE 0 END) AS Debit,
  SUM(CASE debit WHEN '0'THEN amount ELSE 0 END) AS Credit

FROM

  transactions INNER JOIN
  vendors ON transactions.vendor_id=vendors.id LEFT OUTER JOIN
  accounts ON transactions.account_id=accounts.id LEFT OUTER JOIN
  categories ON transactions.category_id=categories.id

WHERE

  (transactions.user_id = %1) AND
  (transactions.date >= '%2' AND transactions.date <= '%3')

  %4
