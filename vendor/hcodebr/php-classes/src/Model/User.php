<?php 

namespace AndreMoura\Model;

use \AndreMoura\DB\Sql;
use \AndreMoura\Model;
use \AndreMoura\Mailer;

class User extends Model {

	const SESSION = 'User';
	const SECRET = 'andrecommerce123';
	const SECRET_IV = 'andrecommerce123';

	public static function login ( $login, $password ) {

		$sql = new Sql ( );

		$results = $sql -> select ( "SELECT * FROM tb_users WHERE deslogin = :LOGIN", array (
			':LOGIN' => $login
		) );

		if ( count ( $results ) === 0 ) {
			throw new \Exception ( "Usuário inexistente ou senha inválida." );
		}

		$data = $results[0];

		if ( password_verify ( $password, $data['despassword'] ) === true ) {

			$user = new User ( );

			$user -> setData ( $data );

			$_SESSION[User::SESSION] = $user -> getValues ( );

			return $user;

		} else {
			throw new \Exception ( "Usuário inexistente ou senha inválida." );
		}
	}

	public static function verifyLogin ( $inadmin = true ) {

		if ( 
			!isset ( $_SESSION[User::SESSION] ) || 
			!$_SESSION[User::SESSION] ||
			!( int ) $_SESSION[User::SESSION]['iduser'] > 0 ||
			( bool ) $_SESSION[User::SESSION]['inadmin'] !== $inadmin
		) {

			header ( 'Location: /admin/login' );
			exit;

		}
	}

	public static function logout ( ) {

		$_SESSION[User::SESSION] = null;

	}

	public static function listAll ( ) {

		$sql = new Sql ( );

		return $sql ->  select ( "
			SELECT * FROM tb_users INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson
		" );
	}

	public function save ( ) {

		$sql = new Sql ( );

		$results = $sql -> select ( 
			"CALL sp_user_save (:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
			array (
				':desperson' => $this -> getdesperson( ),
				':deslogin' => $this -> getdeslogin( ),
				':despassword' => $this -> getdespassword ( ),
				':desemail' => $this -> getdesemail ( ),
				':nrphone' => $this -> getnrphone ( ),
				':inadmin' => $this -> getinadmin ( )
		));

		$this -> setData ( $results[0] );

	}

	public function get ( $iduser ) {

		$sql = new Sql ( );

		$results = $sql -> select ( "
			SELECT * FROM tb_users a 
			INNER JOIN tb_persons b USING (idperson) 
			WHERE a.iduser = :iduser", array (
				'iduser' => $iduser
			) );

		$this -> setData ( $results[0] );
	}

	public function update ( ) {

		$sql = new Sql ( );

		$results = $sql -> select ( "
			CALL sp_userupdate_save (:iduser, :desperson, :deslogin, :despassword, :desemail, 
				:nrphone, :inadmin)", array (
					':iduser' => $this -> getiduser ( ),
					':desperson' => $this -> getdesperson( ),
					':deslogin' => $this -> getdeslogin( ),
					':despassword' => $this -> getdespassword ( ),
					':desemail' => $this -> getdesemail ( ),
					':nrphone' => $this -> getnrphone ( ),
					':inadmin' => $this -> getinadmin ( )
		));

		$this -> setData ( $results[0] );
	}

	public function delete ( ) {

		$sql = new Sql ( );

		$sql -> query ( "CALL sp_users_delete (:iduser)", array (
			':iduser' => $this -> getiduser ( )
		));
	}

	public static function getForgot ( $email ) {

		$sql = new Sql ( );

		$results = $sql -> select ( "
			SELECT * FROM tb_persons a 
			INNER JOIN tb_users b USING (idperson)
			WHERE a.desemail = :email
		", array (
				':email' => $email
		));

		if ( count ( $results ) === 0 ) {
			throw new \Exception( "Não foi possível recuperar a senha." );
		} else {

			$data = $results[0];

			$results2 = $sql -> select ( "CALL sp_userpasswordsrecoveries_create (:iduser, :desip)", 
				array (
					':iduser' => $data['iduser'],
					':desip' => $_SERVER['REMOTE_ADDR']
			));

			if ( count ( $results2 ) === 0 ) {

				throw new \Exception( "Não foi possível recuperar a senha." );

			} else {

				$dataRecovery = $results2[0];

				$cipher = "AES-128-CBC";

				$code = base64_encode ( openssl_encrypt (
					$dataRecovery['idrecovery'], 
					$cipher, 
					User::SECRET, 
					$options = 0,
					User::SECRET_IV
				));

				$link = "http://www.andrecommerce.com.br/admin/forgot/reset?code=$code";

				$mailer = new Mailer ( $data['desemail'], $data['desperson'], 
					'Redefinir Senha de André Ecommerce', 'forgot', array (
						'name' => $data['desperson'],
						'link' => $link
				) );

				$mailer -> send ( );

				return $data;

			}
		}
	}

	public static function validForgotDecrypt ( $code ) {

		$cipher = "AES-128-CBC";

		$idrecovery = openssl_decrypt ( base64_decode ( $code ),
			$cipher,
			User::SECRET, 
			$options = 0,
			User::SECRET_IV
		);

		$sql = new Sql ( );

		$results = $sql -> select ( "
			SELECT * FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING (iduser)
			INNER JOIN tb_persons USING (idperson)
			WHERE a.idrecovery = :idrecovery
				AND a.dtrecovery IS NULL
				AND date_add(a.dtregister, INTERVAL 1 HOUR) >= now()", array (
					'idrecovery' => $idrecovery
		));

		if ( count ( $results ) === 0 ) {

			throw new \Exception("Não foi possível recuperar a senha." );
			
		} else {

			return $results[0];

		}
	}

	public static function setForgotUsed ( $idrecovery ) {

		$sql = new Sql ( );

		$sql -> query ( "UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() 
							WHERE idrecovery = :idrecovery", array (
			'idrecovery' => $idrecovery
		));

	}

	public function setPassword ( $password ) {

		$sql = new Sql ( );

		$sql -> query ( "UPDATE tb_users SET despassword = :despassword WHERE iduser = :iduser", array (
			'despassword' => $password,
			'iduser' => $this -> getiduser ( )
		));

	}
}
