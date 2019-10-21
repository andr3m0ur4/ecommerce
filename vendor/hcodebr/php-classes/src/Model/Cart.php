<?php 

namespace AndreMoura\Model;

use \AndreMoura\DB\Sql;
use \AndreMoura\Model;
use \AndreMoura\Mailer;
use \AndreMoura\Model\User;

class Cart extends Model 
{

	const SESSION = 'Cart';

	public static function getFromSession ( )
	{

		$cart = new Cart ( );

		if ( isset ( $_SESSION[Cart::SESSION] ) AND $_SESSION[Cart::SESSION]['idcart'] > 0 ) {

			$cart -> get ( ( int ) $_SESSION[Cart::SESSION]['idcart'] );

		} else {

			$cart -> getFromSessionID ( );

			if ( !( int ) $cart -> getidcart ( ) > 0 ) {

				$data = [
					'dessessionid' => session_id ( )
				];

				if ( User::checkLogin ( false ) === true ) {

					$user = User::getFromSession ( );

					$data['iduser'] = $user -> getiduser ( );

				}

				$cart -> setData ( $data );

				$cart -> save ( );

				$cart -> setToSession ( );

			}

		}
	}

	public function setToSession ( )
	{

		$_SESSION[Cart::SESSION] = $this -> getValues ( );

	}
	
	public function save ( ) 
	{

		$sql = new Sql ( );

		$results = $sql -> select (	"
			CALL sp_carts_save (:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)
		", array (
			':idcart' => $this -> getidcart ( ),
			':dessessionid' => $this -> getdessessionid ( ),
			':iduser' => $this -> getiduser ( ),
			':deszipcode' => $this -> getdeszipcode ( ),
			':vlfreight' => $this -> getvlfreight ( ),
			':nrdays' => $this -> getnrdays ( )
		));

		$this -> setData ( $results[0] );

	}

	public function get ( int $idcart ) 
	{

		$sql = new Sql ( );

		$results = $sql -> select ( "SELECT * FROM tb_carts WHERE idcart = :idcart", [
				'idcart' => $idcart
		] );

		if ( count ( $results ) > 0 ) {
			$this -> setData ( $results[0] );
		}

	}

	public function getFromSessionID ( ) 
	{

		$sql = new Sql ( );

		$results = $sql -> select ( "SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
				'dessessionid' => session_id ()
		] );

		if ( count ( $results ) > 0 ) {
			$this -> setData ( $results[0] );
		}

	}

	public function delete ( ) 
	{

		$sql = new Sql ( );

		$sql -> query ( "DELETE FROM tb_categories WHERE idcategory = :idcategory", [
			':idcategory' => $this -> getidcategory ( )
		]);

		Category::updateFile ( );
		
	}
	
}
