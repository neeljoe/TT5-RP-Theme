# Adding Post Tags Workflow

Use the `novamira/execute-php` ability (not WP-CLI) to add tags to posts on this WordPress site.

## Pattern

For each post, follow these steps in parallel where possible:

### Step 1: Find the post and check/create missing tags (parallel)

**Find post by title:**
```php
$post = get_page_by_title('EXACT POST TITLE', OBJECT, 'post');
if ($post) {
  return array('id' => $post->ID, 'title' => $post->post_title, 'status' => $post->post_status);
} else {
  return 'Post not found';
}
```

**Check and create missing tags:**
```php
$results = array();
foreach (array('Tag1', 'Tag2', 'Tag3') as $tag_name) {
  $existing = term_exists($tag_name, 'post_tag');
  if ($existing) {
    $results[] = $tag_name . ' already exists: ' . json_encode($existing);
  } else {
    $term = wp_insert_term($tag_name, 'post_tag');
    if (is_wp_error($term)) {
      $results[] = $tag_name . ' error: ' . $term->get_error_message();
    } else {
      $results[] = $tag_name . ' created: ' . json_encode($term);
    }
  }
}
return $results;
```

### Step 2: Set all tags on the post

```php
$tags = array('Tag1', 'Tag2', 'Tag3');
$result = wp_set_post_terms(POST_ID, $tags, 'post_tag');
if (is_wp_error($result)) {
  return 'Error: ' . $result->get_error_message();
}
return 'Tags set successfully. Term IDs: ' . json_encode($result);
```

## Important Notes

- Always batch independent checks (find post + check tags) in parallel using two separate execute-php calls.
- Tags with spaces in names are matched/set using the full name string — no need for slugs or IDs.
- `wp_set_post_terms()` replaces all existing tags with the new set (it does not append).
- Check existing tags first with `term_exists()` to avoid duplicate term errors from `wp_insert_term()`.
- DO NOT use `novamira/run-wp-cli` — the PHP binary is not available on this server.
