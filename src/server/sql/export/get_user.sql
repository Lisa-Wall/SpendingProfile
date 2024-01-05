SELECT

  email      AS Email,
  currency   AS Currency,
  password   AS Password,
  first_name AS FirstName,
  last_name  AS LastName,
  location   AS Location,
  preference AS Preference,
  created_on AS CreatedOn,
  last_ip    AS LastIP,
  last_login AS LastLogin

FROM

  users

WHERE

  id = %1