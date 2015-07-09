<?php
/*
 * Test script to figure out how to pass command line arguments to
 * a php script.
 * NOTE: argv[0] is the path to the script.
 */
if(count($argv) > 1) {
  $my_arg = $argv[1];
  if ($my_arg == '--run') {
    geoff_is_the_best();
  }
}
else {
  print "you did not pass any command line arguments; available arguments are:\n\n";
  print "\t --run \t=> \truns the migration\n";
  print "\t --rollback \t=> \trolls back the migration\n";
  print "\t --help \t=> \tprints this help message\n";
  print "\n";
}

function geoff_is_the_best() {
  print 'geoff is the best!' . "\n\n";
}
?>