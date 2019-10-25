<?php 

namespace AndreMoura\Model;

use \AndreMoura\DB\Sql;
use \AndreMoura\Model;

class OrderStatus extends Model 
{
	
	const EM_ABERTO = 1;
	const AGUARDANDO_PAGAMENTO = 2;
	const PAGO = 3;
	const ENTREGUE = 4;
}
