SELECT

  id   AS Id,
  filter AS Filter

FROM

  filters

WHERE

  user_id=%1
  
ORDER BY

  filter DESC