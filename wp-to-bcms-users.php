<?php
/**
 * Migrate CMI WP Users to Backdrop CMS
 *
 *  start the connection to the database.
 */
/*
 * --run : run a migration
 * --rollback : rollback a migration
 * --help : get possibilities
 */
require_once('wp-to-bcms-posts.php');
if(count($argv) > 1) {
  $my_arg = $argv[1];
  $type = $argv[2];
  if ($my_arg == '--run') {
    if ($type == '--users') {
      run_migrate_users();
    }
    elseif ($type == '--posts') {
      run_migrate_posts();
      //geoff();
    }
  }
  elseif($my_arg == '--rollback') {
    if ($type == '--users') {
      run_migrate_users_rollback();
    }
    elseif ($type == '--posts') {
      run_migrate_posts_rollback();
    }
  }
  else {
    run_migrate_help();
  }
}
else {
  run_migrate_help();
}

function run_migrate_users() {
  $user = 'root';
  $pass = 'pass';
  $wpdb = new PDO('mysql:host=localhost;dbname=cmi_wp', $user, $pass);
  $bddb = new PDO('mysql:host=localhost;dbname=backdrop_cmi', $user, $pass);

  /*
   * Data map
   *
   * 0: ID             => uid
   * 1: user_login     => name
   * 2: user_pass      => pass  // hardcode; 'pass'
   * 3: user_nicename  => NOT USED
   * 4: user_email     => mail
   * 5: user_url       => NOT USED
   * 6: user_registered => 2012-09-29 20:15:51 // timestamp
   * 7: user_activation_key => NOT USED
   * 8: user_status => NOT USED
   * 9: display_name => field_name
   */
// get the data from the WP database.
  $sql = $wpdb->prepare("select * from wp_users where 1");
  $sql->execute();
  $result = $sql->fetchAll();

// insert statement.
  $row_query = $bddb->prepare(
    "insert into users (
        uid,
        name,
        pass,
        mail,
        signature_format,
        created,
        status,
        timezone,
        init
      ) values(
        :uid,
        :my_name,
        :pass,
        :mail,
        :signature_format,
        :created,
        :status,
        :timezone,
        :init
      )"
  );
  $field_name_query = $bddb->prepare(
    "insert into field_data_field_name (
        entity_type,
        bundle,
        deleted,
        entity_id,
        revision_id,
        language,
        delta,
        field_name_value,
        field_name_format,
      )
      values(
        ':entity_type',
        ':bundle',
        ':deleted',
        ':entity_id',
        ':revision_id',
        ':language',
        ':delta',
        ':field_name_value',
        ':field_name_format',
      )"
  );
  foreach($result as $r) {
    $uid = $r['ID'] + 1;
    $name = $r['user_login'];
    $pass = $r['user_pass'];
    $mail = $r['user_email'];
    $signature_format = 'filtered_html';
    //$r['user_registered'] = trim($r['user_registered'], '"');
    $created = strtotime($r['user_registered']);
    $status = 1;
    $time_zone = 'America/New_York';
    $init = $r['user_email'];
    $display_name = $r['display_name'];

    $binds = array(
      ':uid' => $uid,
      ':my_name' => $name,
      ':pass' => $pass,
      ':mail' => $mail,
      ':signature_format' => $signature_format,
      ':created' => $created,
      ':status' => $status,
      ':timezone' => $time_zone,
      ':init' => $init,
    );
    $row_query->execute($binds);
    print $r['ID'] . " " . $created . "\n";

    $field_name_binds = array(
      ':entity_type' => 'user',
      ':bundle' => 'user',
      ':deleted' => 0,
      ':entity_id' => $uid,
      ':revision_id' => $uid,
      ':language' => 'und',
      ':delta' => 0,
      ':field_name_value' => $display_name,
      ':field_name_format' => NULL,
    );
    $field_name_query->execute($field_name_binds);
    print $display_name . "\n";
  }
  print 'you did it' . "\n";
}

function run_migrate_users_rollback() {
  $user = 'root';
  $pass = 'pass';
  $bddb = new PDO('mysql:host=localhost;dbname=backdrop_cmi', $user, $pass);
  /* Delete all rows from the users table ; except user 1 */
  $count = $bddb->exec("delete from users where uid > 1");
  $bddb->exec("delete from field_data_field_name where entity_id > 1");

  /* Return number of rows that were deleted */
  print("Deleted $count users.\n");
}

function run_migrate_help() {
  print "\n\n";
  print "I did not understand; available options are:\n\n";
  print "\t --run \t=> \truns the migration\n";
  print "\t --rollback \t=> \trolls back the migration\n";
  print "\t --help \t=> \tprints this help message\n";
  print "\n";
}