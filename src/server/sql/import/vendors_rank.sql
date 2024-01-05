SELECT

  name AS Name,
  '0'  AS Rank

FROM

  vendors

WHERE

  (user_id=%1) AND

  %2