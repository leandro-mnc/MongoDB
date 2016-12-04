<?php

require 'MongoDB.class.php';
require 'UserModel.php';

$user = new Models\UserModel();


//$user->removeAll();
//$user->updateUser();

$user->insertUser();

$users = $user->getAllUsers();
echo '<pre>';
print_r($users);
echo '</pre>';
