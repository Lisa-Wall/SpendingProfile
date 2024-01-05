SELECT

  categories.id              AS Id,
  categories.name            AS Name,
  categories.budget_amount   AS Amount,
  categories.budget_visible  AS Active,

  SUM(CASE debit WHEN '1'THEN amount ELSE 0 END) AS DebitTotal,
  SUM(CASE debit WHEN '0'THEN amount ELSE 0 END) AS CreditTotal

FROM

  categories left OUTER JOIN transactions ON transactions.category_id=categories.id AND

  (transactions.date >= '%2' AND transactions.date <= '%3')

WHERE

  categories.user_id=%1

GROUP BY categories.id

ORDER BY categories.name ASC
