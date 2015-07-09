<?php
/**
 * Migrate CMI Worpress posts to Backdrop CMS blog content type.
 * 0: "ID",
 * 1: "post_author",
 * 2: "post_date",
 * 3: "post_date_gmt",
 * 4: "post_content",
 * 5: "post_title",
 * 6: "post_excerpt",
 * 7: "post_status",
 * 8: "comment_status",
 * 9: "ping_status",
 * 10: "post_password" => NOT USED,
 * 11: "post_name",
 * 12: "to_ping",
 * 13: "pinged",
 * 14: "post_modified",
 * 15: "post_modified_gmt",
 * 16: "post_content_filtered",
 * 17: "post_parent",
 * 18: "guid",
 * 19: "menu_order",
 * 20: "post_type",
 * 21: "post_mime_type",
 * 22: "comment_count"
 */

function run_migrate_posts() {
  $user = 'root';
  $pass = 'pass';
  $wpdb = new PDO('mysql:host=localhost;dbname=cmi_wp', $user, $pass);
  $bddb = new PDO('mysql:host=localhost;dbname=backdrop_cmi', $user, $pass);
  $sql = $wpdb->prepare("select * from wp_posts where post_type = 'post' and post_content != ''");
  $sql->execute();
  $data = $sql->fetchAll();

  $blog_sql = $bddb->prepare(
    "insert into node (
   nid,
   vid,
   type,
   langcode,
   title,
   uid,
   status,
   created,
   changed,
   comment,
   promote,
   sticky,
   tnid,
   translate
 ) values (
     :nid,
     :vid,
     :type,
     :langcode,
     :title,
     :uid,
     :status,
     :created,
     :changed,
     :comment,
     :promote,
     :sticky,
     :tnid,
     :translate
   )"
  );
  $body_query = $bddb->prepare(
    "insert into field_data_body (
     entity_type,
     bundle,
     deleted,
     entity_id,
     revision_id,
     language,
     delta,
     body_value,
     body_summary,
     body_format
   ) values (
     'node',
     'blog',
     0,
     :entity_id,
     :revision_id,
     'und',
     0,
     :body_value,
     NULL,
     'full_html'
   )"
  );
  //$i = 79;
  foreach($data as $d) {
    $post_author = $d['post_author'] + 1;
    $post_date = strtotime($d['post_date']);
    $post_changed = strtotime($d['post_modified']);
    $blog_binds = array(
      ':nid' => $d['ID'],
      ':vid' => $d['ID'],
      ':type' => 'blog',
      ':langcode' => 'und',
      ':title' => $d['post_title'],
      ':uid' => $post_author,
      ':status' => 1,
      ':created' => $post_date,
      ':changed' => $post_changed,
      ':comment' => 0,
      ':promote' => 0,
      ':sticky' => 0,
      ':tnid' => 0,
      ':translate' =>0,
    );
    $blog_sql->execute($blog_binds) or die(print_r($blog_sql->errorInfo(), true));
    print $d['post_author'] + 1 . " " . $post_date . "\n";

    $body_binds = array(
      ':entity_id' => $d['ID'],
      ':revision_id' => $d['ID'],
      ':body_value' => $d['post_content'],
    );
    $body_query->execute($body_binds) or die(print_r($body_query->errorInfo(), true));
    //$i++;
  }

  print "CMI wp-posts to Backdrop CMS blog content type complete.\n";

}

// rollback posts migration
function run_migrate_posts_rollback() {
  $user = 'root';
  $pass = 'pass';
  $bddb = new PDO('mysql:host=localhost;dbname=backdrop_cmi', $user, $pass);
  /* Delete all rows from the users table ; except user 1 */
  $count = $bddb->exec("delete from node where nid > 6 and type = 'blog'");
  $bddb->exec("delete from field_data_body where entity_id > 6 and bundle = 'blog'");

  /* Return number of rows that were deleted */
  print("Deleted $count nodes of type 'blog'.\n");
}
?>
