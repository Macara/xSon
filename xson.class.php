<?php
/*
 * Lightweigth library for objectual management of XML/JSON data
 * 
 * basic instantation/conversion example:
 * <code>
 * 
 * $xml = '<?xml version="1.0" encoding="UTF-8" ?><base id="1"></base>';
 *
 * $xml_xson = new xSon($xml);
 * 
 * echo $xml_xson->to_string(JSON);
 * 
 * </code>
 *
 * @author Miguel Ángel Cagide Fagín <mikelusmac@gmail.com>
 */
include(dirname(__FILE__).'/arrayIterador.class.php');

define('XML','xml');
define('JSON','json');

//internally the code isn't JSON or XML, only my own array representation.
class xSon
{
	
	var $ficheiro;
	var $datos;
	var $temp;
	var $xml_version;
	var $xml_codif;
	var $json_root;
	var $root;
	var $xml_value_parameter;
	
	public function __construct($datos = "",$json_root = "json_root", $xml_value = "xml_value")
	{
		$this->xml_value = $xml_value;
		$this->json_root = $json_root;
		$this->xml_version = '1.0';
		$this->xml_codif = 'utf-8';
		
		if ($datos == "")
			$this->datos = array();
		else
		{
			if (!realpath($datos)) {
				$this->ficheiro = $datos;
				if (@simplexml_load_string($this->ficheiro))
				{
					$this->parse_xml(false);
				}
				else
				{
					$datos = '{"@raiz":'.$datos.'}';
					$this->parse_json($datos);
				}
			}
			else
			{
				$this->ficheiro = $datos;
				if (@simplexml_load_file($this->ficheiro))
				{
					$this->parse_xml(true);
				}
				else
				{
					$fich = file_get_contents($datos);
					$fich = '{"@raiz":'.$fich.'}';
					$this->parse_json($fich);
				}
			}
		}
	}
	
	//English aliases
	
	//write to disk file. Using the flag value as XML or JSON you'll obtain the correct codification
	public function write_file($file = null, $flags = 0)
	{
		$this->escribir_ficheiro($file,$flags);
	}
	
	//Obtain a single node as a new xSon object using the key value(mandatory) and the other values(optional) 
	public function get_node($key = "", $attribute = null, $attributeValue = null, $father = null, $fatherAttribute = null, $fatherAtributeValue = null)
	{
		return $this->obter_nodo($key,$attribute,$attributeValue,$father,$fatherAttribute,$fatherAtributeValue);
	}
	
	//Add a new node to the xSon object
	public function add_node($key = "", $attributes = null, $value = null, $father = null, $fatherAttribute = null , $fatherAtributeValue = null)
	{
		$this->engadir_nodo($key,$attributes,$value,$father,$fatherAttribute,$fatherAtributeValue);
	}
	
	//Delete the specified node
	public function delete_node($key = "", $attribute = null, $attributeValue = null, $father = null, $fatherAttribute = null, $fatherAtributeValue = null)
	{
		$this->eliminar_nodo($key,$attribute,$attributeValue,$father,$fatherAttribute,$fatherAtributeValue);
	}
	
	//Obtain the attributes of a particular node
	public function attributes($key = "", $attribute = null, $attributeValue = null, $father = null, $fatherAttribute = null, $fatherAtributeValue = null)
	{
		return $this->atributos($key,$attribute,$attributeValue,$father,$fatherAttribute,$fatherAtributeValue);
	}
	
	//Obtain the sons of a particular node
	public function sons($key = "", $attribute = null, $attributeValue = null, $father = null, $fatherAttribute = null, $fatherAtributeValue = null)
	{
		return $this->fillos($key,$attribute,$attributeValue,$father,$fatherAttribute,$fatherAtributeValue);
	}
	
	//Obtain the value of a particular node
	public function value($key = "", $attribute = null, $attributeValue = null, $father = null, $fatherAttribute = null, $fatherAtributeValue = null)
	{
		return $this->valor($key,$attribute,$attributeValue,$father,$fatherAttribute,$fatherAtributeValue);
	}
	
