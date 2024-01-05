SELECT

  id   AS Id,
  name AS Name

FROM

  vendors

WHERE

  user_id=%1

ORDER BY name ASC