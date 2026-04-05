-- Structured Query Language (SQL) is the standard language used to interact with relational databases. 
-- Widely supported across various database systems like MySQL oracle, PostgreSQL, SQL Server and many others.
-- Before running SQL queries you need to set up a database server like MySQL, PostgreSQL or SQLite. 
--  DBMS tools like MySQL and SQL Server have their own SQL engine and an interface where users can write and execute SQL queries.
-- DDL (Data Definition Language) commands are used to create, change, and delete database objects
-- A relational database can be updated with new data using data manipulation language (DML) statements.
-- Data retrieval instructions are written in the data query language (DQL), which is used to access relational databases.
-- Data Control language or DCL commands manage user access to the database by granting or revoking permissions.
-- Transaction Control Language or TCL commands manage transactions in the database, ensuring data integrity and consistency.

/* SQL keywords like SELECT and INSERT are not case-sensitive */

CREATE DATABASE test_db;
USE test_db;

CREATE TABLE greetings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message VARCHAR(255) NOT NULL
);

-- Data Types:
-- Numeric: BIGINT (Large integer numbers), INT (Standard integer), SMALLINT (-32,768 to 32,767), TINYINT (0 to 255), DECIMAL (Exact fixed-point numbers (e.g., for financial values), FLOAT, REAL.
-- String: CHAR (Fixed-length string- The maximum length of 8000 characters.), VARCHAR (Variable-length string), Varchar(max), TEXT (Large text data)
-- Unicode Character String (Unicode data types are used to store characters from any language):
-- NCHAR (Fixed-length Unicode string), NVARCHAR (Variable-length Unicode string), NTEXT (Large Unicode text data).
-- Date and Time: DATE (Date values- year, month, day), DATETIME (Date and time values),  TIME (Time values- hour, minute,second), TIMESTAMP (Date and time values with time zone information), YEAR (Year values).
-- Binary: BINARY (Fixed-length binary data), VARBINARY (Variable-length binary data), IMAGE (Large binary data).
-- Boolean: BOOLEAN (True or False values): 1 represents TRUE and 0 represents FALSE.
-- XML Data Type: Used to store XML data and manipulate XML structures in the database.
-- Spatial Data Type (Geometry): stores planar spatial data, such as points, lines, and polygons, in a database table. etc.