	//obtain an iterator of the xSon internal data
	public function iterator()
	{
		return $this->iterador();
	}
	
	//Output as an XML/JSON string
	public function to_string($flags = XML)
	{
		return $this->a_cadea($flags);
	}
	
	//This section defines the getters/setters of the class
	public function data(data = null)
	{
		if (data == null)
			return $this->datos;
		else 
			$this->datos = data;
	}
	
	public function json_root(json_root = null)
	{
		if (json_root == null)
			return $this->json_root;
		else 
			$this->json_root = json_root;
	}
	
	
	public function xml_codification(xml_codif = null)
	{
		if (xml_codif == null)
			return $this->xml_codif;
		else 
			$this->xml_codif = xml_codif;
	}
	
	public function xml_version(xml_version = null)
	{
		if (xml_version == null)
			return $this->xml_version;
		else 
			$this->xml_version = xml_version;
	}
	
	
	public function xml_value(xml_value = null)
	{
		if (xml_value == null)
			return $this->xml_value;
		else 
			$this->xml_value = xml_value;
	}
	
	//Implementacion das funcions
	public function escribir_ficheiro($ficheiro = null,  $flags = 0)
	{
		if ($flags == XML)
			$this->escribir_xml($this->datos,$ficheiro);
		else
			if ($flags == JSON)
			{
				$datos = $this->escribir_json();
				$ficheiro = fopen($ficheiro, "w");
				fwrite($ficheiro,$datos);
				fclose($ficheiro);
			}
			else
			{
				$this->escribir_xml($this->datos,$ficheiro);
			}
	}
	
	public function obter_nodo($key = "", $atributo = null, $idAtributo = null, $pai = null, $atributoPai = null, $idAtributoPai = null)
	{
		$this->temp = array();
		
		if ($key == "")
			return false;
		else
		{	
			$this->temp[$key] = array();
		}
		
		if ($pai == null)
			$this->get_nodo_recursivo($this->datos,$key,$atributo,$idAtributo);
		else
			$this->get_nodo_pai_recursivo($this->datos,$key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai);
				
		$temporal = new xSon();
		$temporal->cambiar_datos($this->temp);
		$this->temp = null; 
		return $temporal;
	}
	
	public function engadir_nodo($key = "", $atributos = null, $valor = null, $pai = null, $atributoPai = null, $idAtributoPai = null)
	{
		if ($key == "")
			return false;
			
		$temporal = array();
		
		if ($atributos != null)
			$temporal["@atributos"] = $atributos;
		else
			$temporal["@atributos"] = array();
			
		if ($valor != null)
			$temporal["@valor"] = $valor;
		else
			$temporal["@valor"] = "";
			
		if ($pai == null)
		{
			if (count($this->datos) == 0)
				$this->datos[$key][0] = $temporal;
			else
			{
				$chave = key(array_slice($this->datos, 0, 1, true));
				$this->datos[$chave][0][$key][] = $temporal;
			}
		}
		else
		{
			$this->datos = $this->engadir_nodo_recursivo($this->datos,$key,$temporal,$pai,$atributoPai,$idAtributoPai);
		}
	}
	
		public function eliminar_nodo($key = "", $atributo = null, $idAtributo = null, $pai = null, $atributoPai = null, $idAtributoPai = null)
	{
		if ($key == "")
			return false;
		$this->datos = $this->eliminar_nodo_recursivo_pai($this->datos,$key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai);
	}
	
		public function atributos($key = "", $atributo = null, $idAtributo = null, $pai = null, $atributoPai = null, $idAtributoPai = null)
	{
		if ($key == "")
			return false;
			
		$datos = $this->obter_nodo($key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai)->obter_datos();
		
		$atributos = array();
		
		foreach ($datos[$key] as $elemento)
		{
			$atributos[] = $elemento["@atributos"];
		}
		if (count($atributos) == 0)
			return false;
		else
			return $temporal = new arrayIterador($atributos);
		$this->temp = null;
	}
	
