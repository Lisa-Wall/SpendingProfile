UPDATE

  users

SET

  attempts = attempts+1,
  last_attempt = '%2'

WHERE

  email='%1'