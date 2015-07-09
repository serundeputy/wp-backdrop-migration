<?php
/**
 * Migrate CMI WP Users to Backdrop CMS
 *
 *  start the connection to the database.
 */
 require_once('/Users/geoff/Sites/backdrop_cmi/core/includes/password.inc');
 require_once('/Users/geoff/Sites/backdrop_cmi/core/includes/bootstrap.inc');
 // function user_hash_password($password, $count_log2 = 0) {
 //   return _password_crypt('sha512', $password, _password_generate_salt($count_log2));
 // }
$user = 'root';
$pass = 'pass';
$dbh = new PDO('mysql:host=localhost;dbname=backdrop_cmi', $user, $pass);

/*
 * Data map
 *
 * 0: ID             => uid
 * 1: user_login     => name
 * 2: user_pass      => pass  // hardcode; 'pass'
 * // field_name = explode('-', user_nicename);
 * 3: user_nicename  => NOT USED
 * 4: user_email     => mail
 * 5: user_url       => NOT USED
 * 6: user_registered => 2012-09-29 20:15:51 // timestamp
 * 7: user_activation_key => NOT USED
 * 8: user_status => NOT USED
 * 9: display_name => field_name
 */
$data = file('../cmi-wp-users.csv', FILE_IGNORE_NEW_LINES);
foreach($data as $d) {
  $d = explode(',', $d);
  $d[6] = trim($d[6], '"');
  $time = strtotime($d[6]);
  $el_pass = user_hash_password('pass');
  print $el_pass . "\n";
  $sql = "insert into users (
    uid,
    name,
    pass,
    mail,
    created,
    status,
    timezone
    ) values(
        $d[0],
        $d[1],
        $el_pass,
        $d[4],
        $time,
        1,
        'America/New_York'
      )";
      $dbh->query($sql);

      $name_sql = "insert into field_data_field_name (
          entity_type,
          bundle,
          deleted,
          entity_id,
          revision_id,
          language,
          field_name_value
        ) values (
            'user',
            'user',
            0,
            $d[0],
            1,
            'und',
            $d[9]
          )";
          $dbh->query($name_sql);

      $rev_name_sql = "insert into field_revision_field_name (
          entity_type,
          bundle,
          deleted,
          entity_id,
          revision_id,
          language,
          field_name_value
        ) values (
            'user',
            'user',
            0,
            $d[0],
            1,
            'und',
            $d[9]
          )";
          $dbh->query($rev_name_sql);
}
print 'i did it.' . "\n";