	public function fillos($key = "", $atributo = null, $idAtributo = null, $pai = null, $atributoPai = null, $idAtributoPai = null)
	{
		$datos = $this->obter_nodo($key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai)->obter_datos();

		$fillos = array();
		
		foreach ($datos[$key] as $elemento)
		{
			$j = 0;
			foreach ($elemento as $fillo)
			{
				$chave = key(array_slice($elemento, $j, 1, true));
				if (($chave != "@atributos") && ($chave != "@valor")) 
				{
					foreach ($fillo as $unico)
					{
						if(!isset($fillos[$chave]))
							$fillos[$chave] = array();
							
						array_push($fillos[$chave],$unico);
					}
				}
			$j++;
			}
		}
		
		if (count($fillos) == 0)
		{
			return false;
		}
		else
		{
			$temporal = new xSon();
			$temp = array();
			$temp[$key] = array();
			$temp[$key][0] = array();
			$temp[$key][0] = $fillos;
			$temp[$key][0]["@atributos"] = array();
			$temp[$key][0]["@valor"] = "";
			$temporal->cambiar_datos($temp);
		}
		
		return $temporal;
	}
	
	public function valor($key = "", $atributo = null, $idAtributo = null, $pai = null, $atributoPai = null, $idAtributoPai = null)
	{
		$datos = $this->obter_nodo($key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai)->obter_datos();
		
		$valores = array();
		
		foreach ($datos[$key] as $elemento)
		{
			if (strlen($elemento["@valor"]) !=0)
				$valores[] = $elemento["@valor"];
		}
		if (count($valores) == 0)
			return false;
		
		$temporal = new arrayIterador($valores);
		
		return $temporal;;

	}
	
	public function a_cadea($flags = XML)
	{
		if ($flags == XML)
		{
			$xml = new DOMDocument($this->xml_version, $this->xml_codif);
			$xml->formatOutput = true;
		
			//creamos a raiz
			$chave = key(array_slice($this->datos, 0, 1, true));
			if ($chave == "@raiz")
			{
				$raiz = $xml->createElement($this->json_root);
				$raiz = $xml->appendChild($raiz);
			}
			else
			{
				$raiz = $xml->createElement($chave);
				$raiz = $xml->appendChild($raiz);
			}
					
			$xml->appendChild($this->escribir_recursivo($this->datos[$chave][0],$raiz,$xml));
			
			if ($valor = $xml->saveXML($xml))
				return $valor;
			else 
			{
				$xml = new DOMDocument($version, $codificacion);
				$xml->formatOutput = true;
				
				$raiz = $xml->createElement("erro");
				$raiz->nodeValue = "Erro ao gardar";
				$raiz = $xml->appendChild($raiz);
				
				return $xml->saveXML($raiz);
			}
		}
		else
			if ($flags == JSON)
			{
				return $this->escribir_json();
			}
			else
			{
				return $this->escribir_json();
			}
	}
	
	public function iterador()
	{
		$chave = key(array_slice($this->datos, 0, 1, true));
		return new arrayIterador($this->datos[$chave]);
	}
	
	
	
	//Implementacions recursivas privadas
	private function parse_xml($lFicheiro)
	{
		if ($lFicheiro)
		{
			if (!($xml = @simplexml_load_file($this->ficheiro)))
			{
				$this->datos = array();
			}
			else
			{
				$saida[$xml->getName()] = array($this->parsear_recursivo($xml));
				$this->datos =  $saida;
			}
		}
		else
		{
		if (!($xml = @simplexml_load_string($this->ficheiro)))
			{
				$this->datos = array();
			}
			else
			{
				$saida[$xml->getName()] = array($this->parsear_recursivo($xml));
				$this->datos =  $saida;
			}
		}
	}
	
