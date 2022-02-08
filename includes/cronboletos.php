<?php
require_once('conexaoregistro.php');

function formata_cpf_cnpj($cpf_cnpj){
    /*
        Pega qualquer CPF e CNPJ e formata

        CPF: 000.000.000-00
        CNPJ: 00.000.000/0000-00
    */

    ## Retirando tudo que não for número.
    $cpf_cnpj = preg_replace("/[^0-9]/", "", $cpf_cnpj);
    $tipo_dado = NULL;
    if(strlen($cpf_cnpj)==11){
        $tipo_dado = "cpf";
    }
    if(strlen($cpf_cnpj)==14){
        $tipo_dado = "cnpj";
    }
    switch($tipo_dado){
        default:
            $cpf_cnpj_formatado = "Não foi possível definir tipo de dado";
        break;

        case "cpf":
            $bloco_1 = substr($cpf_cnpj,0,3);
            $bloco_2 = substr($cpf_cnpj,3,3);
            $bloco_3 = substr($cpf_cnpj,6,3);
            $dig_verificador = substr($cpf_cnpj,-2);
            $cpf_cnpj_formatado = $bloco_1.".".$bloco_2.".".$bloco_3."-".$dig_verificador;
        break;

        case "cnpj":
            $bloco_1 = substr($cpf_cnpj,0,2);
            $bloco_2 = substr($cpf_cnpj,2,3);
            $bloco_3 = substr($cpf_cnpj,5,3);
            $bloco_4 = substr($cpf_cnpj,8,4);
            $digito_verificador = substr($cpf_cnpj,-2);
            $cpf_cnpj_formatado = $bloco_1.".".$bloco_2.".".$bloco_3."/".$bloco_4."-".$digito_verificador;
        break;
    }
    return $cpf_cnpj_formatado;
}

$url = 'https://bling.com.br/Api/v2/contareceber/json/';

function executeSendOrder($url, $cnpj, $competencia, $cliente, $tipo, $valor, $email, $endereco, $numero, $bairro, $cidade, $uf, $cep){
	$xml = '<?xml version="1.0" encoding="UTF-8"?>
	<contareceber>
		<dataEmissao>' . date('d/m/Y') . '</dataEmissao>
		<vencimentoOriginal>' . date('d/m/Y', strtotime(date('Y-m-d'). ' + 5 days')) . '</vencimentoOriginal>
		<competencia>' . $competencia . '</competencia>
		<valor>' . $valor . '</valor>
		<historico>Referente a mensalidade do ULTRASIS Life ' . date('d/m/Y', strtotime(date('Y-m-d'). ' + 10 days')) . '</historico>
		<categoria>Manutenção</categoria>
		<idFormaPagamento>1247575</idFormaPagamento>
		<portador>Bling Conta</portador>
		<ocorrencia>
		   <ocorrenciaTipo>U</ocorrenciaTipo>
		</ocorrencia>
		<cliente>
		<nome>' . $cliente . '</nome>
		   <cpf_cnpj>' . $cnpj . '</cpf_cnpj>
		   <tipoPessoa>' . $tipo . '</tipoPessoa>
		   <email>' . $email . '</email>
		   <endereco>' . $endereco . '</endereco>
		   <numero>' . $numero . '</numero>
		   <bairro>' . $bairro . '</bairro>
		   <cidade>' . $cidade . '</cidade>
		   <uf>' . $uf . '</uf>
		   <cep>' . $cep . '</cep>
		</cliente>
	 </contareceber>';
	$posts = array (
		"apikey" => "c33d09b6f0d466366722f6226ffd75c79f8956b80ec293da7e32c8bc792a90de24f6ff96",
		"xml" => rawurlencode($xml)
	);
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_POST, count($posts));
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $posts);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($curl_handle);
    curl_close($curl_handle);
    return $response;
}

$apikey = "c33d09b6f0d466366722f6226ffd75c79f8956b80ec293da7e32c8bc792a90de24f6ff96";
$outputType = "json";

