-- 1. Добавление новых пользователей
INSERT INTO users (name, email, gender, register_date, occupation_id)
VALUES 
('Марьин Михаил', 'sliznyaaak@gmail.com', 'male', date('now'), 
    (SELECT id FROM occupations WHERE name = 'student')),
('Лемясев Роман', 'l3myas3v.r@aboba.com', 'male', date('now'), 
    (SELECT id FROM occupations WHERE name = 'student')),
('Лузин Максим', 'mask0n41k@yandex.ru', 'male', date('now'), 
    (SELECT id FROM occupations WHERE name = 'student')),
('Орлов Вадим', 'or3lvadya@aboba.com', 'male', date('now'), 
    (SELECT id FROM occupations WHERE name = 'student')),
('Пьянов Роман', '@scryyge@mail.com', 'male', date('now'), 
    (SELECT id FROM occupations WHERE name = 'student'));


INSERT INTO movies (title, year)
VALUES 
('F1', 2025),
('Тёмный рыцарь', 2008),
('Зелёная миля', 1999);


INSERT INTO movies_genres (movie_id, genre_id)
VALUES 
-- F1: Thriller, Drama, Sport
((SELECT id FROM movies WHERE title = 'F1'), 
 (SELECT id FROM genres WHERE name = 'Thriller')),
((SELECT id FROM movies WHERE title = 'F1'), 
 (SELECT id FROM genres WHERE name = 'Drama')),
((SELECT id FROM movies WHERE title = 'F1'), 
 (SELECT id FROM genres WHERE name = 'Sport')),

-- Тёмный рыцарь: Fantasy, Thriller, Drama
((SELECT id FROM movies WHERE title = 'Тёмный рыцарь'), 
 (SELECT id FROM genres WHERE name = 'Fantasy')),
((SELECT id FROM movies WHERE title = 'Тёмный рыцарь'), 
 (SELECT id FROM genres WHERE name = 'Thriller')),
((SELECT id FROM movies WHERE title = 'Тёмный рыцарь'), 
 (SELECT id FROM genres WHERE name = 'Drama')),

-- Зелёная миля: Drama, Fantasy, Crime
((SELECT id FROM movies WHERE title = 'Зелёная миля'), 
 (SELECT id FROM genres WHERE name = 'Drama')),
((SELECT id FROM movies WHERE title = 'Зелёная миля'), 
 (SELECT id FROM genres WHERE name = 'Fantasy'));
((SELECT id FROM movies WHERE title = 'Зелёная миля'), 
 (SELECT id FROM genres WHERE name = 'Crime'));

-- 4. Добавление отзывов
INSERT INTO ratings (user_id, movie_id, rating, timestamp)
VALUES 
((SELECT id FROM users WHERE email = 'sliznyaaak@gmail.com'), 
 (SELECT id FROM movies WHERE title = 'F1'), 4.2, strftime('%s', 'now')),
((SELECT id FROM users WHERE email = 'sliznyaaak@gmail.com'), 
 (SELECT id FROM movies WHERE title = 'Тёмный рыцарь'), 4.5, strftime('%s', 'now')),
((SELECT id FROM users WHERE email = 'sliznyaaak@gmail.com'), 
 (SELECT id FROM movies WHERE title = 'Зелёная миля'), 5.0, strftime('%s', 'now'));