	private function parsear_recursivo($xml = null)
	{
		$arrayFillos = array();
		$i = 0;
		
		$valor = trim((string)$xml); 
		
		$arrayFillos["@valor"] = $valor;
			
		if ($xml->attributes()->count() > 0)
		{
			foreach ($xml->attributes() as $c => $v)
			{
				$temp = "".$v;
				$arrayFillos["@atributos"][$c] = $temp;
			}
		}
		else
		{ 
			$arrayFillos["@atributos"] = array();
		}
			
		foreach ($xml->children() as $fillos)
		{
			$arrayFillos[$fillos->getName()][] =  $this->parsear_recursivo($fillos);
		}
		
		return $arrayFillos;
		
	}
	
	private function parse_json($string = "")
	{
		$array = array();
		$temp = json_decode($string,true);
		$chave = key(array_slice($temp, 0, 1, true));
		
		$chave2 = key(array_slice($temp[$chave], 0, 1, true));
		if (!is_numeric($chave2))
		{
			if ($chave2 == $this->json_root)
			{
				$temp[$chave] = $temp[$chave][$chave2];
			}
			$aux[$this->json_root]=$temp[$chave];
			$temp[$chave] = $aux;
			$array[$chave] = array($this->json_recursivo($temp[$chave]));
		}
		else
		{
			$aux[$this->json_root]=$temp[$chave];
			$temp[$chave] = $aux;
			$array[$chave] = array($this->json_recursivo($temp[$chave]));
		}
		
		
		$this->datos = $array;
		
		
	}
	
	private function json_recursivo($datos = null)
	{
		$temp["@valor"] = "";
		$temp["@atributos"] = array();
	
		for ($i = 0; $i < count($datos);$i++)
		{
			$chave = key(array_slice($datos, $i, 1, true));
			if (is_array($datos[$chave]))
			{
				$temp[$chave] = array();
				
				for ($j = 0; $j < count($datos[$chave]);$j++)
				{
					$chave2 = key(array_slice($datos[$chave], $j, 1, true));
					if (is_array($datos[$chave][$chave2]))
					{
						
						if (array_key_exists("0",$datos[$chave][$chave2]))
						{
							foreach ($datos[$chave][$chave2] as $elemento)
							{
								$temp[$chave][$chave2][] = $this->json_recursivo($elemento);
							}
						}
						else 
						{
							$temp[$chave][$chave2] = $this->json_recursivo($datos[$chave][$chave2]);
						}
					}
					else
						$temp[$chave] = array($this->json_recursivo($datos[$chave]));
				}
			}
			else
			{
				$temp["@atributos"][$chave] = $datos[$chave];
			}
		}
		return $temp;
	}
		
	private function escribir_xml($datos = null, $ficheiro = null)
	{
		
		$xml = new DOMDocument($this->xml_version, $this->xml_codif);
		$xml->formatOutput = true;
		
		//creamos a raiz
		$chave = key(array_slice($datos, 0, 1, true));
		if ($chave == "@raiz")
		{
			$raiz = $xml->createElement($this->json_root);
			$raiz = $xml->appendChild($raiz);
		}
		else
		{
			$raiz = $xml->createElement($chave);
			$raiz = $xml->appendChild($raiz);
		}
				
		$xml->appendChild($this->escribir_recursivo($datos[$chave][0],$raiz,$xml));
		
		if ($ficheiro == null)
			$xml->save($this->ficheiro);
		else
			$xml->save($ficheiro);
	}
	
	private function escribir_recursivo($array = null, $xml = null, $doc = null)
	{
		$i = 0;
		for ($i = 0; $i<count($array); $i++)
		{
			$chave = key(array_slice($array, $i, 1, true));

			if ($chave == "@atributos")
			{
				$j = 0;
				$temp = $array[$chave];
				for ($j = 0; $j<count($temp); $j++)
				{
					$chave = key(array_slice($temp, $j, 1, true));
					$atributo = $doc->createAttribute($chave);
					$atributo->value = $temp[$chave];
					$xml->appendChild($atributo);
				}
			} 
			else if ($chave == "@valor")
			{
				$xml->nodeValue = $array[$chave];
			}
			else
			{
				$j = 0;
				$temp = $array[$chave];
				for ($j = 0; $j<count($temp); $j++)
				{
					$elemento = $doc->createElement($chave);
					$xml->appendChild($this->escribir_recursivo($temp[$j],$elemento,$doc));
					
				}
			}		
		}
		return $xml;
	}
	
