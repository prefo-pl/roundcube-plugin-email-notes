<?php

/**
 * Email Notes
 *
 * This plugin allows you to add notes to emails.
 *
 * @version 1.0
 * @license MIT License
 * @url
 * @package email_notes
 */

// icon from: https://www.veryicon.com/icons/business/a-set-of-commercial-icons/notes-54.html 

class email_notes extends rcube_plugin
{
	public $task = 'mail';
	private $rc;
	private $db;
	private $config;

	function init()
	{
		$this->rc = rcmail::get_instance();

		$this->config = $this->rc->config->get('email_notes');

		$this->include_script('email_notes.js');
		$this->include_stylesheet('email_notes.css');

		$this->add_hook('message_objects', array($this, 'message_objects'));
		$this->register_action('email_notes.save_note', array($this, 'save_note'));
	}

	function message_objects($args)
	{
		$message = $args['message'];
		$message_id = $message->get_header('message-id');


		$note = $this->get_note($this->rc->get_user_id(), $message_id);

		$content = $args['content'];

		$div = '
		<div>
			<div class="email_note" data-message-id="'.htmlspecialchars($message_id).'">
				<div class="icon"></div>
				<div class="content">
					<div class="text">'.$note.'</div>
				</div>
				<div class="edit">
					<svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.--><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/></svg>
				</div>
			</div>
		</div>';

		array_push($content, $div);

		return ['content' => $content];
	}

	public function get_note($user_id, $message_id)
	{
		$db = $this->get_dbh();

		$result = $db->query('select note from email_notes where user_id = ? and message_id = ?', $user_id, $message_id);
		$row = $db->fetch_assoc($result);

		if(!$row)
			return null;
		
		return $row['note'];
	}

	public function save_note()
	{
		$user_id = $this->rc->get_user_id();
		$message_id = $_POST['message_id'];
		$note = rcube_utils::get_input_value('note', rcube_utils::INPUT_GPC);

		$this->get_dbh()->query('delete from email_notes where user_id = ? and message_id = ?', $user_id, $message_id);
		$this->get_dbh()->query('insert into email_notes (user_id, message_id, note) values (?, ?, ?)', $user_id, $message_id, $note);
	}

	function get_dbh(): rcube_db
	{
		if (!isset($this->db)) {
			if ($dsn = $this->config['dsn']) {
				$this->db = rcube_db::factory($dsn);
				$this->db->set_debug((bool)$this->rc->config->get('sql_debug'));
				$this->db->db_connect('w');
			} else {
				$this->db = $this->rc->get_dbh();
			}
		}

		$this->db->query('CREATE TABLE IF NOT EXISTS email_notes (
			user_id int,
			message_id VARCHAR(1000),
			note TEXT
		);
		');

		return $this->db;
	}
}
