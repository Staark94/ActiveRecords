<?php
    require 'src/ActiveRecord/Eloquent.php';
    require 'src/User.php';

    // Set Connection to database
    \Staark\Support\ActiveRecord\Eloquent::setConnection('mysql', 'localhost', 'dashboard', 'root', '', '3306');

    // Initialize user Model
    $user = new User();

    // Set User Properties
    $user->id = 1;
    $user->emai = 'admin@exemple.com';

    // Get User Properties
    $user->id;
    $user->emai;

    // Store in database
    $user->save();
    $user->update();
    $user->insert();
    $user->delete();

    // Static Magic Methods
    User::find('id');
    User::findAll();
    User::findById('id');
    User::findByEmail('email');

    // For testing browse interface