	private function escribir_json()
	{
		$array = array();
		
		$chave = key(array_slice($this->datos, 0, 1, true));

		if ($chave == "@raiz")
		{
			$array = $this->a_json_recursivo($this->datos[$chave]);
		}
		else
		{
			$array[$chave] = $this->a_json_recursivo($this->datos[$chave]);
		}
		
		$chave = key(array_slice($array, 0, 1, true));
		if (!is_numeric($chave))
		{
			if ($chave == $this->json_root)
				$array = $array[$chave];
		}
		else
		{
			$chave2 = key(array_slice($array[$chave], 0, 1, true));
			if ($chave2 == $this->json_root)
				$array = $array[$chave][$chave2];
			else
				$array = $array[$chave];
		}
		if (isset($array[$chave][0]))
			$array[$chave] = $array[$chave][0];

		return json_encode($array);
	}
	
	private function a_json_recursivo($datos)
	{
		$array = array();
		for ($i = 0; $i < count($datos); $i++)
		{
			$chave = key(array_slice($datos, $i, 1, true));
			if (array_key_exists("@atributos",$datos))
			{
				if ($chave == "@atributos")
				{
					$j = 0;
					
					foreach ($datos[$chave] as $atributo)
					{
						$chave2 = key(array_slice($datos["@atributos"], $j, 1, true));
						$array[$chave2] = $atributo;
						$j++;
					}
				}
				else
					if ($chave == "@valor")
					{
						if (strlen($datos["@valor"]))
							$array[$this->xml_value] = $datos["@valor"];
					}
					else
					{
						if (!isset($array[$chave]))
							$array[$chave] = array();
						if (gettype($datos[$chave]) == "array")
						{
							if (count($datos[$chave]) > 1)
							{
								foreach ($datos[$chave] as $aux)
								{
									$temp = $this->a_json_recursivo($aux);
									$chave_temp = key(array_slice($temp, 0, 1, true));
									if (is_numeric($chave_temp))
										$temp = $temp[$chave_temp];
									if (count($temp) > 0)
									{
										$array[$chave][] = $temp;
									}
									else
										$array = "";
								}
							}
							else
							{
								$temp = $this->a_json_recursivo($datos[$chave]);
								$chave_temp = key(array_slice($temp, 0, 1, true));
								if (is_numeric($chave_temp))
									$temp = $temp[$chave_temp];
								if (count($temp) > 0)
									$array[$chave] = $temp;
								else
									$array = "";

							}
						}
						else 
						{
							$temp = $this->a_json_recursivo($datos[$chave]);
							$chave_temp = key(array_slice($temp, 0, 1, true));
							if (is_numeric($chave_temp))
								$temp = $temp[$chave_temp];
							if (count($temp) > 0)
								$array[$chave] = $temp;
							else
								$array = "";
						}
					}
			}
			else
			{
				if (!isset($array[$chave]))
							$array[$chave] = array();
				if (gettype($datos) == "array")
				{
					if (count($datos) > 1)
						foreach ($datos as $aux)
						{
							$temp = $this->a_json_recursivo($aux);
							if (count($temp) > 0)
								$array[$chave][] = $temp;
							else
								$array = "";
						}
					else 
					{
						$temp = $this->a_json_recursivo($datos[$chave]);
						$chave_temp = key(array_slice($temp, 0, 1, true));
							if (is_numeric($chave_temp))
								$temp = $temp[$chave_temp];
						if (count($temp) > 0)
							$array[$chave] = $temp;
						else
							$array = "";
					}
				}
			}
			
		}
		return $array;
	}
		
