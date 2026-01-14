<?php
// Returns fully qualified class:

app_path() // full app directory
app_path('Http/Controllers/Controller.php'); // full path of that namespace.

base_path();
base_path('vendor/bin');

config_path();
config_path('app.php');

database_path();
database_path('factories/UserFactory.php');

lang_path();
lang_path('en/messages.php');

public_path();
public_path('css/app.css');

resource_path();
resource_path('sass/app.scss');

storage_path();
storage_path('app/file.txt');