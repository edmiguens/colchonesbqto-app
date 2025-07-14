<?php
return [
  // Cambia aquí al entorno que quieras probar
  'modo'       => 'produccion',

  'desarrollo' => [ // SANDBOX
    'ClientID'     => 'AB4dLiT5xDU15Ih8F6HoFE12wuq6MfGRNJI4DLbH1ERJb4bbLB',
    'ClientSecret' => 'E711ETcTF4XLgrvxjBys6sD5BDer0YoijhMRceI5',
    'RedirectURI'  => 'http://localhost:8080/colchonesbqto/callback.php',
    'baseUrl'      => 'Development'
  ],

  'produccion' => [ // LIVE/QBO REAL
    'ClientID'     => 'ABCdF0BQFmcaxBa9KI9wtNRq9GbIMYbB2cWNA1UAvEa8t6hfmz',
    'ClientSecret' => 't1IRhPgphog6kZqAtH7TA3aXGAjwh8ZIpZHfQaZb',
    'RedirectURI'  => 'https://colchonesbqto-app.onrender.com/callback.php',
    'baseUrl'      => 'Production'
  ]
];