<?php

/**
 * Definitions of webhooks supported by StreamIn
 *
 * Params:
 * - title
 * - description
 * - example
 */
$STREAMIN_WEBHOOKS = array(
    'user_register' => array(
        'title' => 'User Registered',
        'description' => 'Fired immediately after a new user is added to the database.',
        'example' => 'New user on <a href="#">My fancy blog</a>: <b>The User</b>'
    ),
    'delete_user' => array(
        'title' => 'User Removed',
        'description' => 'Fired when a user is deleted / removed.',
        'example' => '<b>The User</b> removed from <a href="#">My fancy blog</a>'
    ),
    'wp_login' => array(
        'title' => 'Login',
        'description' => 'Fired when a user logs in.',
        'example' => '<b>The User</b> logged in to <a href="#">My fancy blog</a>'
    ),
    'publish_post' => array(
        'title' => 'Publish Post',
        'description' => 'Fired when a post is published, or if it is edited and its status is "published".',
        'example' => '<a href="#">My fancy article</a> published on <a href="#">My fancy blog</a>. Author: <b>The User</b>'
    ),
    'wp_insert_comment' => array(
        'title' => 'New Comment',
        'description' => 'Fired whenever a comment is created.',
        'example' => 'New comment on <a href="#">My fancy blog</a> from <b>Comment Author</b> to <a href="#">My fancy article</a>'
    ),
);

/**
 * Webhook fired immediately after a new user is added to the database.
 */
function streamin_webhook_user_register($user_id) {
    // get user
    $user = get_userdata($user_id);

    // form message
    $message = "New user on " . streamin_site_link_wiki() .": '''{$user->user_firstname} {$user->user_lastname}'''";

    // get webhooks settings
    $webhooks = streamin_get_webhooks();

    // perform API call
    streamin_api_call('user_register',$webhooks['user_register'],$message);
}

/**
 * Webhook fired when a user is deleted/removed.
 */
function streamin_webhook_delete_user($user_id) {
    // get user
    $user = get_userdata($user_id);

    // form message
    $message = "'''{$user->user_firstname} {$user->user_lastname}''' removed from " . streamin_site_link_wiki();

    // get webhooks settings
    $webhooks = streamin_get_webhooks();

    // perform API call
    streamin_api_call('delete_user',$webhooks['delete_user'],$message);
}

/**
 * Webhook fired when a user logs in.
 */
function streamin_webhook_wp_login($wp_user) {
    // get user
    $user = get_user_by('login',$wp_user);

    // form message
    $message = "'''{$user->user_firstname} {$user->user_lastname}''' logged in to " . streamin_site_link_wiki();

    // get webhooks settings
    $webhooks = streamin_get_webhooks();

    // perform API call
    streamin_api_call('wp_login',$webhooks['wp_login'],$message);
}

/**
 * Webhook fired when a post is published.
 */
function streamin_webhook_publish_post($post_id) {
    // get post
    $post = get_post($post_id);

    // get post permalink
    $url = get_permalink($post);

    // get author
    $author = get_userdata($post->post_author);

    // form message
    $message = "[{$url} {$post->post_title}] published on " . streamin_site_link_wiki() .
        ". Author: '''{$author->user_firstname} {$author->user_lastname}'''";

    // get webhooks settings
    $webhooks = streamin_get_webhooks();

    // perform API call
    streamin_api_call('publish_post',$webhooks['publish_post'],$message);
}

/**
 * Webhook fired whenever a comment is created.
 */
function streamin_webhook_wp_insert_comment($comment_id, $comment_object) {
    // get comment
    $comment = get_comment($comment_id);

    // get post
    $post = get_post($comment->comment_post_ID);

    // get post permalink
    $url = get_permalink($post);

    // init comment author
    $author = (isset($comment->comment_author_url) && $comment->comment_author_url != null) ?
        "[{$comment->comment_author_url} {$comment->comment_author}]" :
        "'''{$comment->comment_author}'''";

    // form message
    $message = "New comment on " . streamin_site_link_wiki() . " from {$author} to [{$url} {$post->post_title}]";

    // get webhooks settings
    $webhooks = streamin_get_webhooks();

    // perform API call
    streamin_api_call('publish_post',$webhooks['publish_post'],$message);
}