SELECT

  count(id) AS Total

FROM

  transactions

WHERE

  (user_id=%1) AND (vendor_id=%2)