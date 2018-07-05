<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php
/*
Entra no site
Pega a lista completa das series do site
	vou salvar essa lista no DB e trabalhar quem ainda falta ou é novo pra baixar depois
Pegando do DB, vou varrer cada serie fazendo:
	Acesso o link salvo e pego: 
		ANO e MÊS da serie (formam o link),
		A foto da capa,
		crio a URL base,
		Nome da serie
		funcoes pros links/urls???
		
	Disso:
		crio a URL base da serie (primeira temporada)
*/

$url = 'https://minhaserie.org/';
$classe = 'post-body';

error_reporting(E_ALL & ~ E_NOTICE);

function pegaConteudo($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function pegaSerie($linkBase, $nomeDaSerie) {
	$url = $linkBase.$nomeDaSerie.'-season-';
	
	for ($i = 1; $i <= 10; $i++) {
		$u = $url.$i.'.html';
		$t = $i;
		$e = 1;
		
		echo $u;
		
		if ( @fopen($u, "r") ) {
			//Temporada existe. Vamos pegar sinopse e lista de EPs
			
			$doc = new DOMDocument();
			@$doc->loadHTML(pegaConteudo($u));	
			$trs = $doc->getElementsByTagName('tr');
			$sinopse = $doc->getElementsByTagName('p')[0]->nodeValue;			
			
			foreach ($trs AS $tr) {
				$arr['Nome'] = explode(' – ', $tr->childNodes[1]->nodeValue )[1];
				$Dub = $tr->childNodes[3]->childNodes[0]->childNodes[0];
				$Leg = $tr->childNodes[5]->childNodes[0]->childNodes[0];
				
				if ( $Dub->hasAttribute('href') ) {
					$arr[$Dub->nodeValue] = $linkBase.strrchr($Dub->getAttribute('href'), '/');					
				}
				
				if ( $Leg->hasAttribute('href') ) {
					$arr[$Leg->nodeValue] = $linkBase.strrchr($Leg->getAttribute('href'), '/');				
				}
				
				$arrEps['s'.$t.'e'.$e] = $arr;

				$e++;
				//die();
			}
			
		} else {
			//Temporada nao existente. Quebra o loop
			echo 'Nao achei temporada';
			break;
		}
	}
	
	$arrSerie = array('nome' => $nomeDaSerie, 'Sinopse' => $sinopse, 'EPs' => $arrEps);
	
	print_r($arrSerie);
	
	die('FIM');
	
	return $arrSerie;
	
}

function testURL($url) {
	//echo get_headers($url)[0];
	if (! strstr(get_headers($url)[0], '404') ) {
		
		$doc = new DOMDocument();
		@$doc->loadHTML(pegaConteudo($url));
		
		return $doc;
	}
	return false;
}

//$url = 'https://minhaserie.org/2017/08/assistir-breaking-bad-s1e1.html';



$doc = new DOMDocument();
@$doc->loadHTML(pegaConteudo($url));
//$xpath = new DOMXpath($doc);


$as = $doc->getElementsByTagName('a');

foreach ($as AS $a) {
	if ( strstr($a->getAttribute('href'), 'minhaserie.org/tvshows/') ) {
		$minhasSeries[] = $a->getAttribute('href');
	}	
}

//Enquanto desenvolvo... so pra diminuir o array
$minhasSeries = array_slice($minhasSeries, 30, 10);

echo '<pre>';
print_r($minhasSeries);
//die();



foreach ($minhasSeries AS $serie) {
	//Primeiro loop. Descobre o link -season-X.html da serie.
	$nomeDaSerie = str_replace('minhaserie.org/tvshows/', '', explode('//', $serie)[1]);
	echo '<h3>Estou nesta série: '.$nomeDaSerie.'</h3>';
	
	//echo $serie;
	
	$limite = 10;
	
	for ($i = 1; $i <= $limite; $i++) {
		//echo 'kkk';die();
		$url = $serie.'/page/'.$i;
		$dom = testURL($url);

		if($dom === false) {echo 'porra';break;} 
		
		$as = $dom->getElementById('featured-thumbnail');
		$div = $dom->getElementById('content_box');
		//$href = $as->getAttribute('href');
		
		$img = $as->childNodes[1]->childNodes[0]->getAttribute('src');
		
		foreach ($div->childNodes AS $d){
			if (@sizeof($d->childNodes) == 11) {
				echo $d->childNodes[1]->nodeName;
				//print_r($d->lastChild);
				die('sssssssssssss');			

			}
			//echo 'd';
			//echo 'pqp';
			//echo '-- '.$d->nodeValue;
			//echo $d->getAttribute('class');
			//echo $d->tagName;
			//echo '|'.$d->childNodes->tagName.'|L';
			print_r($d->childNodes);//die();
		}
		
		die();
		
		print_r( $as);
		
		
		//print_r($dom);
		die();
		
	}
	
	
}

die('-= FIM =-');



foreach ($minhasSeries AS $serie) {
	//Primeiro loop. Descobre o link -season-X.html da serie.
	$nomeDaSerie = str_replace('minhaserie.org/tvshows/', '', explode('//', $serie)[1]);
	echo '<h3>Estou nesta série: '.$nomeDaSerie.'</h3>';
		
	$doc = new DOMDocument();
	@$doc->loadHTML(pegaConteudo($serie));	
	$a = $doc->getElementById('featured-thumbnail');
	
	$linkTemp = $a->getAttribute('href');
	$img = $a->childNodes[1]->childNodes[0]->getAttribute('src');
	
	
	$linkBase = str_replace(strrchr($a->getAttribute('href'), '/'), '/', $a->getAttribute('href'));
	
	
	
	pegaSerie($linkBase, $nomeDaSerie);
	
	die($linkBase);
	
	
	echo '----'.$link = $a->getAttribute('href');
	
	
	
	//var_dump($a->getAttribute('href'));
	print_r($img);
	
	
	
	
	die('Testando...');
	
	foreach ($as AS $a) {
		if ( strstr($a->getAttribute('href'), $nomeDaSerie.'-season-') ) {
			echo '<h4>Já tem o link da primeira temporada. Vamos pegar sinopse.</h4>';
			
			$linkPrimeiraTemp = str_replace(strrchr($a->getAttribute('href'), '-'), '-1.html', $a->getAttribute('href'));
			$linkGenerico = str_replace(strrchr($a->getAttribute('href'), '/'), '/', $a->getAttribute('href'));
			$dados = explode('/', $linkGenerico);
			
			$protocolo = $dados[0];
			$url = $dados[2];
			$ano = $dados[3];
			$mes = $dados[4];
			
			$limite = 30;
			
			for ($i = 1; $i <= $limite; $i++){
				echo $i.'<br>';
				$url = $linkGenerico.$nomeDaSerie.'-season-'.$i.'.html';
				//print_r(get_headers($url, 1));
				if ( strstr(get_headers($url)[0], '200') ) {
					echo $url.'<br>';
				}
				
				//pegaConteudo($url);
			}
			
			
			
			
			
			echo $linkGenerico;die();
			
			$doc = new DOMDocument();
			@$doc->loadHTML(pegaConteudo($linkPrimeiraTemp));	
			$tds = $doc->getElementsByTagName('td');
			
			$sinopse = $doc->getElementsByTagName('p')[0]->nodeValue;
			
			//echo '<p>Sinopse: '.$sinopse.'</p>';
			
			echo '<pre>';
			foreach ($tds AS $td) {
				
				$filho = $td->childNodes[0]->childNodes[0];
				if ($filho->tagName === 'span') {
					//Nome do ep
					$nomeEp = explode(' – ', $td->nodeValue)[1];
					//$temp[$nomeEp] = array('Titulo' => $nomeEp);
				} elseif ($filho->tagName === 'a') {
					if ( $filho->getAttribute('href') ) {
						//$temp[$td->nodeValue][] = $filho->getAttribute('href');
						//$temp[$nomeEp]['Links'] = array( $td->nodeValue => $filho->getAttribute('href') );
						$temp[$nomeEp][$td->nodeValue] = $filho->getAttribute('href');
					}
				}
			}
			print_r($temp);
		
			break;
		}
		//https://minhaserie.org/2017/06/100-code-season-1.html
		//assistir-greys-anatomy-s5e2.html
		
		
	}
	echo 'FIM';
	die();
	
	echo '<pre>';
	print_r($minhasSeries);
	
	
}



