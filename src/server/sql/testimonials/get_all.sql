SELECT
  Users.first_name         AS FirstName,
  Users.location           AS Location,
  Testimonials.testimonial AS Testimonial

FROM

  users AS Users,
  testimonials AS Testimonials

WHERE

  Users.id=Testimonials.user_id