	private function get_nodo_recursivo($array, $key = "", $atributo = null, $idAtributo = null)
	{
		if (array_key_exists($key,$array))
		{
			if ($atributo == null)
				foreach ($array[$key] as $elemento)
				{
					$this->temp[$key][] = $elemento;
				}
			else	
				foreach ($array[$key] as $elemento)
				{
					if ((gettype($elemento) == "array"))
					{
						$atributos = $elemento["@atributos"];
					if (!($idAtributo == null))
						if (array_key_exists($atributo,$atributos)) 
							if ($atributos[$atributo] == $idAtributo)
								$this->temp[$key][] = $elemento;	
					}
				}
			foreach ($array as $elemento)
			{
				if ((gettype($elemento) == "array"))
				{
					$this->get_nodo_recursivo($elemento,$key,$atributo,$idAtributo);
				}
			}	
		}
		else
		{
			foreach ($array as $elemento)
			{
				if ((gettype($elemento) == "array"))
				{
					$this->get_nodo_recursivo($elemento,$key,$atributo,$idAtributo);
				}
			}
		}	
	}
	
	private function get_nodo_pai_recursivo($array = null, $key = "", $atributo = null, $idAtributo = null, $pai = null, $atributoPai = null, $idAtributoPai = null)
	{
		if (array_key_exists($pai,$array))
		{
			if ($atributoPai == null)
			{
				$temporal = $array[$pai];
				$this->get_nodo_recursivo($temporal,$key,$atributo,$idAtributo);
			}
			else
			{
				foreach ($array[$pai] as $elemento)
				{
					if ((gettype($elemento) == "array"))
					{
						$atributos = $elemento["@atributos"];
						if (!($idAtributoPai == null))
							if (array_key_exists($atributoPai,$atributos)) 
								if ($atributos[$atributoPai] == $idAtributoPai)
									$this->get_nodo_recursivo($elemento,$key,$atributo,$idAtributo);
					}
				}
			}
		}
		else
		{
			foreach ($array as $elemento)
			{
				if ((gettype($elemento) == "array"))
				{
					$this->get_nodo_pai_recursivo($elemento,$key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai);
				}
			}
		}
	
	}
	
	private function engadir_nodo_recursivo($array = null, $key = "", $datos = null, $pai = null, $atributoPai = null, $idAtributoPai = null)
	{ 	
		if (array_key_exists($pai,$array))
		{
			if ($atributoPai != null)
			{
				if (array_key_exists("@atributos",$array[$pai]))
				{
					if (!($idAtributoPai == null))
						if (array_key_exists($atributoPai,$array[$pai]["@atributos"]))
						{
							if ($array[$pai]["@atributos"][$atributoPai] == $idAtributoPai)
							{
								$array[$pai][$key][] = $datos;
								return $array;
							}
							else 
								return $array;
						}
						else
							return $array;
				}
				else
				{
					$i = 0;
					for ($i = 0; $i < count($array[$pai]); $i++)
					{
						if (array_key_exists($atributoPai,$array[$pai][$i]["@atributos"]))
						{
							if (!($idAtributoPai == null))
								if (!($idAtributoPai == null))
								if ($array[$pai][$i]["@atributos"][$atributoPai] == $idAtributoPai)
								{
									$array[$pai][$i][$key][] = $datos;
									return $array;
								}
						}
					}
				}
			}
			else
			{
				for ($i = 0; $i < count($array[$pai]); $i++)
				{
					$array[$pai][$i][$key][] = $datos;
					return $array;
				}
				return $array;
			}
		}
		else
		{
			$i = 0;
			for ($i = 0; $i < count($array); $i++)
			{
				$chave = key(array_slice($array, $i, 1, true));
				if ((gettype($array[$chave]) == "array"))
				{
					$array[$chave] = $this->engadir_nodo_recursivo($array[$chave],$key,$datos,$pai,$atributoPai,$idAtributoPai);	
				}		
			}
		}
		return $array;
	}
	
