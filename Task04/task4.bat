#!/bin/bash
chcp 65001

sqlite3 movies_rating.db < db_init.sql

echo "1. Найти все пары пользователей, оценивших один и тот же фильм. Устранить дубликаты, проверить отсутствие пар с самим собой. Для каждой пары должны быть указаны имена пользователей и название фильма, который они ценили. В списке оставить первые 100 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo \
"SELECT
  u1.name AS user1,
  u2.name AS user2,
  m.title AS movie
FROM ratings AS r1
JOIN ratings AS r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id
JOIN users AS u1 ON r1.user_id = u1.id
JOIN users AS u2 ON r2.user_id = u2.id
JOIN movies AS m ON r1.movie_id = m.id
LIMIT 100;"

echo "2. Найти 10 самых свежих оценок от разных пользователей, вывести названия фильмов, имена пользователей, оценку, дату отзыва в формате ГГГГ-ММ-ДД."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo \
"SELECT
  m.title,
  u.name,
  rr.rating,
  date(rr.timestamp, 'unixepoch') AS review_date
FROM
(
  SELECT
    r.user_id,
    r.movie_id,
    r.rating,
    r.timestamp,
    ROW_NUMBER() OVER(PARTITION BY r.user_id ORDER BY r.timestamp DESC) as rn
  FROM ratings r
) AS rr
JOIN movies m ON rr.movie_id = m.id
JOIN users u ON rr.user_id = u.id
WHERE rr.rn = 1
ORDER BY rr.timestamp DESC
LIMIT 10;"

echo "3. Вывести в одном списке все фильмы с максимальным средним рейтингом и все фильмы с минимальным средним рейтингом. Общий список отсортировать по году выпуска и названию фильма. В зависимости от рейтинга в колонке Рекомендуем для фильмов должно быть написано Да или Нет"
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo \
"WITH movie_ratings AS (
  SELECT
    m.title,
    AVG(r.rating) AS rating
  FROM movies AS m
  JOIN ratings AS r
    ON r.movie_id = m.id
  GROUP BY
    m.id
  ORDER BY m.year, m.title
),
ratings_with_overall_stats AS (
  SELECT
    title,
    rating,
    MAX(rating) OVER () AS max_overall_rating,
    MIN(rating) OVER () AS min_overall_rating
  FROM movie_ratings
)
SELECT
  title,
  CASE
    WHEN rating = max_overall_rating
    THEN 'Да️'
    ELSE 'Нет'
  END AS 'Рекомендуем'
FROM ratings_with_overall_stats
WHERE rating = max_overall_rating OR rating = min_overall_rating
ORDER BY rating DESC;"

echo "4. Вычислить количество оценок и среднюю оценку, которую дали фильмам пользователи-женщины в период с 2010 по 2012 год."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo \
"SELECT
  m.title,
  count(r.rating) as rating_count,
  avg(r.rating) as avg_rating
FROM ratings AS r
JOIN users AS u ON r.user_id = u.id
JOIN movies AS m ON r.movie_id = m.id
WHERE
  u.gender = 'female' AND
  strftime('%Y', r.timestamp, 'unixepoch') BETWEEN '2010' AND '2012'
GROUP BY m.id, m.title;"

echo "5. Составить список фильмов с указанием их средней оценки и места в рейтинге по средней оценке. Полученный список отсортировать по году выпуска и названию фильмов. В списке оставить первые 20 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo \
"SELECT
  m.title AS 'Название',
  t.avg_rating AS 'Средняя оценка',
  ROW_NUMBER() OVER (ORDER BY t.avg_rating DESC) AS 'Место'
FROM (
  SELECT
    r.movie_id,
    AVG(r.rating) AS avg_rating
  FROM ratings r
  GROUP BY r.movie_id
) t
JOIN movies m ON m.id = t.movie_id
ORDER BY m.year, m.title
LIMIT 20;
"

echo "6. Вывести список из 10 последних зарегистрированных пользователей в формате Фамилия Имя|Дата регистрации (сначала фамилия, потом имя)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo \
"SELECT
TRIM(SUBSTR(name, INSTR(name, ' ') + 1))
|| ' '
|| TRIM(SUBSTR(name, 1, INSTR(name, ' ') - 1)) AS full_name
, register_date
FROM users
ORDER BY register_date DESC
LIMIT 10;"

echo "7.  С помощью рекурсивного CTE составить таблицу умножения для чисел от 1 до 10."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo \
"WITH RECURSIVE multiplication_table(a, b) AS (
  SELECT 1, 1
  UNION ALL
  SELECT
    a,
    b + 1
  FROM multiplication_table
  WHERE b < 10
  UNION ALL
  SELECT
    a + 1,
    1
  FROM multiplication_table
  WHERE b = 10 AND a < 10
)
SELECT a, b, (a * b)
FROM multiplication_table;"

echo "8. С помощью рекурсивного CTE выделить все жанры фильмов, имеющиеся в таблице movies (каждый жанр в отдельной строке)"
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo \
"WITH RECURSIVE genres_cte(movie_id, genre, remaining_genres) AS (
  SELECT
    id,
    '',
    genres || '|'
  FROM movies
  UNION ALL
  SELECT
    movie_id,
    SUBSTR(remaining_genres, 1, INSTR(remaining_genres, '|') - 1),
    SUBSTR(remaining_genres, INSTR(remaining_genres, '|') + 1)
  FROM genres_cte
  WHERE remaining_genres != ''
)
SELECT DISTINCT genre
FROM genres_cte
WHERE genre != '' AND genre != '(no genres listed)'
ORDER BY genre;"
