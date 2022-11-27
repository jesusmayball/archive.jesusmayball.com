<?php
//Placeholder until we get an MVC framework in here
require(dirname(__FILE__) . "/includes/config.php");
require(dirname(__FILE__) . "/includes/database_config.php");
require(dirname(__FILE__) . "/includes/constants.php");
require(dirname(__FILE__) . "/includes/captcha_config.php");

date_default_timezone_set("Europe/London");

ini_set("session.name", "TICKETING_SESSION");
//TODO: This isn't correct... session_path isn't saved
//ini_set("session.save_path", $config["session_path"]);
//ini_set("session.save_path", $_SERVER["DOCUMENT_ROOT"] . "/session");


/**
 * the auto-loading function, which will be called every time a file "is missing"
 * NOTE: don't get confused, this is not "__autoload", the now deprecated function
 * The PHP Framework Interoperability Group (@see https://github.com/php-fig/fig-standards) recommends using a
 * standardized auto-loader https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md, so we do:
 */
function autoload($clazz)
{
	$classMap = array(
		"Layout" => "/layout/Layout.php",
		"Vulnerabilities" => "/includes/vulnerabilities.php",
		"AjaxUtils" => "/includes/AjaxUtils.php",
		"ApplicationUtils" => "/includes/ApplicationUtils.php",
		"Auth" => "/includes/Auth.php",
		"UcamWebauth" => "/includes/ucam_webauth.php",
		"UcamAuthenticationModuleFailure" => "/includes/ucam_webauth.php",
		"UcamAuthenticationAuthenticationFailure" => "/includes/ucam_webauth.php",
		"DummyWebauth" => "/includes/dummy_webauth.php",
		"Database" => "/includes/database.php",
		"DatabaseException" => "/includes/database.php",
		"Emails" => "/emails/emails.php",
		"Email" => "/emails/email.php",
		"EmailDatabase" => "/emails/emailDatabase.php",
		"PHPMailer" => "/includes/PHPMailer/class.phpmailer.php",
		"SMTP" => "/includes/PHPMailer/class.smtp.php",
		"POP3" => "/includes/PHPMailer/class.pop3.php",
		"CaptchaBuilderInterface" => "/includes/CaptchaBuilderInterface.php",
		"CaptchaBuilder" => "/includes/CaptchaBuilder.php",
		"PhraseBuilderInterface" => "/includes/PhraseBuilderInterface.php",
		"PhraseBuilder" => "/includes/PhraseBuilder.php",
		"Ticket" => "/includes/ticket.php",
		"TicketRecord" => "/includes/ticket_record.php",
		"TicketType" => "/includes/tickettype.php",
		"TicketTypeRecord" => "/includes/tickettype_record.php",
		"Application" => "/includes/application.php",
		"ApplicationRecord" => "/includes/application_record.php",
		"NameChangeRecord" => "/includes/namechange_record.php",
		"PaymentRecord" => "/includes/payment-record.php",
		"ApplicationRecord" => "/includes/application_record.php",
		"WaitingListRecord" => "/includes/waitinglist_record.php",
		"NameChange" => "/includes/namechange.php",
		"FormValidation" => "/includes/form-validation.php",
		"LDAP" => "/includes/ldap.php",
		"PDFUtils" => "/includes/PDFUtils.php"
	);
	foreach ($classMap as $class => $file) {
		if ($class == $clazz) {
			require_once dirname(__FILE__) . $file;
			return;
		}
	}
	// exit ('The class ' . $clazz . ' is missing a corresponding includes.');
}

// spl_autoload_register defines the function that is called every time a file is missing. as we created this
// function above, every time a file is needed, autoload(THENEEDEDCLASS) is called
spl_autoload_register("autoload");
