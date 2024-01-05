SELECT

  vendors.id       AS Id,
  vendors.name     AS Name,
  count(vendor_id) AS Count

FROM

  vendors LEFT OUTER JOIN transactions ON transactions.vendor_id = vendors.id

WHERE

  vendors.user_id=%1

GROUP BY vendors.id

ORDER BY vendors.name