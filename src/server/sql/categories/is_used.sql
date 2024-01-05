SELECT

  count(id) AS Total

FROM

  transactions

WHERE

  (user_id=%1) AND (category_id=%2)