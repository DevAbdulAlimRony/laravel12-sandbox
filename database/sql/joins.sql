-- SQL Joins:
-- SQL Joins are used to combine data from two or more tables based on a related column. 
-- Inner join Returns only records with matching values in both tables.
-- Outer join Returns records even if there is no match in one or both tables.
-- Inner join Used when focusing strictly on relationships between tables, common in transactional query.
-- Outer join Common in reporting, analytics and data integration tasks.

-- Inner Join:
SELECT Employees.name, Departments.department_name
FROM Employees
INNER JOIN Departments ON Employees.department_id = Departments.id;

-- Outer Join:
-- Left Join (or Left Outer Join): Returns all records from the left table and the matched records from the right table. If there is no match, the result is NULL on the right side.
SELECT Employees.name, Departments.department_name
FROM Employees
LEFT JOIN Departments ON Employees.department_id = Departments.id;

-- Right Join (or Right Outer Join): Returns all records from the right table and the matched records from the left table. If there is no match, the result is NULL on the left side.
SELECT Employees.name, Departments.department_name
FROM Employees
RIGHT JOIN Departments ON Employees.department_id = Departments.id;

-- Full Join (or Full Outer Join): Returns all records when there is a match in either left or right table. If there is no match, the result is NULL on the side that does not have a match.
SELECT Employees.name, Departments.department_name
FROM Employees
FULL JOIN Departments ON Employees.department_id = Departments.id;

--Natural Join: Returns records that have matching values in both tables based on all columns with the same name.
-- It joins tables using common columns with the same name, The common column appears only once in the result.
SELECT Employees.name, Departments.department_name
FROM Employees
NATURAL JOIN Departments;

-- Cross Join: Returns the Cartesian product of the two tables, meaning it returns all possible combinations of records from both tables.
--  it returns every possible combination of rows from both tables. 
SELECT * FROM table1
CROSS JOIN table2;

-- Self Join: A self join is a regular join but the table is joined with itself. It is used to compare rows within the same table.
SELECT columns
FROM table AS alias1
JOIN table AS alias2 
ON alias1.column = alias2.related_column;

SELECT e.employee_name AS employee, m.employee_name AS manager
FROM GFGemployees AS e 
JOIN GFGemployees AS m ON e.manager_id = m.employee_id;

-- Update/Delete with Join:
UPDATE Employee e
JOIN Department d 
ON e.dept_id = d.dept_id
SET e.salary = e.salary + d.bonus;

-- Recursive Join: A recursive join is a self-referential join that allows you to query hierarchical data, such as organizational charts or bill of materials. It typically uses Common Table Expressions (CTEs) to achieve recursion.
WITH RECURSIVE EmployeeHierarchy AS (
    SELECT employee_id, manager_id, employee_name
    FROM Employees      
    WHERE manager_id IS NULL -- Start with top-level employees (e.g., CEOs)
    UNION ALL
    SELECT e.employee_id, e.manager_id, e.employee_name
    FROM Employees e
    JOIN EmployeeHierarchy eh ON e.manager_id = eh.employee_id -- Join to get subordinates
)
SELECT * FROM EmployeeHierarchy; -- Final query to retrieve the hierarchical data
-- Useful for organizational charts, bill of materials, and any hierarchical data structures.