SELECT

  *,
  (Debit - Credit) AS Total

FROM

(SELECT

  goals.id          AS Id,
  goals.category_id AS CategoryId,
  goals.name        AS Name,
  goals.date_start  AS StartDate,
  goals.date_mature AS MatureDate,
  goals.amount      AS Amount,
  goals.notes       AS Notes,
  categories.name   AS Category,

  SUM(CASE transactions.debit WHEN '1'THEN transactions.amount ELSE 0 END) AS Debit,
  SUM(CASE transactions.debit WHEN '0'THEN transactions.amount ELSE 0 END) AS Credit

FROM

  goals inner join categories on (goals.category_id=categories.id)  left outer join transactions on (transactions.category_id=goals.category_id) AND

  (transactions.date >= goals.date_start AND transactions.date <= goals.date_mature)

WHERE

  goals.user_id=1

GROUP BY goals.category_id) GoalsTable