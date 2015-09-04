<?php
	
	/*
		@author Alisson Linneker <alissonlinneker@gmail.com>
		@website www.Alisson.eng.br
	*/
	
	class MECApi
	{
		/*
		Função para listar os municípios do estado com seus respectivos códigos
		*/
		function get_municipios($sigla /* 2 dígitos maiúsculos */)
		{
			$str = file_get_contents("http://emec.mec.gov.br/emec/comum/json/selecionar-municipio/".md5("sg_uf")."/".base64_encode($sigla));
			return array_column(json_decode($str, true), 'co_municipio', 'ds_municipio');
		}

		/*
			Função para obter as instituições à partir do código do município
		*/
		function get_instituicoes($cod_municipio)
		{
			include_once('./src/simple_html_dom.php');
			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL, 'http://emec.mec.gov.br/emec/nova-index/listar-consulta-avancada/list/1000'); 
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0');
			curl_setopt($ch, CURLOPT_ENCODING , "gzip");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "data%5BCONSULTA_AVANCADA%5D%5Bhid_template%5D=listar-consulta-avancada-ies&data%5BCONSULTA_AVANCADA%5D%5Bhid_order%5D=ies.no_ies+ASC&data%5BCONSULTA_AVANCADA%5D%5Bhid_no_cidade_avancada%5D=&data%5BCONSULTA_AVANCADA%5D%5Bhid_no_regiao_avancada%5D=&data%5BCONSULTA_AVANCADA%5D%5Bhid_no_pais_avancada%5D=&data%5BCONSULTA_AVANCADA%5D%5Bhid_co_pais_avancada%5D=&data%5BCONSULTA_AVANCADA%5D%5Brad_buscar_por%5D=IES&data%5BCONSULTA_AVANCADA%5D%5Btxt_no_ies%5D=&data%5BCONSULTA_AVANCADA%5D%5Btxt_no_curso%5D=&data%5BCONSULTA_AVANCADA%5D%5Btxt_no_especializacao%5D=&data%5BCONSULTA_AVANCADA%5D%5Bsel_co_area%5D=&data%5BCONSULTA_AVANCADA%5D%5Bsel_sg_uf%5D=MG&data%5BCONSULTA_AVANCADA%5D%5Bsel_co_municipio%5D=".$cod_municipio."&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_natureza_gn%5D%5B%5D=3&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_natureza_gn%5D%5B%5D=1&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_natureza_gn%5D%5B%5D=2&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_natureza_gn%5D%5B%5D=5&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_natureza_gn%5D%5B%5D=4&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_natureza_gn%5D%5B%5D=6&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_natureza_gn%5D%5B%5D=7&data%5BCONSULTA_AVANCADA%5D%5Bsel_st_gratuito%5D=&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_organizacao_gn%5D%5B%5D=10022%2C10024%2C10023%2C10027&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_organizacao_gn%5D%5B%5D=10019%2C10020%2C10021%2C10026&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_organizacao_gn%5D%5B%5D=10026%2C10019&data%5BCONSULTA_AVANCADA%5D%5Bchk_tp_organizacao_gn%5D%5B%5D=10028%2C10029&data%5BCONSULTA_AVANCADA%5D%5Bsel_no_indice_ies%5D=&data%5BCONSULTA_AVANCADA%5D%5Bsel_co_indice_ies%5D=&data%5BCONSULTA_AVANCADA%5D%5Bsel_no_indice_curso%5D=&data%5BCONSULTA_AVANCADA%5D%5Bsel_co_indice_curso%5D=&data%5BCONSULTA_AVANCADA%5D%5Bsel_co_situacao_funcionamento_ies%5D=10035&data%5BCONSULTA_AVANCADA%5D%5Bsel_co_situacao_funcionamento_curso%5D=9&data%5BCONSULTA_AVANCADA%5D%5Bsel_st_funcionamento_especializacao%5D=&captcha=");
			$buffer = curl_exec($ch);  
			curl_close($ch);
			
			 $dom = new domDocument; 

			@$dom->loadHTML($buffer);
			$dom->preserveWhiteSpace = false;
			$tables = $dom->getElementsByTagName('tr'); 
			
			foreach ($tables as $row) 
			{ 
				$cols = $row->getElementsByTagName('td'); 
				if($cols->length == 8)
				{
					$array[$cols->item(0)->nodeValue] = array(
					'IES' => $cols->item(1)->nodeValue,
					'organizacao' => $cols->item(2)->nodeValue,
					'categoria' => $cols->item(3)->nodeValue,
					'CI' => $cols->item(4)->nodeValue,
					'IGC' => $cols->item(5)->nodeValue,
					'situacao' => $cols->item(6)->nodeValue
					);
				}
			}
			return $array;
		}
		
		/*
			Função para obter o endereço de cada campus da instituição
		*/
		function get_instituicao_enderecos($cod)
		{
			$html = file_get_contents('http://emec.mec.gov.br/emec/consulta-ies/listar-endereco/d96957f455f6405d14c6542552b0f6eb/'.base64_encode($cod).'/list/1000');
			
			include_once('./src/simple_html_dom.php');
			
			$dom = new domDocument; 
			@$dom->loadHTML($html);
			$dom->preserveWhiteSpace = false;
			$tables = $dom->getElementsByTagName('tbody'); 
			
			foreach ($tables as $row) 
			{
				$cols = $row->getElementsByTagName('td'); 
				$array[trim($cols->item(0)->nodeValue)] = array(
				'denominacao' => trim($cols->item(1)->nodeValue),
				'endereco' => trim($cols->item(2)->nodeValue),
				'polo' => trim($cols->item(3)->nodeValue),
				'municipio' => trim($cols->item(4)->nodeValue),
				'UF' => preg_replace("/[^A-Z{2}]/", "", $cols->item(5)->nodeValue)
				);
			}
			return $array;
		}
		
		/*
			Função para obter cada curso de um determinado campus de uma instituição
		*/
		function get_instituicao_cursos($cod_endereco, $cod_instituicao) 
		{
			$html = file_get_contents('http://emec.mec.gov.br/emec/consulta-ies/listar-curso-endereco/d96957f455f6405d14c6542552b0f6eb/'.base64_encode($cod_instituicao).'/aa547dc9e0377b562e2354d29f06085f/'.base64_encode($cod_endereco).'/list/1000');
			include_once('./src/simple_html_dom.php');
			
			$dom = new domDocument; 
			@$dom->loadHTML($html);
			$dom->preserveWhiteSpace = false;
			$tables = $dom->getElementsByTagName('tbody'); 
			
			foreach ($tables as $row) 
			{
				$cols = $row->getElementsByTagName('td'); 
				$array[] = preg_replace("/[^A-Za-z]/", "", $cols->item(0)->nodeValue);
			}
			return $array;
		}
	}
	
	$mec = new MECApi;
	
	echo "<pre>";
	
	// Exemplos: 
	
	// print_r($mec->get_municipios('MG')); // Lista todos os municípios de Minas Gerais
	// print_r($mec->get_instituicoes('000000003106200')); // Obtem lista de instituições de Belo Horizonte (Cod 000000003106200)
	// print_r($mec->get_instituicao_enderecos('575')); // Obtem lista de endereços de cada campus da UFMG (Instituição Cod 575)
	// print_r($mec->get_instituicao_cursos('34819', '575')); // Obtem lista de cursos do "Campus Saúde" (Endereço Cod 34819) da instituição UFMG (Instituição Cod 575)
	
?>