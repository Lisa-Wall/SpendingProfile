SELECT

  referrals.email AS Email,
  referrals.referred_on AS ReferredOn,

  users.created_on AS CreatedOn,
  users.last_login AS LastLogin


FROM

  referrals left outer join users on referrals.email = users.email

WHERE

  referrals.user_id=%1

ORDER BY

  referrals.referred_on ASC