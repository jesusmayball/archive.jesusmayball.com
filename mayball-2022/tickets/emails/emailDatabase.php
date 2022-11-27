<?php

class EmailDatabase
{

	private $db = null;

	public function __construct()
	{
		$this->db = Database::getInstance();
	}

	public function deleteEmail($id)
	{
		$email = $this->db->getEmail($id);
		if (!$email) {
			return self::error("No email exists with id \"$id\"");
		}
		if ($email->builtIn == 1) {
			return self::error("Unable to delete a built in email");
		}
		$query = $this->db->prepare("DELETE FROM `emails` WHERE `id` = :id");
		$query->execute(array(
			":id" => $id
		));
		return self::success($email->name . " deleted");
	}

	public function addOrUpdateEmail($name, $appOrTicket, $subject, $body)
	{
		//Should we validate here?
		if ($name == null) {
			return self::error("Name may not be null");
		}
		if ($appOrTicket < 0 || $appOrTicket > 2) {
			return self::error("appOrTicket may not be outside range 0 - 2");
		}

		$email = $this->db->getEmailByName($name);
		if (!$email) {
			$builtIn = 0;
			$subject = $subject == null ? "" : $subject;
			$body = $subject == null ? "" : $body;

			$defaultSubject = "";
			$defaultBody = "";

			$sql = "INSERT INTO `emails` (name, appOrTicket, builtIn, subject, body, defaultSubject, defaultBody) VALUES (:name, :appOrTicket, :builtIn, :subject, :body, :defaultSubject, :defaultBody)";
			$query = $this->db->prepare($sql);
			$query->execute(array(
				":name" => $name,
				":appOrTicket" => $appOrTicket,
				":builtIn" => $builtIn,
				":subject" => $subject,
				":body" => $body,
				":defaultSubject" => $defaultSubject,
				":defaultBody" => $defaultBody,
			));

			return self::success("Email \"$name\" updated");
		} else {
			if (isset($subject)) {
				$email->subject = $subject;
			}
			if (isset($body)) {
				$email->body = $body;
			}
			if ($email->builtIn == 0) {
				$email->appOrTicket = $appOrTicket;
			}
			return $this->storeEmail($email);
		}
	}

	public function restoreDefaultEmail($id)
	{
		$email = $this->db->getEmail($id);
		if (!$email) {
			return self::error("No email exists with id \"$id\"");
		}
		if ($email->builtIn == 0) {
			return self::error("Custom emails don't have default values.");
		}
		return $this->addOrUpdateEmail($email->name, 1, $email->defaultSubject, $email->defaultBody);
	}

	private function storeEmail($email)
	{
		// 		if (get_class($email) != "Email") {
		// 			throw new DatabaseException("Incorrect object supplied, required Email");
		// 		}

		$query = $this->db->prepare("UPDATE `emails` SET `body` = :body, `subject` = :subject, `appOrTicket` = :appOrTicket WHERE `name` = :name");

		$query->execute(array(
			":body" => $email->body,
			":subject" => $email->subject,
			":appOrTicket" => $email->appOrTicket,
			":name" => $email->name
		));

		return self::success("Email \"$email->name\" updated");
	}

	private static function success($message)
	{
		return array("success" => $message);
	}

	private static function error($message)
	{
		return array("error" => $message);
	}
}
