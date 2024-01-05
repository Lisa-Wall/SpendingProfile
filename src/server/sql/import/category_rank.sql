SELECT

  *

FROM

(SELECT

    transactions.fixed AS Fixed,
    transactions.notes AS Notes,

    vendors.name AS Vendor,
    accounts.name AS Account,
    categories.name AS Category,

    COUNT(vendors.name) AS Rank


  FROM

    transactions INNER JOIN
    vendors ON transactions.vendor_id=vendors.id LEFT OUTER JOIN
    accounts ON transactions.account_id=accounts.id LEFT OUTER JOIN
    categories ON transactions.category_id=categories.id

  WHERE

    (transactions.user_id = %1) AND

    %2

  GROUP BY

    vendors.name

) AS CategoriesRanks

ORDER BY

  Rank DESC