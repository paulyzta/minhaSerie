<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<?php

$url = 'http://www.bludvfilmes.com/zombies-2018-web-dl-720p-e-1080p-dublado-dual-audio-torrent/';
$classe = 'post-body';

error_reporting(E_ALL & ~ E_NOTICE);

function file_get_contents_curl($url) {
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



function infoFilme($dados) {
	$d = explode( "\n", trim($dados) );
	
	foreach ($d AS $dado) {
		
		if ( strstr($dado, 'Vídeo:')) {
			$dado = str_replace('Vídeo: ', 'V = ', str_replace('Áudio: ', 'Qualidade: A = ', $dado));
		}
		
		$dados = explode( ':', trim($dado) );
		$legenda = trim( $dados[0] == 'Titulo do Filme' ? 'Nome' : $dados[0] );
		$info = trim( $dados[1] );
		$arr[$legenda] = $info;
	}
	return $arr;	
}




$content = file_get_contents_curl($url);
$doc = new DOMDocument();
@$doc->loadHTML($content);
$xpath = new DOMXpath($doc);

$links = [];
$infos = [];
$filme = [];

/*
$table = $doc->getElementsByTagName('table');

var_dump($table);

die();
*/

$expression = './/div[contains(concat(" ", normalize-space(@class), " "), "'. $classe .'")]';
$alvo = $xpath->evaluate($expression);


echo '<pre>';
foreach ($alvo as $div) {
	//print_r($div);
	foreach ($div->childNodes AS $item) {
		//print_r($item);
		
		//Dados do flime ou série
		if (strstr($item->nodeValue, 'Titulo do Filme:')) {
			$filme['infos'] = infoFilme( str_replace( "Ficha Técnica\n", '', trim($item->nodeValue) ) );
		}
		
		//Sinopse do filme ou serie
		if (strstr($item->nodeValue, 'Sinopse:')) {
			$filme['sinopse'] = trim( str_replace("Sinopse:\n", '', $item->nodeValue) );
		}
		
		//Trailer do filme
		if ($item->childNodes[0]->tagName == 'iframe') {
			$filme['trailer'] = $item->childNodes[0]->getAttribute('src');
		}
		
		//Links megnet
		if ($item->childNodes[0]->tagName === 'a' && strstr($item->childNodes[0]->getAttribute('href'), 'magnet:?' ) ) {
			//print_r($item);
			//die();
			//echo '<|'.$item->parentNode->previousSibling->nodeValue.'|><br>';
			echo '<|'.$item->previousSibling->nodeValue.'|><br>';
			print_r($item->parentNode->previous_sibling);
			echo $item->nodeValue.'<br>';
			
			echo $item->childNodes[0]->getAttribute('href').'<br>';
			echo '-----<br>';
		}
		
		
	}
}
echo '<hr>';
print_r($filme);
die();

$expression = './/div[contains(concat(" ", normalize-space(@class), " "), " $classe ")]';

foreach ($xpath->evaluate($expression) as $div) {
	
	/*
	
	falta pegar a IMG com a foto do filme!
	
	*/
	
	
	//print_r($div);
	foreach ($div->childNodes AS $key => $p) {
		//Dados do flime ou série
		if (strstr($p->nodeValue, 'Baixar Filme:')) {
			//$filme['infos'] = explode("\n", trim($p->nodeValue));
			
			infoFilme($p->nodeValue);
		}
		
		//Sinopse do filme ou serie
		if (strstr($p->nodeValue, 'SINOPSE:')) {
			$filme['sinopse'] = $p->nodeValue;
		}
		
		//Trailer do filme
		if ($p->childNodes[0]->tagName == 'iframe') {
			$filme['trailer'] = $p->childNodes[0]->getAttribute('src');
		}		
		
		//Torrents
		if (strstr($p->nodeValue, 'DOWNLOAD TORRENT')) {
			$n = str_replace('DOWNLOAD TORRENT BLURAY ', 'Resolucao', $p->childNodes[0]->nodeValue);
			$filmes['links'][$n] = $p->childNodes[0]->getAttribute('href');
		}
		
		
	}
}
echo '---';
print_r($filme);
die('sss');
foreach ($arr AS $item) {
	echo $item->getAttribute('href');
}


die('-----');
