<?php 

namespace AndreMoura;

use Rain\Tpl;

class Mailer {

	const USERNAME = 'mouraandre2500@gmail.com';
	const PASSWORD = '$andr3_m0ur4';
	const NAME_FROM = 'André Ecommerce';

	private $email;

	public function __construct ( $toAddress, $toName, $subject, $tplName, $data = array ( ) ) {

		$config = array(
			"tpl_dir"       => $_SERVER['DOCUMENT_ROOT'] . '/views/email/',
			"cache_dir"     => $_SERVER['DOCUMENT_ROOT'] . "/views-cache/",
			"debug"         => false // set to false to improve the speed
		);

		Tpl::configure( $config );

		// create the Tpl object
		$tpl = new Tpl;

		foreach ( $data as $key => $value ) {
			$tpl -> assign ( $key, $value );
		}

		$html = $tpl -> draw ( $tplName, true );

		// Acessar a aplicação de e-mails;
		// Acessar o servidor de e-mails;
		// Fazer a autenticação com usuário e senha;
		// Usar a opção para escrever um e-mail;
		$this -> email = new \PHPMailer ( );	// Esta é a criação do objeto

		$this -> email -> CharSet = 'utf-8';
		$this -> email -> SMTPDebug = 0;
		$this -> email -> isSMTP ( );
		$this -> email -> Host = "smtp.gmail.com";
		$this -> email -> Port = 587;
		$this -> email -> SMTPSecure = 'tls';
		$this -> email -> SMTPAuth = true;
		$this -> email -> Username = Mailer::USERNAME;
		$this -> email -> Password = Mailer::PASSWORD;
		$this -> email -> setFrom ( Mailer::USERNAME, Mailer::NAME_FROM );

		// Digitar o e-mail	do destinatário;
		$this -> email -> addAddress ( $toAddress, $toName );

		// Digitar o assunto do	e-mail;
		$this -> email -> Subject = $subject;

		// Escrever	o corpo	do e-mail;
		$this -> email -> msgHTML ( $html );

		$this -> email -> AltBody = 'This is a plain-text message body';
		
	}

	public function send ( ) {

		return $this -> email -> send ( );

	}
}
