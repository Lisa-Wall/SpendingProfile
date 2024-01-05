SELECT

  vendor_id         AS VendorId,
  account_id        AS AccountId,
  category_id       AS CategoryId,
  date              AS Date,
  debit             AS Debit,
  fixed             AS Fixed,
  amount            AS Amount,
  notes             AS Notes

FROM

  transactions

WHERE

  (transactions.user_id = %1) AND (transactions.id IN (%2))
