SELECT

  BudgetTable.Id      AS Id,
  BudgetTable.Name    AS Name,
  BudgetTable.Active  AS Active,
  BudgetTable.Amount  AS Amount,

/*
  AverageTable.AverageDebit  AS AverageDebit,
  AverageTable.AverageCredit AS AverageCredit,
*/

  (AverageTable.AverageDebit - AverageTable.AverageCredit)/(%6) AS Average,
  
/*
  BudgetTable.TotalDebit  AS Debit,
  BudgetTable.TotalCredit AS Credit,
*/
  (BudgetTable.TotalDebit - BudgetTable.TotalCredit) AS Total,

  IF (Amount = 0, 0.0, ((BudgetTable.TotalDebit - BudgetTable.TotalCredit)/Amount*100)) AS Percent

FROM

(  SELECT

      categories.id              AS Id,
      categories.name            AS Name,
      categories.budget_amount   AS Amount,
      categories.budget_visible  AS Active,

      SUM(CASE debit WHEN '1'THEN amount ELSE 0 END) AS TotalDebit,
      SUM(CASE debit WHEN '0'THEN amount ELSE 0 END) AS TotalCredit

    FROM

      categories left outer join transactions on categories.id=transactions.category_id AND

      (transactions.date >= '%2' AND transactions.date <= '%3')

    WHERE

      (categories.user_id=%1) AND (categories.budget_visible IN (1, %7))

    GROUP BY categories.id

) BudgetTable

inner join

(  SELECT

      categories.id   AS Id,

      SUM(CASE debit WHEN '1'THEN amount ELSE 0 END) AS AverageDebit,
      SUM(CASE debit WHEN '0'THEN amount ELSE 0 END) AS AverageCredit

    FROM

      categories left OUTER JOIN transactions ON transactions.category_id=categories.id AND

      (transactions.date >= '%4' AND transactions.date <= '%5')

    WHERE

      (categories.user_id=%1) AND (categories.budget_visible IN (1, %7))

    GROUP BY categories.id

) AverageTable

on BudgetTable.Id = AverageTable.Id

ORDER BY %8 %9
