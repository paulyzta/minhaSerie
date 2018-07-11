<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php
include 'conecta.php';
global $conn;

$url = 'https://minhaserie.org/';

error_reporting(E_ALL & ~ E_NOTICE);
ini_set('max_execution_time', 300);

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


function pegaSeries($url) {
	//Verifica se tem series no DB
	global $conn;
	$sql = 'SELECT * FROM series WHERE chupado = 0 LIMIT 10';
	$result = $conn->query($sql);
	$qtdRow = $result->num_rows;
	

	if ($qtdRow > 0) {
		//pega a lista q veio do banco
		foreach ($result AS $r) {
			//$minhasSeries[$r['nome']] = $r['url'];
			$minhasSeries[$r['id']] = $r;
		}
		//print_r($minhasSeries);
		//die();
		return $minhasSeries;
		//die();	
	} else {
		//Vai no site e pega da LI e salva no DB
		$doc = testURL($url);
	
		if ($doc) {
			$sidebar = $doc->getElementById('sidebars');
			$as = $sidebar->getElementsByTagName('a');

			foreach ($as AS $a) {
				if ( strstr($a->getAttribute('href'), '/tvshows/') ) {
					$nomeSerie = str_replace('/tvshows/', '', $a->getAttribute('href'));
					$minhasSeries[$nomeSerie] = 'https://minhaserie.org/tvshows/'.$nomeSerie;
					
					insereSeries($nomeSerie, 'https://minhaserie.org/tvshows/'.$nomeSerie);
				}	
			}

			//Enquanto desenvolvo... so pra diminuir o array
			//$minhasSeries = array_slice($minhasSeries, 34, 5);

			return $minhasSeries;
		} else {
			return false;
		}
	}




	
}

function insereSeries($nome, $url) {
	global $conn;
	$sql = "
		INSERT INTO listaseriestemp (nome, url)
		SELECT * FROM (SELECT '".$nome."', '".$url."') AS tmp
		WHERE NOT EXISTS (
			SELECT nome FROM listaseriestemp WHERE nome = '".$nome."'
		) LIMIT 1
	";
	$result = $conn->query($sql);
	//var_dump($result);
	//die('inseri');
}

function insereTemporadas($idSerie, $NomeTemporada, $urlTemporada, $imgTemporada, $sinopseTemporada, $arrEpisodiosTemporada) {
	global $conn;
	$sql = "
	INSERT INTO temporadas (
		id_serie,
		nome_temporada,
		url_temporada,
		img_temporada,
		sinopse_temporada,
		qtdEps_temporada
	) VALUES (
		'".$idSerie."',
		'".$NomeTemporada."',
		'".$urlTemporada."',
		'".$imgTemporada."',
		'".$sinopseTemporada."',
		'".count($arrEpisodiosTemporada)."'
	)";

	$result = $conn->query($sql);

	if($result) {
		$idInserido = mysql_insert_id();
		var_dump($idInserido);

		foreach ($arrEpisodiosTemporada AS $ep) {
			print_r($ep);
			die('aqui');
		}


		//chama a nova parte para inserir os EPs
	}

	$idInserido = mysql_insert_id();

	var_dump($idInserido);

	echo 'foi?';
	die($result);

}

function pegaEpisodio( $arrSerie, $nomeSerie ) {
	//print_r($arrSerie);die();
	foreach ($arrSerie AS $t) {
		$u = $t['url'];
		$doc = testURL($u);
		
		$temporada = substr( str_replace( '/'.mb_strtolower($nomeSerie).'-', '', strrchr($u, '/') ), 0, -5);

		$sinopse = trim($doc->getElementsByTagName('p')[0]->nodeValue);
		$trs = $doc->getElementsByTagName('tr');

		foreach ($trs AS $tr) {
			$titulo       = explode(' – ', $tr->childNodes[1]->nodeValue );
			$numEpisodio  = str_replace('Episódio ', '', $titulo[0]);
			$nomeEpisodio = $titulo[1];

			$Dub = $tr->childNodes[3]->childNodes[0]->childNodes[0];
			$Leg = $tr->childNodes[5]->childNodes[0]->childNodes[0];

			$links = $tr->getElementsByTagName('a');

			foreach ($links AS $l) {
				if ( $l->hasAttribute('href') ) {
					$arrEpisodio[$l->nodeValue] = $l->getAttribute('href');					
				}
			}
			//Agrupa os links dos episodios em um array so
			$arrEpisodios[$numEpisodio] = $arrEpisodio;			
		}

		
		
		$result[$nomeSerie][$temporada] = array(
			'url'       => $t['url'],
			'img'       => $t['img'],
			'Sinopse'   => $sinopse,
			'Episodios' => $arrEpisodios
		);

		print_r($result);
		insereTemporadas($t['id'], $t['nome'], $t['url'], $t['img'], $sinopse, $arrEpisodios);
		
	}
	//print_r($result);die();
		
	return $result;
	
}

function testURL($url) {

	if ($url) {
		$arquivo = 'arquivos/tvshows-arrow.html';
	}

	$doc = new DOMDocument();
	@$doc->loadHTMLFile($arquivo);

	


	$contentBox = $doc->getElementById('content_box');
	var_dump($contentBox);

	var_dump($doc);
	die('aquiiii');
	return $doc;



	//print_r(get_headers($url));die('55555');
	if (strstr(get_headers($url)[0], '404') ) {
		//Erro: HTTP/1.1 404 Not Found
		return false;
	} elseif (strstr(get_headers($url)[0], '503') ) {
		//Erro: HTTP/1.1 503 Service Temporarily Unavailable
		echo '<h1>Erro 503. Site nao disponivel agora... tente mais tarde.</h1>';
		die('-=FIM=-');
	}	

	$doc = new DOMDocument();
	@$doc->loadHTML(pegaConteudo($url));		
	return $doc;
}


//Inicio

$minhasSeries = pegaSeries($url);

echo '<pre>';
print_r($minhasSeries);
//die();



foreach ($minhasSeries AS $s) {
	//Primeiro loop. Descobre o link -season-X.html da serie.	
	echo '<h3>Estou nesta série: '.$s['nome'].'</h3>';

	$pageLimite = 10;	
	for ($i = 1; $i <= $pageLimite; $i++) {
		//echo $s['url'].'/page/'.$i;	
		$doc = testURL( $s['url'].'/page/'.$i );

		if($doc) {
			$contentBox = $doc->getElementById('content_box');
			
			//var_dump($doc);
			die();



			$as = $contentBox->getElementsByTagName('a');
			

			foreach ($as AS $a) {
				$img = $a->childNodes[1]->childNodes[0];
				if ($img->nodeName === 'img') {
					$arr[$s['nome']][] = array(
						'id'   => $s['id'],
						'nome' => $s['nome'],
						'img'  => $img->getAttribute('src'),
						'url'  => $a->getAttribute('href')
					);
				}
			}
		} else {
			//Link invalido. Sai do loop...
			break;
		}		
	}


	
	$danilo = pegaEpisodio( $arr[$s['nome']], $s['nome'] );
	print_r( $danilo );
	die();

	//aqui

	//print_r( $arr[$nomeDaSerie] );
	//echo 'sai do for';
	//die('sss');
	
	
}
print_r( $danilo );

die('-= FIM =-');
