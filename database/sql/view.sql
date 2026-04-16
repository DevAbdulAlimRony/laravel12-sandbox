-- The SQL CREATE VIEW statement creates a virtual table based on a SELECT query.
-- It does not store data itself but displays data from one or more tables when accessed.

-- Creating:
CREATE VIEW view_name AS
SELECT column1, column2
FROM table_name
WHERE condition;

CREATE VIEW expensive_products AS
SELECT product_id, product_name, price
FROM products
WHERE price > 100;

CREATE VIEW employee_department_info AS
SELECT e.employee_id, e.first_name, e.last_name, d.department_name
FROM employees e
JOIN departments d ON e.department_id = d.department_id;

-- Updating:
UPDATE view1  
SET Marks=50 
where Roll_no in (3,5);

CREATE OR REPLACE VIEW view1 AS
SELECT Subject, SUM(Marks) AS TotalMarks
FROM Student
GROUP BY Subject;

CREATE OR REPLACE VIEW view1 AS
SELECT Name,
       (SELECT SUM(Marks) FROM Student s WHERE s.Subject = Student.Subject) AS TotalMarks
FROM Student;

-- Renaming:
EXEC sp_rename 'old_view_name', 'new_view_name';
EXEC sp_rename 'sales_report', 'monthly_sales_report';

-- Dropping:
-- Dropping a view removes only the view definition, not the actual data stored in the base tables. 
DROP VIEW employee_view;

-- In laravel, We can create a migration to create a view using the DB::statement method to execute raw SQL.
-- Then we can define a model for it.
-- So whenever we need to execute that complex query, we just call that model.

-- SQL Views are mostly about "Clean Code." * They hide massive, ugly queries behind a simple name.
-- They provide a single "source of truth." If you change how "Profit" is calculated, you change it in the View, and every part of your Laravel app is updated instantly.
-- A standard SQL View is usually not faster than a raw query. The database engine basically takes your View name and replaces it with the underlying query when you run it.
-- But materialized views can be faster because they store the results of the query, so you don't have to run the complex query every time. However, they require maintenance to keep the data up to date.

-- A materialized view stores the results of a query physically in the database for faster retrieval.
-- Needs manual or automatic refresh to update the stored data when underlying tables change.
-- Requires extra storage as it saves query results.
-- Involves maintenance cost due to periodic refreshes to keep data synchronized with base tables.
-- Not fully standardized; support and implementation vary across database systems, like mysql doesnt support it, postgres suppot it.
CREATE MATERIALIZED VIEW materialized_view_name  
BUILD [IMMEDIATE | DEFERRED]  
REFRESH [FAST | COMPLETE | FORCE]  
ON [COMMIT | DEMAND]  
AS  
SELECT column1, column2, ...  
FROM table_name  
WHERE condition;