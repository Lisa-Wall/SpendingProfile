UPDATE

  goals

SET

  name        = '%3',
  date_start  = '%4',
  date_mature = '%5',
  amount      = '%6',
  category_id = '%7',
  notes       = '%8'

WHERE

  user_id = %1 AND id = %2