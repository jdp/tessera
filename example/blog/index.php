<?php
/* Assuming this is being run out of TESSERA/examples/blog */
require '../../tessera.php';
require 'redisent.php';

class Blog extends Tessera {

	var $layout = 'blog';

	/* Configuration */
	function __before() {
		$this->admin_pw = 'chalkboard';
		try {
			$this->redis = new Redisent('127.0.0.1', 6379);
			$this->redis->setnx('global:nextPostId', 100);
		}
		catch (Exception $e) {
			die("<p>Redis Error: {$e->getMessage()}</p>");
		}
	}
	
	function __error($code) {
		echo "<p>Error code {$code}.</p>";
	}
	
	/* Browse a page of posts */
	function browse($page = 1) {
		$this->set('page', $page);
		$this->set('pages', ceil($this->redis->llen('global:posts') / 10));
		$this->set('posts', $this->getPosts(($page - 1) * 10, 10));
	}
	
	/* View a single post */
	function view($id) {
		$this->view = 'browse';
		$this->set('posts', array($this->getPost($id)));
	}
	
	/* Add a new post */
	function add() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			foreach (array('title', 'body') as $field) {
				if (empty($_POST[$field])) {
					$this->set('message', "You need to fill in the {$field} field!");
					return;
				}
			}
			if ($this->admin_pw != $_POST['password']) {
				$this->set('message', "That's definitely not the right password!");
				return;
			}
			$post_id = $this->redis->incr('global:nextPostId');
			$this->redis->lpush('global:posts', $post_id);
			$this->redis->set("post:{$post_id}:date", time());
			$this->redis->set("post:{$post_id}:title", trim($_POST['title']));
			$this->redis->set("post:{$post_id}:body", trim($_POST['body']));
			header("Location: index.php?/post/{$post_id}");
		}
	}
	
	/* Edit a post by ID */
	function edit($post_id) {
		$this->set('post', $post = $this->getPost($post_id));
		if (empty($post)) {
			$this->set('message', "That post doesn't exist to edit.");
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			foreach (array('title', 'body') as $field) {
				if (empty($_POST[$field])) {
					$this->set('message', "You need to fill in the {$field} field!");
					return;
				}
			}
			if ($this->admin_pw != $_POST['password']) {
				$this->set('message', "That's definitely not the right password.");
				return;
			}
			$this->redis->set("post:{$post_id}:title", trim($_POST['title']));
			$this->redis->set("post:{$post_id}:body", trim($_POST['body']));
			header("Location: index.php?/post/{$post_id}");
		}
	}
	
	/* Delete a post by ID */
	function delete($post_id) {
		$this->set('post', $post = $this->getPost($post_id));
		if (empty($post)) {
			$this->set('message', "That post doesn't exist to delete.");
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($this->admin_pw != $_POST['password']) {
				$this->set('message', "That's definitely not the right password.");
				return;
			}
			$this->redis->lrem("global:posts", 1, $post_id);
			$this->redis->del("post:{$post_id}:date");
			$this->redis->del("post:{$post_id}:title");
			$this->redis->del("post:{$post_id}:body");
			header("Location: index.php");
		}
	}
	
	/* Gets a paginated list of posts */
	private function getPosts($start, $count) {
		$posts = array();
		$post_ids = $this->redis->lrange("global:posts", $start, $count);
		if (!empty($post_ids)) {
			foreach ($post_ids as $id) {
				$posts[] = $this->getPost($id);
			}
		}
		return $posts;
	}
	
	/* Returns a single post by ID */
	private function getPost($id) {
		$title = $this->redis->get("post:{$id}:title");
		if (empty($title)) {
			return null;
		}
		return array(
			'id'    => $id,
			'date'  => $this->redis->get("post:{$id}:date"),
			'title' => $title,
			'body'  => $this->redis->get("post:{$id}:body")
		);
	}
	
}

$blog = new Blog(array(
	'/'             => 'browse',
	'/page/$page'   => 'browse',
	'/post/$id'     => 'view',
	'/add'          => 'add',
	'/edit/$id'     => 'edit',
	'/remove/$id'   => 'delete'
));
