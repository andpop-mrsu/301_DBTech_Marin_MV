CREATE TABLE CarCategory (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE Employee (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    hire_date DATE NOT NULL,
    dismissal_date DATE NULL,
    salary_percentage DECIMAL(5,2) NOT NULL DEFAULT 25.00,
    is_active BOOLEAN NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CHECK (salary_percentage BETWEEN 0 AND 100),
    CHECK (dismissal_date IS NULL OR dismissal_date > hire_date)
);


CREATE TABLE Service (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    duration_minutes INTEGER NOT NULL DEFAULT 60,
    base_price DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CHECK (duration_minutes > 0),
    CHECK (base_price >= 0)
);


CREATE TABLE ServicePrice (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    service_id INTEGER NOT NULL,
    car_category_id INTEGER NOT NULL,
    actual_price DECIMAL(10,2) NOT NULL,
    effective_date DATE NOT NULL DEFAULT CURRENT_DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES Service(id) ON DELETE CASCADE,
    FOREIGN KEY (car_category_id) REFERENCES CarCategory(id) ON DELETE CASCADE,
    CHECK (actual_price >= 0),
    UNIQUE(service_id, car_category_id, effective_date)
);

CREATE TABLE EmployeeSpecialization (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES Employee(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES Service(id) ON DELETE CASCADE,
    UNIQUE(employee_id, service_id)
);


CREATE TABLE Appointment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    car_category_id INTEGER NOT NULL,
    client_name VARCHAR(100) NOT NULL,
    client_phone VARCHAR(20),
    car_model VARCHAR(100) NOT NULL,
    car_license_plate VARCHAR(20),
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'scheduled', 
    scheduled_duration INTEGER NOT NULL,
    scheduled_price DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES Employee(id),
    FOREIGN KEY (service_id) REFERENCES Service(id),
    FOREIGN KEY (car_category_id) REFERENCES CarCategory(id),
    CHECK (status IN ('scheduled', 'completed', 'cancelled', 'no_show')),
    CHECK (scheduled_duration > 0),
    CHECK (scheduled_price >= 0)
);


CREATE TABLE WorkRecord (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER NOT NULL,
    employee_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    car_category_id INTEGER NOT NULL,
    actual_duration INTEGER NOT NULL,
    actual_price DECIMAL(10,2) NOT NULL,
    work_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES Appointment(id),
    FOREIGN KEY (employee_id) REFERENCES Employee(id),
    FOREIGN KEY (service_id) REFERENCES Service(id),
    FOREIGN KEY (car_category_id) REFERENCES CarCategory(id),
    CHECK (actual_duration > 0),
    CHECK (actual_price >= 0),
    CHECK (end_time > start_time)
);




INSERT INTO CarCategory (name, description) VALUES
('Легковые', 'Легковые автомобили всех классов'),
('Внедорожники', 'Кроссоверы и внедорожники'),
('Коммерческие', 'Грузовики и коммерческий транспорт'),
('Мотоциклы', 'Мотоциклы и скутеры');


INSERT INTO Employee (first_name, last_name, phone, email, hire_date, salary_percentage) VALUES
('Иван', 'Петров', '+7-912-345-67-89', 'ivan@sto.ru', '2023-01-15', 30.00),
('Анна', 'Сидорова', '+7-912-345-67-90', 'anna@sto.ru', '2023-02-20', 28.00),
('Сергей', 'Козлов', '+7-912-345-67-91', 'sergey@sto.ru', '2023-03-10', 32.00),
('Мария', 'Иванова', '+7-912-345-67-92', 'maria@sto.ru', '2022-11-05', 26.00);


INSERT INTO Employee (first_name, last_name, phone, email, hire_date, dismissal_date, salary_percentage, is_active) VALUES
('Дмитрий', 'Смирнов', '+7-912-345-67-93', 'dmitry@sto.ru', '2022-08-01', '2023-06-30', 25.00, 0);


INSERT INTO Service (name, description, duration_minutes, base_price) VALUES
('Замена масла', 'Полная замена моторного масла и фильтра', 45, 1500.00),
('Замена тормозных колодок', 'Замена передних или задних тормозных колодок', 90, 3000.00),
('Развал-схождение', 'Регулировка углов установки колес', 60, 2500.00),
('Диагностика двигателя', 'Компьютерная диагностика двигателя', 30, 1200.00),
('Замена свечей зажигания', 'Замена комплекта свечей зажигания', 40, 1800.00);


INSERT INTO ServicePrice (service_id, car_category_id, actual_price) VALUES
(1, 1, 1500.00), (1, 2, 1800.00), (1, 3, 2200.00), (1, 4, 800.00),
(2, 1, 3000.00), (2, 2, 3500.00), (2, 3, 4500.00), (2, 4, 1200.00),
(3, 1, 2500.00), (3, 2, 2800.00), (3, 3, 3200.00), (3, 4, 1500.00),
(4, 1, 1200.00), (4, 2, 1400.00), (4, 3, 1600.00), (4, 4, 900.00),
(5, 1, 1800.00), (5, 2, 2000.00), (5, 3, 2400.00), (5, 4, 1000.00);


INSERT INTO EmployeeSpecialization (employee_id, service_id) VALUES
(1, 1), (1, 2), (1, 5),  
(2, 1), (2, 4), (2, 5),  
(3, 2), (3, 3),          
(4, 1), (4, 4), (4, 5),  
(5, 1), (5, 2);          


INSERT INTO Appointment (employee_id, service_id, car_category_id, client_name, client_phone, car_model, car_license_plate, appointment_date, appointment_time, scheduled_duration, scheduled_price) VALUES
(1, 1, 1, 'Алексей Воронов', '+7-923-111-22-33', 'Toyota Camry', 'А123БВ777', '2024-01-15', '09:00', 45, 1500.00),
(2, 4, 2, 'Ольга Крылова', '+7-923-111-22-34', 'Honda CR-V', 'Б456ГД777', '2024-01-15', '10:00', 30, 1400.00),
(3, 2, 1, 'Павел Орлов', '+7-923-111-22-35', 'Lada Vesta', 'В789ЕЖ777', '2024-01-15', '11:00', 90, 3000.00),
(4, 5, 1, 'Екатерина Соколова', '+7-923-111-22-36', 'Kia Rio', 'Г012ИК777', '2024-01-16', '09:30', 40, 1800.00);


INSERT INTO WorkRecord (appointment_id, employee_id, service_id, car_category_id, actual_duration, actual_price, work_date, start_time, end_time, notes) VALUES
(1, 1, 1, 1, 40, 1500.00, '2024-01-15', '09:00', '09:40', 'Замена синтетического масла 5W-30'),
(2, 2, 4, 2, 35, 1400.00, '2024-01-15', '10:00', '10:35', 'Диагностика показала нормальные параметры'),
(3, 3, 2, 1, 85, 3000.00, '2024-01-15', '11:00', '12:25', 'Замена передних тормозных колодок');


CREATE INDEX idx_appointment_date ON Appointment(appointment_date, appointment_time);
CREATE INDEX idx_appointment_employee ON Appointment(employee_id, appointment_date);
CREATE INDEX idx_workrecord_date ON WorkRecord(work_date);
CREATE INDEX idx_workrecord_employee ON WorkRecord(employee_id, work_date);
CREATE INDEX idx_employee_active ON Employee(is_active);
CREATE INDEX idx_service_price ON ServicePrice(service_id, car_category_id);