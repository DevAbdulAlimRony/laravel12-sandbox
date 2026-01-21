<?php

namespace App\Http\Controllers;

class DBController{
    public function index(){
       //* DB Facade:
       // To write raw sql query we can use DB facade or DatabaseManager injcetion , $db->make()
       // DB::select(), DB::scalar(),  DB::selectResultSets, DB::insert(), DB::update(), DB::delete(), DB::statement(), DB::unprepared()
       // Using multiple connection: DB::connection('sqlite')->select(/* ... */);
       // PDO Instance: DB::connection()->getPdo();
       // Listening for Query Events: DB::listen()

       //* Transaction:
       // We can use DB::transaction(function(){}) for multiple transactions, If one failed whole transaction will fail.
       // don't need to worry about manually rolling back or committing while using the transaction method
       DB::transaction(function () {
          DB::update('update users set votes = 1');
          DB::delete('delete from posts');
       });

       //* Handling Deadlocks:
       DB::transaction(function () {
          DB::update('update users set votes = 1');
          DB::delete('delete from posts');
       },  attempts: 5);
       // If any deadlock happen, it will retry again, Maximum 5 times attempt.

       // If we want complete control over transaction commit and rollback, we can use:
       DB::beginTransaction();
       DB::rollBack();
       DB::commit();
       // The DB facade's transaction methods control the transactions for both the query builder and Eloquent ORM.
       
       //* Migrations: See users table migration file.
    }
}