<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("JWT_SECRET", $_ENV["JWT_SECRET"]);
