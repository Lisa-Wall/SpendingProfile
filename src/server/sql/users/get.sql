SELECT

  id         AS Id,
  email      AS Email,
  currency   AS Currency,
  password   AS Password,
  preference AS Preference

FROM

  users

WHERE

  email='%1' AND password='%2' AND (attempts < 5 || (UNIX_TIMESTAMP('%3') - UNIX_TIMESTAMP(last_attempt)) > (60*60) )