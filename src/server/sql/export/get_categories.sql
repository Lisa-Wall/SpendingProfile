SELECT

  name            AS Name,
  budget_amount   AS BudgetAmount,
  budget_visible  AS BudgetActive

FROM

  categories

WHERE

  user_id=%1

ORDER BY name ASC