	private function eliminar_nodo_recursivo($array = null, $key = "", $atributo, $idAtributo = null)
	{
		if (array_key_exists($key,$array))
		{
			if ($atributo == null)
			{
				unset($array[$key]);
				
				for ($i = 0; $i < count($array); $i++)
				{
					$chave = key(array_slice($array, $i, 1, true));
					if ((gettype($array[$chave]) == "array"))
						$array[$chave] = $this->eliminar_nodo_recursivo($array[$chave],$key,$atributo,$idAtributo);
				}
				
				return $array;
			}
			else
			{
				$i = 0;
				foreach ($array[$key] as $elemento)
				{
					if (!($idAtributo == null))
						if (array_key_exists($atributo,$elemento["@atributos"]))
						{
							if ($elemento["@atributos"][$atributo] == $idAtributo)
							{
								unset($array[$key][$i]);
								if (count($array[$key]) == 0)
									unset($array[$key]);
								else
									$array[$key] = array_values($array[$key]);
							}
							$i++;
					}
				}
			}
			for ($i = 0; $i < count($array); $i++)
			{
				$chave = key(array_slice($array, $i, 1, true));
				if ((gettype($array[$chave]) == "array"))
					$array[$chave] = $this->eliminar_nodo_recursivo($array[$chave],$key,$atributo,$idAtributo);
			}
			return $array;
		}
		else
		{
			for ($i = 0; $i < count($array); $i++)
			{
				$chave = key(array_slice($array, $i, 1, true));
				if ((gettype($array[$chave]) == "array"))
					$array[$chave] = $this->eliminar_nodo_recursivo($array[$chave],$key,$atributo,$idAtributo);
			}
			return $array;
		}
	}
	
	private function eliminar_nodo_recursivo_pai($array = null, $key = "", $atributo = null, $idAtributo = null, $pai = null, $atributoPai = null, $idAtributoPai = null)
	{
		if ($pai == null)
		{
			$array = $this->eliminar_nodo_recursivo($array,$key,$atributo,$idAtributo);
			return $array;
		}
		else
		{
			if ($atributoPai == null)
			{
				if (array_key_exists($pai,$array))
				{
					$array[$pai] = $this->eliminar_nodo_recursivo($array[$pai],$key,$atributo,$idAtributo);
					
					for ($i = 0; $i < count($array); $i++)
					{	
						$chave = key(array_slice($array, $i, 1, true));
						if ((gettype($array[$chave]) == "array"))
							$array[$chave] = $this->eliminar_nodo_recursivo_pai($array[$chave],$key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai);
					}
					return $array;
				}
				else
				{
					for ($i = 0;$i <count($array); $i++)
					{
						$chave = key(array_slice($array, $i, 1, true));
						if ((gettype($array[$chave]) == "array"))
							$array[$chave] = $this->eliminar_nodo_recursivo_pai($array[$chave],$key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai);
					}
					return $array;
				}
			}
			else 
			{
				if (array_key_exists($pai,$array))
				{
					for ($i = 0; $i < count($array[$pai]); $i++)
					{
						$chave = key(array_slice($array[$pai], $i, 1, true));
						if(!($idAtributoPai == null))
							if (array_key_exists($atributoPai,$array[$pai][$chave]["@atributos"]))
							{
								if ($array[$pai][$chave]["@atributos"][$atributoPai] == $idAtributoPai)
								{
									$array[$pai][$chave] = $this->eliminar_nodo_recursivo($array[$pai][$chave],$key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai);
								}
							}
					}
					
					for ($i = 0; $i < count($array); $i++)
					{	
						$chave = key(array_slice($array, $i, 1, true));
						if ((gettype($array[$chave]) == "array"))
							$array[$chave] = $this->eliminar_nodo_recursivo_pai($array[$chave],$key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai);
					}
					
					return $array;
				}
				else
				{
					for ($i = 0;$i <count($array); $i++)
					{
						$chave = key(array_slice($array, $i, 1, true));
						if ((gettype($array[$chave]) == "array"))
							$array[$chave] = $this->eliminar_nodo_recursivo_pai($array[$chave],$key,$atributo,$idAtributo,$pai,$atributoPai,$idAtributoPai);
					}
					return $array;
				}
			}
		}
	}
}
?>