function executeGetContacts($url, $apikey){
   $curl_handle = curl_init();
   curl_setopt($curl_handle, CURLOPT_URL, $url . '&apikey=' . $apikey);
   curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
   curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
   $response = curl_exec($curl_handle);
   curl_close($curl_handle);
   return $response;
}

$datalog=date('Y-m-d H:i:s');
$retorno='';
$expira=date('Y-m-d', strtotime(date('Y-m-d'). ' + 10 days'));
$query = "select * from registros where gerado=0 and expira='".$expira."' order by id limit 4";
$result = array();
$res = mysqli_query($conexaoregistro,$query);
while($row = mysqli_fetch_assoc($res)) {
	$urlclientes = 'https://bling.com.br/Api/v2/contato/' . $row['cnpj'] . '/' . $outputType;
	$clientes = json_decode(executeGetContacts($urlclientes,$apikey),true);
	//print_r($clientes['retorno']['contatos'][0]['contato']['email']);
	$valor = 0;
	$tipo = 'J';
	if(strlen($row['cnpj'])==11){
		$tipo='F';
	}
	$life = $row['licencas_life'];
	$cap = $row['licencas_cap'];
	$clin = $row['licencas_clin'];
	if($row['pagamento']=='mensal'){
		if($life>0){
			if($row['plano']=='B'){ $valor = $valor + (45*$life); }
			if($row['plano']=='I'){ $valor = $valor + (75*$life); }
			if($row['plano']=='A'){ $valor = $valor + (110*$life); }
			if($row['plano']=='P'){ $valor = $valor + (150*$life); }
		}
		if($cap>0){
			$valor = $valor + (45*$cap);
		}
		if($clin>0){
			$valor = $valor + (45*$clin);
		}
	} else {
		if($life>0){
			if($row['plano']=='B'){ $valor = $valor + (485*$life); }
			if($row['plano']=='I'){ $valor = $valor + (810*$life); }
			if($row['plano']=='A'){ $valor = $valor + (1190*$life); }
			if($row['plano']=='P'){ $valor = $valor + (1620*$life); }
		}
		if($cap>0){
			$valor = $valor + (485*$cap);
		}if($clin>0){
			$valor = $valor + (485*$clin);
		}
	}
	$competencia = new DateTime($row['competencia']);
	$resposta = executeSendOrder($url, str_replace(array('.','-','/'),'',$row['cnpj']), $competencia->format('d/m/Y'), $row['cliente'], $tipo, $valor, explode(';',trim($clientes['retorno']['contatos'][0]['contato']['email']))[0], $clientes['retorno']['contatos'][0]['contato']['endereco'], $clientes['retorno']['contatos'][0]['contato']['numero'], $clientes['retorno']['contatos'][0]['contato']['bairro'], $clientes['retorno']['contatos'][0]['contato']['cidade'], $clientes['retorno']['contatos'][0]['contato']['uf'], $clientes['retorno']['contatos'][0]['contato']['cep']);
	//$resposta = json_decode($resposta);
	$resposta = json_decode($resposta,true);
	//mysqli_query($conexaoregistro,"insert into log (data, acao) VALUES ('".$datalog."', '".$resoista."')");
	//print_r($resposta);
	mysqli_query($conexaoregistro,"insert into log (data, acao, resposta) VALUES ('".$datalog."', 'novoBoleto###".$row['cliente']." (".$row['cnpj'].")###".$valor."###".date('d/m/Y', strtotime(date('Y-m-d'). ' + 5 days'))."', '".$resposta."')");
	//mysqli_query($conexaoregistro,"update registros set codboleto='".$resposta->retorno->contasreceber[0]->contaReceber->id."' where id=".$row['id']);
	$codboleto = $resposta['retorno']['contasreceber'][0]['contaReceber']['id'];
	if($codboleto==''){
		$codboleto = '0';
	}
	mysqli_query($conexaoregistro,"update registros set gerado=1, codboleto='".$codboleto."' where id=".$row['id']);
}
?>
