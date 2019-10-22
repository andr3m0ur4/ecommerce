<?php 

namespace AndreMoura\Model;

use \AndreMoura\DB\Sql;
use \AndreMoura\Model;

class Address extends Model 
{
	
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
	
	public function getValues ( )
	{

		$this -> getCalculateTotal ( );

		return parent::getValues ( );

	}
	
}
