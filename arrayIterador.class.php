<?php

/*
 * Simple iterator that serves as a utility class for xson
 * 
 * basic example:
 * <code>
 * 
 * $iterator = new arrayIterador(array(data));
 * 
 * while ($iterador->exists_next())
 * {
 * 	echo $iterador->next();
 * }
 * 
 * </code>
 *
 * @author Miguel Ángel Cagide Fagín <mikelusmac@gmail.com>
 */
class arrayIterador
{
	var $datos;
	var $conta;
	
	public function __construct($datos = null)
	{
		$this->datos = $datos;	
		$this->conta = 0;
	}
	
	//Iterator english aliases
	public function next()
	{
		return $this->seguinte();
	}
	
	public function exists_next()
	{
		return $this->existe_seguinte();
	}
	
	public function get_data()
	{
		return $this->obter_datos();
	}
	
	//orixinais en galego
	public function seguinte()
	{
		if ($this->conta < count($this->datos))
		{
			$chave = key(array_slice($this->datos, $this->conta, 1, true));
			$this->conta++;
		
			return $this->datos[$chave];
		}
		else
			return false;
	}	
	
	public function existe_seguinte()
	{
		if ($this->conta < count($this->datos))
			return true;
		else
			return false;
	}
	
	public function obter_datos()
	{
		return $this->datos;
	}
	
	public function reset()
	{
		$this->conta = 0;
	}
	
	public function conta()
	{
		return count($this->datos);
	}

}
?>
