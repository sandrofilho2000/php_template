<?php
/**
 * Função utilizada para incluir automaticamente as classes utilizada nos arquivos
 * @param String $nomeDaClasse
 * @return boolean
 */
spl_autoload_register(function($class_name) {
    //include $class_name . '.php';
    $path = CLASSES . $class_name . '.php';
    if (file_exists($path) ) {
        require_once($path);
        return true;
    }
});
function abreviarNome($fullName, $limit){
    $fullName = trim($fullName);
    if (strlen($fullName) > $limit) {
        $nomes = explode(' ', $fullName);
        $indiceAtual = count($nomes) - 2;
        $proibirAbreviacao = array('DA', 'DE', 'DO', 'DOS', 'E', 'da', 'de', 'do', 'dos', 'e');
        while ($indiceAtual > 0) {
            // Verifica se o nome atual não está na lista de proibição de abreviação
            if (!in_array($nomes[$indiceAtual], $proibirAbreviacao, true)) {
                // Abrevia o nome para a primeira letra seguida de um ponto
                $nomes[$indiceAtual] = substr($nomes[$indiceAtual], 0, 1) . '.';
            }
            // Recria o nome completo com as abreviações
            $fullName = implode(' ', $nomes);
            // Se o nome abreviado se encaixa no limite, retorna
            if (strlen($fullName) <= $limit) {
                return $fullName;
            }
            $indiceAtual--;
        }
    }
    // Retorna o nome completo (original ou abreviado se não couber mais)
    return $fullName;
}
function abreviar_nome($nome_completo) {
    $_parte_nome = explode(' ', $nome_completo);
    $nome_abreviado = $_parte_nome[0];
    if (count($_parte_nome > 1)) {
        $nome_abreviado .= ' ' . end($_parte_nome);
    }
    return $nome_abreviado;
}
function reformatar_moeda($valor) {
    if($valor == ''){
        return $valor;
    }
    $search = array('.', 'R$', 'R$ ', ' %', '%', ',', ' ');
    $replace = array('', '', '', '', '', '.', '');
    $novo_valor = str_replace($search, $replace, $valor);
    return $novo_valor;
}
function formatar_moeda($num, $cifrao = FALSE) {
    try{
        $valor = number_format($num, 2, ',', '.');
        if ($cifrao) {
            $valor = 'R$ ' . $valor;
        }
        return $valor;
    } 
    catch (Exception $ex) {
        return $num;
    }
}
/**
 * 
 * @param type $val
 * @param type $mascara
 * @return type
 * @exemplo echo mask($cpf,'###.###.###-##'); echo mask($cep,'#####-###'); echo mask($data,'##/##/####');
 */
function mascarar($val, $mask) {
    $maskared = '';
    $k = 0;
    for ($i = 0; $i <= strlen($mask) - 1; $i++) {
        if ($mask[$i] == '#') {
            if (isset($val[$k]))
                $maskared .= $val[$k++];
        }
        else {
            if (isset($mask[$i]))
                $maskared .= $mask[$i];
        }
    }
    return $maskared;
}
/**
 * Remove alguns caracteres (".", "/", "-", "(", ")", " ") de uma string
 * @param string $valor string contendo os caracteres a serem removidos
 * @return string a string sem os caracteres
 */
function removerMascara($valor){
    $caracteres = array(".", "/", "-", "(", ")", " ");
    return str_replace($caracteres, "", $valor);
}
/**
 * Informa se a data passada por parametro eh uma data considerando o formato do segundo parametro
 * @param type string $input - data a ser verificada
 * @param type string $format - formato da data a ser considerada
 * @return type boolean
 */
function isDate($input, $format = 'Y-m-d'){
    return (DateTime::createFromFormat($format, $input) !== FALSE);
}
/**
 * Altera uma data para outro formato
 *
 * @param string $date String contendo a data a ser formatada
 * @param string $outputFormat Formato de saida
 * @throws Exception Quando não puder converter a data
 * @return string Data formatada
 */
function parseDate($date, $outputFormat = 'd/m/Y'){
    if(empty($date)){
        return '';
    }
    $formats = array(
        'd/m/Y',
        'd/m/Y H',
        'd/m/Y H:i',
        'd/m/Y H:i:s',
        'Y-m-d',
        'Y-m-d H',
        'Y-m-d H:i',
        'Y-m-d H:i:s',
        'YmdHis'
    );
    foreach($formats as $format){
        $dateObj = DateTime::createFromFormat($format, $date);
        if($dateObj !== false){
            break;
        }
    }
    if($dateObj === false){
        throw new Exception('Data inválida: ' . $date);
    }
    return $dateObj->format($outputFormat);
}
/**
 * Formata uma string moeda R$ para o banco de dados
 * @param type $input valor
 * @return type string
 */
function parseMoney($input){
    if(substr($input, -3, 1) == ','){
        $_search = array('.', ',');
        $_replace = array('', '.');
        $output = str_replace($_search, $_replace, $input);
        return $output;
    }
    return $input;
}
/**
 * Gerador de senhas
 * @param Integer $tamanho números de caracteres da senha
 * @param Boolean $maiusculas se deve possuir letras maiúsculas na senha
 * @param Boolean $numeros se deve possuir números na senha
 * @param Boolean $simbolos se deve possuir símbolos na senha
 * @return string a senha gerada
 */
function gerar_senha($tamanho = 8, $maiusculas = true, $numeros = true, $simbolos = false) {
    $lmin = 'abcdefghijkmnpqrstuvwxyz';
    $lmai = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $num = '23456789';
    $simb = '!@#$%*-';
    // Variaveis internas
    $retorno = '';
    $caracteres = '';
    // Agrupamos todos os caracteres que poderão ser utilizados
    $caracteres .= $lmin;
    if ($maiusculas){ $caracteres .= $lmai; }
    if ($numeros){ $caracteres .= $num; }
    if ($simbolos){ $caracteres .= $simb; }
    // Calculamos o total de caracteres possíveis
    $len = strlen($caracteres);
    for ($n = 1; $n <= $tamanho; $n++) {
        // Criamos um número aleatório de 1 até $len para pegar um dos caracteres
        $rand = mt_rand(1, $len);
        // Concatenamos um dos caracteres na variável $retorno
        $retorno .= $caracteres[$rand - 1];
    }
    return $retorno;
}

function gerarCamposDefault($nomeObjeto){
    return '<input type="hidden" name="acao" value="incluir" />
            <input type="hidden" name="objeto" value="'. $nomeObjeto .'" />
            <input type="hidden" name="id_objeto" value="" />';
}

function gerarTokenSeguranca(){
    //if(!Sessao::existe(TOKEN) or empty(Sessao::getVariavel(TOKEN))){
        Sessao::gerarToken();
    //}
    return '<input type="hidden" name="'. TOKEN .'" value="'. Sessao::getVariavel(TOKEN) .'" />';
}
function getServer(){
    //$host = filter_input(INPUT_SERVER, 'HTTP_HOST');
    /*if($host == 'localhost'){
        return '10.52.32.125';
    }*/
    $hostname = $_SERVER['SERVER_NAME'];
    $uri = $_SERVER['REQUEST_URI'];
    $pos = strrpos($uri, '/');
    $urlBase = substr($uri, 0, $pos);
    return 'http://'. $hostname . $urlBase;
}
/**
 * O JWT eh dividifo m 3 partes separadas por ponto ".": header, payload e a signature
 * @param type $_dados
 * @return type
 */
function getTokenJWT($key = 'heC6{zN4=mFsg5n#*1#8]qX")=z:,m:R'){
    $header = array(
        'alg' => 'HS256',
        'typ' => 'JWT'
    );
    //converte em objeto
    $header = json_encode($header);
    
    //codifica em base64
    $header = base64_encode($header);
    
    //echo("Header: $header");
    //O payload eh o corpo do JWT, recebe as informacoes que precisa armazenar
    //iss - o dominio da aplicacao que gera o token
    //aud - define o dominio que pode usar o token
    //exp - data de vencimento do token
    
    //validade de 1 dias
    $exp = time() + (1 * 24 * 60 * 60);
    //validade de 1 MINUTO
    //$exp = time() + (60);
    
    $payload = array(
        'iss' => HOMEPAGE,
        'aud' => HOMEPAGE,
        'exp' => $exp        
    );
    
    //converte em objeto
    $payload = json_encode($payload);
    
    //codifica na base64
    $payload = base64_encode($payload);
    
    //echo("<br/>Payload: $payload");
    
    //O signature eh a assinatura. Pegar o header e o payload e codificar com o 
    //algoritmo sha256, junto com a chave
    $chave = $key;
    
    //gera um valor de hash com chave usando o metodo HMAC
    $signature = hash_hmac('sha256', "$header.$payload", $chave, TRUE);
    
    //codifica na base64
    $signature = base64_encode($signature);
    
    //echo("<br/>Signature: $signature");
    
    //Token
    $token = "$header.$payload.$signature";
    
    //echo("<br/>Token: $token");
    
    return $token;
}

function validateTokenJWT($tokenJWT, $key = 'heC6{zN4=mFsg5n#*1#8]qX")=z:,m:R'){
    $_token = explode('.', $tokenJWT);
    $header = $_token[0];
    $payload = $_token[1];
    $signature = $_token[2];
    
    //chave que foi utilizada para gerar o token
    $chave = $key;
    
    //usar o header e o payload e codificar com o algoritmo sha256
    $validar_assinatura = hash_hmac('sha256', "$header.$payload", $chave, TRUE);
    
    //codificar dados em base64
    $validar_assinatura = base64_encode($validar_assinatura);
    
    //comparar a assinatura do token recebido com a assinatura gerada
    if($signature == $validar_assinatura){
        //decodificar da base64
        $dados_token = base64_decode($payload);
        //converte objeto em array
        $dados_token = json_decode($dados_token, TRUE);
        //compara a data de validade do token
        if($dados_token['exp'] > time()){
            return TRUE;
        }
    }
    return FALSE;
}

function sendTokenJWTCURL($url, $tokenJWT, $_data = array()){
    //var_dump($url, $tokenJWT); die();
    if(!function_exists('curl_version')){
        throw new Exception('CURL não disponível no servidor atual.');
    }
    //inicia uma nova sessao 
    $ch = curl_init();
    
    $header = array(
        "Authorization: Bearer $tokenJWT"
    );
    //envio de cabecalho
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    
    //para esperar a resposta da URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
    if(!empty($_data)){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_data));
    }
    
    //Envia a requisicao
    curl_setopt($ch, CURLOPT_URL, $url);
    
    //fecha a sessao
    curl_close($ch);
    
    //obtem o resultado
    return curl_exec($ch);
}
//utilizado para o envio de dados para o consulta boleto
function secured_encrypt($data){
    $first_key = base64_decode(FIRSTKEY);
    $second_key = base64_decode(SECONDKEY);    

    $method = "aes-256-cbc";    
    $iv_length = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($iv_length);

    $first_encrypted = openssl_encrypt($data,$method,$first_key, OPENSSL_RAW_DATA ,$iv);    
    $second_encrypted = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);

    $output = base64_encode($iv.$second_encrypted.$first_encrypted);    
    return $output;        
}

/*function getCurl($url, $_data = NULL){
    
    if(!function_exists('curl_version')){
        throw new Exception('CURL não disponível no servidor atual.');
    }
    //inicia uma nova sessao 
    $ch = curl_init();
    
    
    
    //para esperar a resposta da URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
    //Envia a requisicao
    curl_setopt($ch, CURLOPT_URL, $url);
    
    //fecha a sessao
    curl_close($ch);
    
    //obtem o resultado
    return curl_exec($ch);
}*/

function getCURL($url, $_data = NULL) {
    try{
        if(!function_exists('curl_version')){
            throw new Exception('CURL não disponível no servidor atual.');
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        /*
        curl_setopt($curl, CURLOPT_PROXYUSERPWD, 'usuario:senha');
        curl_setopt($curl, CURLOPT_PROXY, '10.52.132.215');
        curl_setopt($curl, CURLOPT_PROXYPORT, '8080');
        curl_setopt($curl, CURLOPT_PROXYTYPE, 'HTTP');
        */

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        if(!empty($_data)){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($_data, '', '&'));
        }
        return curl_exec($curl);
    } 
    catch (Exception $ex) {
        throw new Exception($ex->getMessage());
    }
    
    //return json_decode($response, true);
    
}
function getClientIP(){
    $proxyReverso = '10.52.216.132';
    $ipaddress = '';
    if(filter_has_var(INPUT_SERVER, 'HTTP_CLIENT_IP') AND filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP') <> $proxyReverso){
        $ipaddress = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP');
    }
    else if(filter_has_var(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR') AND filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR') <> $proxyReverso){
        $ipaddress = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR');
    }
    else if(filter_has_var(INPUT_SERVER, 'HTTP_X_FORWARDED') AND filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED') <> $proxyReverso){
        $ipaddress = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED');
    }
    else if(filter_has_var(INPUT_SERVER, 'HTTP_FORWARDED_FOR') AND filter_input(INPUT_SERVER, 'HTTP_FORWARDED_FOR') <> $proxyReverso){
        $ipaddress = filter_input(INPUT_SERVER, 'HTTP_FORWARDED_FOR');
    }
    else if(filter_has_var(INPUT_SERVER, 'HTTP_FORWARDED') AND filter_input(INPUT_SERVER, 'HTTP_FORWARDED') <> $proxyReverso){
        $ipaddress = filter_input(INPUT_SERVER, 'HTTP_FORWARDED');
    }
    else if(filter_has_var(INPUT_SERVER, 'REMOTE_ADDR') AND filter_input(INPUT_SERVER, 'REMOTE_ADDR') <> $proxyReverso){
        $ipaddress = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
    }
    else{
        $ipaddress = $proxyReverso;
    }
    return $ipaddress;
}

function validarCaptcha(){
    try{
        if(!USAR_CAPTCHA OR AMBIENTE == 'DESENV'){
            return TRUE;
        }
        $captcha_data = filter_input(INPUT_POST, 'recaptcha_token');    
        $_dados = array(
            'secret' => CAPTCHA_SECRET_KEY,
            'response' => $captcha_data,
            'remoteip' => $_SERVER["REMOTE_ADDR"]
        );
        $_result = json_decode(getCURL(CAPTCHA_SITEVERIFY, $_dados), TRUE);
        //var_dump($_result); die();
        if(empty($_result) OR !$_result['success']){
            escreverArquivo(ROOT .'log'. DS .'captcha.log', json_encode($_result));
            throw new Exception('Não foi possível validar o Captcha, por favor, atualize a página e tente novamente.');
        }
        /* 
        padroes de respostas:
        SUCCESS
            array (size=5)
                'success' => boolean true
                'challenge_ts' => string '2023-06-20T14:09:25Z' (length=20)
                'hostname' => string 'localhost' (length=9)
                'score' => float 0,9
                'action' => string 'cadastro' (length=8)
        ERROR
            array (size=2)
                'success' => boolean false
                'error-codes' => 
                    array (size=1)
                        0 => string 'timeout-or-duplicate' (length=20)
        */
        /*$_erros = array(
            'missing-input-secret' => 'O parâmetro secreto está ausente.',
            'invalid-input-secret' => 'O parâmetro secreto é inválido ou está incorreto.',
            'missing-input-response' => 'O parâmetro de resposta está ausente.',
            'invalid-input-response' => 'O parâmetro de resposta é inválido ou está incorreto.',
            'bad-request' => 'A solicitação é inválida ou está incorreta.',
            'timeout-or-duplicate' => 'A resposta não é mais válida: é muito antiga ou foi usada anteriormente.'
        );*/
    } 
    catch (Exception $ex) {
        throw new Exception($ex->getMessage());
    }
}

function enviarEmailApp($destinatario, $assunto, $mensagem, $anexo = NULL, $copia = NULL, $copiaOculta = NULL){
    require_once ROOT.'/phpmailer_5_2_4/class.phpmailer.php';
    require_once ROOT.'/phpmailer_5_2_4/class.smtp.php';
    $mail = new PHPMailer(true);
    // Define a linguagem para as mensagens de erro
    $mail->setLanguage('br', ROOT.'/phpmailer_5_2_4/language/');
    // Define os dados do servidor e tipo de conexao
    $mail->SMTPDebug = 0;                                   // Ativa a saida de erros
    $mail->isSMTP();                                        // Define que a mensagem será SMTP
    $mail->Host = EMAIL_HOST;                       // Servidor de emails
    $mail->SMTPAuth = true;                                 // Define que tera autenticacao SMTP
    $mail->Username = EMAIL_USER;           // Usuario SMTP
    $mail->Password = EMAIL_PASS;                        // Senha do usuario
    $mail->SMTPSecure = EMAIL_SECURE;                              // Ativa criptografia tls ou ssl
    $mail->Port = EMAIL_PORT;                                      // Porta de conexao
    // Define o remetente
    $mail->setFrom($mail->Username, EMAIL_FROM);
    // Define os destinatário(s)
    if(is_array($destinatario)){
        if(is_array($copia)){
            foreach($destinatario as $emailDestinatario => $nomeDestinatario){
                if(empty($nomeDestinatario)){
                    $mail->addAddress($emailDestinatario);
                }
                else{
                    $mail->addAddress($emailDestinatario, $nomeDestinatario);
                }
            }
        }
        else{
            $mail->addAddress($emailDestinatario);
        }
    }
    else{
        $mail->addAddress($destinatario);
    }
    //$mail->addReplyTo('info@example.com', 'Information');  // destino de respostas
    // Define envio como copia
    if(!is_null($copia)){
        if(is_array($copia)){
            foreach($copia as $emailCC => $nomeCC){
                if(empty($nomeCC)){
                    $mail->addCC($emailCC);
                }
                else{
                    $mail->addCC($emailCC, $nomeCC);
                }
            }
        }
        else{
            $mail->addCC($copia);
        }
    }
    // Define envio como copia oculta
    if(!is_null($copiaOculta)){
        if(is_array($copiaOculta)){
            foreach($copiaOculta as $emailBCC => $nomeBCC){
                if(empty($nomeBCC)){
                    $mail->addBCC($emailBCC);
                }
                else{
                    $mail->addBCC($emailBCC, $nomeBCC);
                }
            }
        }
        else{
            $mail->addBCC($copiaOculta);
        }
    }
    // Define os anexos (opcional)
    if(!is_null($anexo)){
        if(is_array($anexo)){
            foreach($anexo as $caminhoAnexo => $nomeAnexo){
                if(empty($nomeAnexo)){
                    $mail->addAttachment($caminhoAnexo);
                }
                else{
                    $mail->addAttachment($caminhoAnexo, $nomeAnexo);
                }
            }
        }
        else{
            $mail->addAttachment($anexo);
        }
    }
    //$mail->AddAttachment(ROOT .'images'. DS .'assinatura_email.jpg');
    // Define os dados técnicos da Mensagem
    $mail->IsHTML(true);                                   // Define que o e-mail sera enviado como HTML
    //$mail->CharSet = 'iso-8859-1';                         // Charset da mensagem (opcional)
    $mail->CharSet = 'utf-8';
    // Define a mensagem (Texto e Assunto)
    $mail->Subject = $assunto;
    $mail->Body = $mensagem;// .'<img src="http://www2.fab.mil.br/sti/images/assinatura_email_novo.jpg" />';
    // Remove tags HTML da mensagem para evitar que servidores que probiem ou nao tenham suporte descartem a msg
    $mail->AltBody = strip_tags($mensagem);
    try{
        $mail->send();
        // Limpa os destinatários e os anexos
        $mail->ClearAllRecipients();
        $mail->ClearAttachments();
        return TRUE;
    } 
    catch (phpmailerException $ex) {
        throw new phpmailerException($ex->getMessage());
    }
    catch (Exception $ex){
        throw new Exception($ex->getMessage());
    }
}

function enviarEmail($destinatario, $assunto, $mensagem, $anexo = NULL, $copia = NULL, $copiaOculta = NULL){
    try{
        $gmailAPI = new GmailAPI();
        $_response = $gmailAPI->enviarEmail($destinatario, $assunto, $mensagem, $copia, $copiaOculta, $anexo);
        if($_response['tipo'] == 'success'){
            return TRUE;
        }
        throw new Exception($_response['texto']);
    } 
    catch (phpmailerException $ex) {
        throw new phpmailerException($ex->getMessage());
    }
    catch (Exception $ex){
        throw new Exception($ex->getMessage());
    }
    /*
    require_once ROOT.'/phpmailer_5_2_4/class.phpmailer.php';
    require_once ROOT.'/phpmailer_5_2_4/class.smtp.php';
    $mail = new PHPMailer(true);
    // Define a linguagem para as mensagens de erro
    $mail->setLanguage('br', ROOT.'/phpmailer_5_2_4/language/');
    // Define os dados do servidor e tipo de conexao
    $mail->SMTPDebug = 0;                                   // Ativa a saida de erros
    $mail->isSMTP();                                        // Define que a mensagem será SMTP
    $mail->Host = EMAIL_HOST;                       // Servidor de emails
    $mail->SMTPAuth = true;                                 // Define que tera autenticacao SMTP
    $mail->Username = EMAIL_USER;           // Usuario SMTP
    $mail->Password = EMAIL_PASS;                        // Senha do usuario
    $mail->SMTPSecure = EMAIL_SECURE;                              // Ativa criptografia tls ou ssl
    $mail->Port = EMAIL_PORT;                                      // Porta de conexao
    // Define o remetente
    $mail->setFrom($mail->Username, EMAIL_FROM);
    // Define os destinatário(s)
    if(is_array($destinatario)){
        if(is_array($copia)){
            foreach($destinatario as $emailDestinatario => $nomeDestinatario){
                if(empty($nomeDestinatario)){
                    $mail->addAddress($emailDestinatario);
                }
                else{
                    $mail->addAddress($emailDestinatario, $nomeDestinatario);
                }
            }
        }
        else{
            $mail->addAddress($emailDestinatario);
        }
    }
    else{
        $mail->addAddress($destinatario);
    }
    //$mail->addReplyTo('info@example.com', 'Information');  // destino de respostas
    // Define envio como copia
    if(!is_null($copia)){
        if(is_array($copia)){
            foreach($copia as $emailCC => $nomeCC){
                if(empty($nomeCC)){
                    $mail->addCC($emailCC);
                }
                else{
                    $mail->addCC($emailCC, $nomeCC);
                }
            }
        }
        else{
            $mail->addCC($copia);
        }
    }
    // Define envio como copia oculta
    if(!is_null($copiaOculta)){
        if(is_array($copiaOculta)){
            foreach($copiaOculta as $emailBCC => $nomeBCC){
                if(empty($nomeBCC)){
                    $mail->addBCC($emailBCC);
                }
                else{
                    $mail->addBCC($emailBCC, $nomeBCC);
                }
            }
        }
        else{
            $mail->addBCC($copiaOculta);
        }
    }
    // Define os anexos (opcional)
    if(!is_null($anexo)){
        if(is_array($anexo)){
            foreach($anexo as $caminhoAnexo => $nomeAnexo){
                if(empty($nomeAnexo)){
                    $mail->addAttachment($caminhoAnexo);
                }
                else{
                    $mail->addAttachment($caminhoAnexo, $nomeAnexo);
                }
            }
        }
        else{
            $mail->addAttachment($anexo);
        }
    }
    //$mail->AddAttachment(ROOT .'images'. DS .'assinatura_email.jpg');
    // Define os dados técnicos da Mensagem
    $mail->IsHTML(true);                                   // Define que o e-mail sera enviado como HTML
    //$mail->CharSet = 'iso-8859-1';                         // Charset da mensagem (opcional)
    $mail->CharSet = 'utf-8';
    // Define a mensagem (Texto e Assunto)
    $mail->Subject = $assunto;
    $mail->Body = $mensagem;// .'<img src="http://www2.fab.mil.br/sti/images/assinatura_email_novo.jpg" />';
    // Remove tags HTML da mensagem para evitar que servidores que probiem ou nao tenham suporte descartem a msg
    $mail->AltBody = strip_tags($mensagem);
    try{
        $mail->send();
        // Limpa os destinatários e os anexos
        $mail->ClearAllRecipients();
        $mail->ClearAttachments();
        return TRUE;
    } 
    catch (phpmailerException $ex) {
        throw new phpmailerException($ex->getMessage());
    }
    catch (Exception $ex){
        throw new Exception($ex->getMessage());
    }*/
}
function getServerIP(){
    $host = filter_input(INPUT_SERVER, 'HTTP_HOST');
    if($host == 'localhost'){
        $host .= '/csa-secretaria';
    }
    else if(AMBIENTE == 'PRODUCAO'){
        $host .= '/infragit';
    }
    return $host;
}
/**
 * Abrevia uma string adicionando reticências no seu final
 * @param String $string texto a ser abreviado
 * @param Integer $max_caracteres total de caracteres que se deseja exibir
 * @return String retorna o texto abreviado desde que seu total de caracteres exceda o numero maximo de caracteres informado no segundo parametro
 */
function abreviar_string($string, $max_caracteres) {
    if (strlen($string) > $max_caracteres) {
        $string = substr($string, 0, $max_caracteres);
        #para remover espacos em branco do final da string jah abreviada
        while (substr($string, -1) == ' ') {
            $string = substr($string, 0, -1);
        }
        $string .= '...';
    }
    return $string;
}

function traduzirCodificacao($string){
    $_replace = array('Ç', 'Í', 'Ã', 'Ê', 'Ú', 'Ç', 'Ó', 'Õ', 'Ö', 'Ê', 'Ó', 'Ã');
    $_search = array('ÃƒÂ‡', 'Ã', 'Ãƒ', 'ÃŠ', 'Ãš', 'Ã‡', 'Ã“', 'Ã•', 'Ã–', 'ÃŠ', 'Ã“', 'Ãƒ');
    return str_replace($_search, $_replace, $string);
}

function substituirCaracteresEspeciais($string){
    $caracteres_sem_acento = array(
        'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Â'=>'Z', 'Â'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
        'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
        'Ï'=>'I', 'Ñ'=>'N', 'Å'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
        'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
        'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
        'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'Å'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
        'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
        'Ä'=>'a', 'î'=>'i', 'â'=>'a', 'È'=>'s', 'È'=>'t', 'Ä'=>'A', 'Î'=>'I', 'Â'=>'A', 'È'=>'S', 'È'=>'T'
    );
    return strtr($string, $caracteres_sem_acento);
}
/**
 * Substitui caracteres especiais de uma string
 * @param String $str string a ter seus caracteres especiais removidos
 * @return String retorna a string com os caracteres especiais substituídos
 */
function substituir_caracteres_especiais($str) {
    $str = preg_replace('/[áàãâä]/ui', 'a', $str);
    $str = preg_replace('/[éèêë]/ui', 'e', $str);
    $str = preg_replace('/[íìîï]/ui', 'i', $str);
    $str = preg_replace('/[óòõôö]/ui', 'o', $str);
    $str = preg_replace('/[úùûü]/ui', 'u', $str);
    $str = preg_replace('/[ç]/ui', 'c', $str);
    // $str = preg_replace('/[,(),;:|!"#$%&/=?~^><ªº-]/', '_', $str);
    $str = preg_replace('/[^a-z0-9]/i', '_', $str);
    $str = preg_replace('/_+/', '_', $str); // ideia do Bacco :)
    return $str;
}
/**
 * Gera nova URL sempre, evitando cache
 */
function nocache($filename) {
    return "{$filename}?cache=" . str_replace(array(' ', '.'), array('', ''), microtime());
}
/**
 * Gera nova URL baseada na data de modificação, evitando cache de arquivos antigos
 */
function nocached_mfile($filename) {
    if(empty($filename) OR !file_exists($filename)){
        return $filename;
    }
    return "{$filename}?cache=" . date("dmYHis", filemtime($filename));
}

function gerarXML($_dados, $nr_pagina, $nr_registros_por_pagina){
    $xml = '<resultado>';
    $xml .= '<nr_registros_total>'. $_dados['total'] .'</nr_registros_total>';
    $xml .= '<nr_registros_na_pagina>'. count($_dados['registros']) .'</nr_registros_na_pagina>';
    $xml .= '<nr_registros_por_pagina>'. $nr_registros_por_pagina .'</nr_registros_por_pagina>';
    $xml .= '<nr_pagina>'. $nr_pagina .'</nr_pagina>';
    $xml .= '<objetos>';
    foreach($_dados['registros'] as $al){
        $xml .= '<objeto>';
        foreach($al as $propriedade => $valor){
            if(!is_array($valor) and !is_object($valor)){
                $xml .= "<$propriedade>$valor</$propriedade>";
            }
        }
        $xml .= '</objeto>';
    }
    $xml .= '</objetos>';
    $xml .= '</resultado>';
    return $xml;
}

function criptografar($string){
    return md5($string . SAL_SENHA);
}

function dataAtualPorExtenso(){
    $dia = date('j');
    $mes = date('n');
    $ano = date('Y');
    $semana = date('w');
    $_mes = array('', "janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro");
    $_semana = array("domingo", "segunda-feira", "terça-feira", "quarta-feira", "quinta-feira", "sexta-feira", "sábado");
    return "{$_semana[$semana]}, $dia de {$_mes[$mes]} de $ano";
}

function dataAtualPorExtensoDiaMesAno(){
    $dia = date('j');
    $mes = date('n');
    $ano = date('Y');
    $_mes = array('', "janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro");
    return "$dia de {$_mes[$mes]} de $ano";
}
function getDirBaseVitual(){
    $_dirbase = explode(DS, ROOT);
    for($i = count($_dirbase)-1; $i > 0; $i--){
        if($_dirbase[$i] != ''){
            return $_dirbase[$i];
        }
    }
}
function getUrlBase(){
    $hostname = $_SERVER['SERVER_NAME'];
    $uri = $_SERVER['REQUEST_URI'];
    $pos = strrpos($uri, '/');
    $urlBase = substr($uri, 0, $pos);
    return 'http://'. $hostname . $urlBase .'/';
}
function valorPorExtenso($valor = 0, $maiusculas = false) {
    if(!$maiusculas){
        $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
        $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões");
        $u = array("", "um", "dois", "três", "quatro", "cinco", "seis",  "sete", "oito", "nove");
    }else{
        $singular = array("CENTAVO", "REAL", "MIL", "MILHÃO", "BILHÃO", "TRILHÃO", "QUADRILHÃO");
        $plural = array("CENTAVOS", "REAIS", "MIL", "MILHÕES", "BILHÕES", "TRILHÕES", "QUADRILHÕES");
        $u = array("", "UM", "DOIS", "TRÊS", "QUATRO", "CINCO", "SEIS",  "SETE", "OITO", "NOVE");
    }

    $c = array("", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
    $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa");
    $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezesete", "dezoito", "dezenove");

    $z = 0;
    $rt = "";

    $valor = number_format($valor, 2, ".", ".");
    $inteiro = explode(".", $valor);
    for($i=0;$i<count($inteiro);$i++)
    for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
    $inteiro[$i] = "0".$inteiro[$i];

    $fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
    for ($i=0;$i<count($inteiro);$i++) {
        $valor = $inteiro[$i];
        $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
        $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
        $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

        $r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd &&
        $ru) ? " e " : "").$ru;
        $t = count($inteiro)-1-$i;
        $r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
        if ($valor == "000")$z++; elseif ($z > 0) $z--;
        if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
        if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
    }

    if(!$maiusculas){
        $return = $rt ? $rt : "zero";
    } else {
        if ($rt) $rt = str_replace(" E "," e ",ucwords($rt));
            $return = ($rt) ? ($rt) : "Zero" ;
    }

    if(!$maiusculas){
        return str_replace(" E "," e ",ucwords($return));
    }else{
        return strtoupper($return);
    }
}

function filtrarPesquisaPost($obj){
    if(filter_has_var(INPUT_POST, 'filtro')){
        $_filtro = $_POST['filtro'];
        foreach($_filtro as $tipoFiltro => $_parametro){
            $keys = array_keys($_parametro);
            $campo = $keys[0];
            $valor = $_parametro[$campo];                
            $obj->filtrar($campo, $valor, $tipoFiltro);
        }
    }
}

function registrarLog($titulo, $corpo, $filesource = ARQUIVO_LOG){
    $quebra = PHP_EOL;
    $log =  '['. date('d/m/Y H:i:s') .'] '. $titulo;
    $log .= $quebra;
    $log .= $corpo;
    $log .= $quebra;
    $strfim = '**************************************';
    $log .= $strfim . $strfim . $strfim;
    $log .= $quebra;
    $file = fopen($filesource, 'a+');
    fwrite($file, $log);
    fclose($file);
}

function getScriptName(){
    return $_SERVER['REQUEST_URI'];
}

function escreverArquivo($arquivo, $conteudo){
    //Variável $fp armazena a conexão com o arquivo e o tipo de ação.
    $fp = fopen($arquivo, "w+");
    //Escreve no arquivo aberto.
    fwrite($fp, $conteudo);
    //Fecha o arquivo.
    fclose($fp);
}

function lerArquivo($arquivo){
    //Variável $fp armazena a conexão com o arquivo e o tipo de ação.
    $fp = fopen($arquivo, "r");
    //Lê o conteúdo do arquivo aberto.
    $conteudo = fread($fp, filesize($arquivo));
    //Fecha o arquivo.
    fclose($fp);
    //retorna o conteúdo.
    return $conteudo;
}
/**
 * 
 * @param type $nrMes numero do mês
 * @return string
 */
function getNomeMes($nrMes, $nrChars = NULL){
    $_meses = array(
        '01' => 'janeiro',
        '02' => 'fevereiro',
        '03' => 'março',
        '04' => 'abril',
        '05' => 'maio',
        '06' => 'junho',
        '07' => 'julho',
        '08' => 'agosto',
        '09' => 'setembro',
        '10' => 'outubro',
        '11' => 'novembro',
        '12' => 'dezembro',
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro'
    );
    if(array_key_exists($nrMes, $_meses)){
        if($nrChars){
            return substr($_meses[$nrMes], 0, $nrChars);
        }
        return $_meses[$nrMes];
    }
    return '';
}

function jsonUnescapedUnicode($json){
    $encoded = json_encode($json);
    $unescaped = preg_replace_callback('/\\\u(\w{4})/', function ($matches) {
        return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
    }, $encoded);
    return $unescaped;
}

function gerarDadosMockingSessao($matricula){
    if(empty($matricula)){
        return;
    }
    $sql = "SELECT cpfcontra FROM Alunos WHERE matricula = ?";
    $_params = array($matricula);
    
    $_result = Dao::select($sql, $_params);
    
    if(count($_result) == 0){
        die("Matricula $matricula nao encontrada para realizar a simulacao");
    }
    
    $cpf_contratante = $_result[0]['cpfcontra'];
    
    $sql_SO = "SELECT * FROM SO_usuario WHERE cpfUsuario = ?";
    $_params_SO = array($cpf_contratante);
    $_result_SO = Dao::select($sql_SO, $_params_SO);

    if(count($_result_SO) == 0){
        $_result_SO = array(
            array(
                'tipoUsuario' => NULL,
                'nomeUsuario' => NULL,
                'emailUsuario' => NULL,
                'emailAdicionalUsuario' => NULL,
                'aceitouTermo' => NULL,
                'jaViuNovidades' => NULL,
                'jaViuMensagemAlunoNovo' => NULL,
                'senhaBloqueada' => NULL,
                'preferencia_timeout' => NULL,
                'preferencia_icone' => NULL,
                'preferencia_wallpaper' => NULL,
                'preferencia_cor' => NULL,
                'emailAdicionalUsuario' => NULL,
                'preferencia_recebeCircularesEmailAdicional' => NULL,
                'preferencia_recebeOcorrenciasEmailPrincipal' => NULL,
                'preferencia_recebeOcorrenciasEmailAdicional' => NULL
            )
        );
    }
    $usuario = $_result_SO[0];
   
    $_SESSION["SO_acessoAdministrativo"] = true; 
    $_SESSION["SO_alunoAtual"] = $matricula;
    $_SESSION["SO_mostrarTurma"] = true;
    $_SESSION["SO_cpfUsuario"] = $cpf_contratante;
    $_SESSION["SO_tipoUsuario"]	= $usuario["tipoUsuario"];
    $_SESSION["SO_nomeUsuario"] = $usuario["nomeUsuario"];
    $_SESSION["SO_emailUsuario"] = $usuario["emailUsuario"];
    $_SESSION["SO_emailAdicionalUsuario"] = $usuario["emailAdicionalUsuario"];
    $_SESSION["SO_inicioSessao"] = date('d/m/Y') . ' &agrave;s ' . date("H:i:s");
    $_SESSION["SO_datahoraInicioSessao"] = strtotime("now"); // formato UNIX
    $_SESSION["SO_aceitouTermo"] = $usuario["aceitouTermo"];
    $_SESSION["SO_jaViuNovidades"] = $usuario["jaViuNovidades"];
    $_SESSION["SO_jaViuMensagemAlunoNovo"] = $usuario["jaViuMensagemAlunoNovo"];
    $_SESSION["SO_senhaBloqueada"] = $usuario["senhaBloqueada"];
    //$_SESSION["SO_ultimoAcessoData"] = $acesso["datahora"];
    $_SESSION["SO_ultimoAcessoLocal"] = '';
    // preferencias do usuario
    $_SESSION["SO_preferencia"]["timeout"] = $usuario["preferencia_timeout"];
    $_SESSION["SO_preferencia"]["icone"] = $usuario["preferencia_icone"];
    $_SESSION["SO_preferencia"]["wallpaper"] = $usuario["preferencia_wallpaper"];
    $_SESSION["SO_preferencia"]["cor"] = $usuario["preferencia_cor"];
    $_SESSION["SO_preferencia"]["emailAdicional"] = $usuario["emailAdicionalUsuario"];
    $_SESSION["SO_preferencia"]["recebeCircularesEmailAdicional"] = $usuario["preferencia_recebeCircularesEmailAdicional"];
    $_SESSION["SO_preferencia"]["recebeOcorrenciasEmailPrincipal"] = $usuario["preferencia_recebeOcorrenciasEmailPrincipal"];
    $_SESSION["SO_preferencia"]["recebeOcorrenciasEmailAdicional"] = $usuario["preferencia_recebeOcorrenciasEmailAdicional"];
    $_SESSION["SO_acessoEnvelopeMatricula"] = true;
    $_SESSION["SO_acessoAgendamento"] = true;
    
    //dados dos alunos associados ao usuario
    $sql_alunos = "SELECT Alunos.matricula, Alunos.cpfcontra, Alunos.nomaluno, Alunos.dtnaluno, Alunos.sexaluno,
                          Alunos.grau, Alunos.serie, Alunos.turma, Alunos.numero, Alunos.email, Alunos.situa__o,
			  Alunos_MHS.situa__o AS mhs_situacao, Alunos_MHS.cpfcontra AS mhs_cpfcontra, 
                          Alunos_MHS.nomcontra AS mhs_nomcontra, IF(Alunos.turma='Z' AND Alunos.numero='99', 'S', 'N') AS aluno_novo 
                   FROM Alunos 
                   LEFT JOIN Alunos_MHS ON Alunos.matricula = Alunos_MHS.matricula 					
                   WHERE (							
                        Alunos.cpfcontra = ? OR 
			Alunos.cpfpaalu = ?  OR 
			Alunos.cpfmaalu = ?
                    )
                    AND (
                        (Alunos.dtsa_da IS NULL) AND
			(Alunos.numero <> '') AND
			(Alunos.numero IS NOT NULL) AND 
                        (Alunos.turma IS NOT NULL) AND 
                        (Alunos.turma <> '')
                    )
                    ORDER BY Alunos.dtnaluno DESC";
    
    $_params_alunos = array($cpf_contratante, $cpf_contratante, $cpf_contratante);
    $aluno = Dao::select($sql_alunos, $_params_alunos);
    
    if(count($aluno) == 0){
        die('Aluno nao encontrado para realizar a simulacao');
    }
    
    for ($i = 0; $i < count($aluno); $i++) {
        $_SESSION["SO_aluno"][$aluno[$i]["matricula"]] = array(
            'contratante' => $aluno[$i]["cpfcontra"],
            'matricula' => $aluno[$i]["matricula"],
            'nomaluno' => utf8_encode($aluno[$i]["nomaluno"]),
            'grau' => $aluno[$i]["grau"],
            'serie' => $aluno[$i]["serie"],
            'turma' => $aluno[$i]["turma"],
            'numero' => $aluno[$i]["numero"],
            'dtnaluno' => $aluno[$i]["dtnaluno"],
            'sexaluno' => $aluno[$i]["sexaluno"],
            'concordancia' => (($aluno[$i]["sexaluno"] == 'F') ? 'a' : 'o'),
            'email' => $aluno[$i]["email"],
            'mhs_situacao' => $aluno[$i]["mhs_situacao"],
            'mhs_cpfcontra' => $aluno[$i]["mhs_cpfcontra"],
            'mhs_nomcontra' => $aluno[$i]["mhs_nomcontra"],
            'aluno_novo' => $aluno[$i]["aluno_novo"]
        );
    }
}

function debug($var){
    print('<pre>'. date('H:i:s') .' - ');
    print_r($var);
    print('</pre>');
}
function printDebug($var){
    $now = DateTime::createFromFormat('U.u', microtime(true));
    if(defined('PRINT_DEBUG') && PRINT_DEBUG){
        if(!is_string($var)){
            print('<pre>'. $now->format("H:i:s.u") .' - ');
            print_r($var);
            print('</pre>');
        }
        else{
            echo("<pre>".$now->format("H:i:s.u") ." - {$var}</pre>");
        }
    }
}
function varDumpDebug($variavel){
    $debug = TRUE;
    $meuip = '170.254.200.49';
    if($debug && $_SERVER['REMOTE_ADDR'] == $meuip){
        print('<pre>');
        print_r($variavel);
        print('</pre>');
    }
}

function corrigirCaracteresCodificacaoNomeMateria($seqmat, $nomemat){
    $_materias = array(
        '04' => 'LÍNGUA PORTUGUESA',
        '99' => 'ED FÍSICA',
        '52' => 'MATEMÁTICA',
        '58' => 'CIÊNCIAS NATURAIS',
        '22' => 'HISTÓRIA',
        /*'64' => 'INFORMÁTICA',*/
        '28' => 'FÍSICA',
        '31' => 'QUÍMICA',
        '73' => 'PRODUÇÃO DE TEXTO',
        '96' => 'REDAÇÃO',
        '13' => 'LÍNGUA INGLESA',
        '14' => 'LÍNGUA ESPANHOLA'
    );
    if(array_key_exists($seqmat, $_materias)){
        $nomemat = $_materias[$seqmat];
    }
    return $nomemat;
}

function ifEmpty($valor, $valorAlternativo){
    if(empty($valor)){
        return $valorAlternativo;
    }
    return $valor;
}

function substituirCaracteres($string){
    $_search = array('ç');
    $_replace = array('&Ccedil;');
    return str_replace($_search, $_replace, $string);
}

function dataPorExtenso($data){
    $dia = date('j', strtotime($data));
    $mes = date('n', strtotime($data));
    $ano = date('Y', strtotime($data));
    $semana = date('w', strtotime($data));
    $_mes = array('', "janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro");
    $_semana = array("domingo", "segunda-feira", "terça-feira", "quarta-feira", "quinta-feira", "sexta-feira", "sábado");
    return "{$_semana[$semana]}, $dia de {$_mes[$mes]} de $ano";
}

function nomeMes($nr){
    $_mes = array('', "janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro");
    return $_mes[$nr];
}
function nomeSemana($nr){
    $_semana = array("domingo", "segunda-feira", "terça-feira", "quarta-feira", "quinta-feira", "sexta-feira", "sábado");
    return $_semana[$nr];
}
//funcao auxiliar da funcao ordenarArrayObjetos
function cmp($a, $b){
    global $prop;
    if(is_object($a)){
        return strcmp($a->$prop, $b->$prop);
    }
    if(is_array($a)){
        return strcmp($a[$prop], $b[$prop]);
    }
} 
/**
 * 
 * @global type $prop
 * @param type $_array
 * @param type $propriedade
 */
function ordenarArrayObjetos(&$_array, $propriedade){
    global $prop;
    $prop = $propriedade;
    usort($_array, 'cmp');
}

function isSenhaValida($senha) {
    #var_dump(preg_match('/[a-z]/', $senha));
    if(!preg_match('/[a-z]/', $senha)){
        return FALSE;
    }
    #var_dump(preg_match('/[A-Z]/', $senha));
    if(!preg_match('/[A-Z]/', $senha)){
        return FALSE;
    }
    #var_dump(preg_match('/[0-9]/', $senha));
    if(!preg_match('/[0-9]/', $senha)){
        return FALSE;
    }
    #var_dump(strlen($senha));
    if(strlen($senha) < 9){
        return FALSE;
    }
    return TRUE;
}

function openEncryptSSL($string){
    $algoritmo = 'aes-128-cbc';
    $chave = 'Yr642&K$oz*y';
    $iv = 'wNYtCnelXfOa6uiJ';
    $result = openssl_encrypt($string, $algoritmo, $chave, OPENSSL_RAW_DATA, $iv);
    return base64_encode($result); //codificada em base64 para conseguirmos enviá-la em transtornos
}
function openDecryptSSL($string){
    $algoritmo = 'aes-128-cbc';
    $chave = 'Yr642&K$oz*y';
    $iv = 'wNYtCnelXfOa6uiJ';
    return openssl_decrypt(base64_decode($string), $algoritmo, $chave, OPENSSL_RAW_DATA, $iv);
}
function getParteEmail($email){
    $domain = strstr($email, '@');
    $user = strstr($email, '@', true);
    $total = strlen($user);
    $totalExibir = (int)($total / 2);
    $saida = substr($user, 0, $totalExibir);
    $saida .= '*****';
    $saida .= $domain;
    return $saida;
}
function getPrimeiraUltimaString($texto){
    $_nome = explode(' ', $texto);
    return $_nome[0] .' '. $_nome[count($_nome)-1];
}
function selectOption($option_value, $option_target){
    if($option_value == $option_target){
        return 'selected=""';
    }
    return '';
}
function getValueArrayKey($_array, $key){
    if(array_key_exists($key, $_array)){
        return $_array[$key];
    }
    return '';
}
function cryptMatriculaCPF($cpf, $matricula){
    $string = strrev($cpf . $matricula);
    //inverter
    //substituir números, pares maiúsculos e ímpares mnúsculos: 
    //0: O; 3: m; 8: B; 1: i; 9: g
    $_search = array(0, 3, 8, 1, 9, 7);
    $_replace = array('O', 'm', 'B', 'i', 'g', 'l');
    
    $string = str_replace($_search, $_replace, $string);
    
    return base64_encode($string);
}
function decryptMatriculaCPF($string){
    //inverter
    //substituir números, pares maiúsculos e ímpares mnúsculos: 
    //0: O; 3: m; 8: B; 1: i; 9: g
    $_replace = array(0, 3, 8, 1, 9, 7);
    $_search = array('O', 'm', 'B', 'i', 'g', 'l');
    $string = base64_decode($string);
    $string = str_replace($_search, $_replace, $string);
    return strrev($string);
}

/**
* O metodo responsavel por descriptograr uma mensagem
* @param string $message Mensagem criptografada
* @param string $key Chave para realizar a descriptografia precisa ser a mesma usada na criptografia Exemplo: "skjj400ndkdçg00"
* @param string $mac_algorithm Tipo da descriptografia que sera usada Exemplo: md5 e sha1
*/
function decrypt($message, $key, $mac_algorithm = 'sha1', $enc_algorithm = MCRYPT_RIJNDAEL_256, $enc_mode = MCRYPT_MODE_CBC)
{
    $message= base64_decode($message);
    $iv_size = mcrypt_get_iv_size($enc_algorithm, $enc_mode);
    $iv_dec = substr($message, 0, $iv_size);
    $message= substr($message, $iv_size);
    $message= mcrypt_decrypt($enc_algorithm, $key, $message, $enc_mode, $iv_dec);
    $mac_block_size = ceil(160/8);
    $mac_dec = substr($message, 0, $mac_block_size);
    $message= substr($message, $mac_block_size);

    $mac = hash_hmac($mac_algorithm, $message, $key, true);

    if($mac_dec == $mac)
    {
        return $password;
    }
    else
    {
        return false;
    }
}

/**
* O metodo responsavel por criptofrafar uma mensagem
* @param string $message Mensagem a ser criptograda
* @param string $key Chave para realizar a criptografia Exemplo: "skjj400ndkdçg00"
* @param string $mac_algorithm Tipo da criptograda que sera usada Exemplo: md5 e sha1
*/
function encrypt($message, $key, $mac_algorithm = 'sha1', $enc_algorithm = MCRYPT_RIJNDAEL_256, $enc_mode = MCRYPT_MODE_CBC){
    $mac = hash_hmac($mac_algorithm, $message, $key, true);
    $mac = substr($mac, 0, ceil(160/8));
    $message= $mac . $message;
    $iv_size = mcrypt_get_iv_size($enc_algorithm, $enc_mode);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $ciphertext = mcrypt_encrypt($enc_algorithm, $key,
                                 $message, $enc_mode, $iv);
    return base64_encode($iv . $ciphertext);
}

function getTiposSanguineos(){
    return array('O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-');
}

function getEstados(){
    $_estados = json_decode('[{"id":11,"sigla":"RO","nome":"Rondônia","regiao":{"id":1,"sigla":"N","nome":"Norte"}},{"id":12,"sigla":"AC","nome":"Acre","regiao":{"id":1,"sigla":"N","nome":"Norte"}},{"id":13,"sigla":"AM","nome":"Amazonas","regiao":{"id":1,"sigla":"N","nome":"Norte"}},{"id":14,"sigla":"RR","nome":"Roraima","regiao":{"id":1,"sigla":"N","nome":"Norte"}},{"id":15,"sigla":"PA","nome":"Pará","regiao":{"id":1,"sigla":"N","nome":"Norte"}},{"id":16,"sigla":"AP","nome":"Amapá","regiao":{"id":1,"sigla":"N","nome":"Norte"}},{"id":17,"sigla":"TO","nome":"Tocantins","regiao":{"id":1,"sigla":"N","nome":"Norte"}},{"id":21,"sigla":"MA","nome":"Maranhão","regiao":{"id":2,"sigla":"NE","nome":"Nordeste"}},{"id":22,"sigla":"PI","nome":"Piauí","regiao":{"id":2,"sigla":"NE","nome":"Nordeste"}},{"id":23,"sigla":"CE","nome":"Ceará","regiao":{"id":2,"sigla":"NE","nome":"Nordeste"}},{"id":24,"sigla":"RN","nome":"Rio Grande do Norte","regiao":{"id":2,"sigla":"NE","nome":"Nordeste"}},{"id":25,"sigla":"PB","nome":"Paraíba","regiao":{"id":2,"sigla":"NE","nome":"Nordeste"}},{"id":26,"sigla":"PE","nome":"Pernambuco","regiao":{"id":2,"sigla":"NE","nome":"Nordeste"}},{"id":27,"sigla":"AL","nome":"Alagoas","regiao":{"id":2,"sigla":"NE","nome":"Nordeste"}},{"id":28,"sigla":"SE","nome":"Sergipe","regiao":{"id":2,"sigla":"NE","nome":"Nordeste"}},{"id":29,"sigla":"BA","nome":"Bahia","regiao":{"id":2,"sigla":"NE","nome":"Nordeste"}},{"id":31,"sigla":"MG","nome":"Minas Gerais","regiao":{"id":3,"sigla":"SE","nome":"Sudeste"}},{"id":32,"sigla":"ES","nome":"Espírito Santo","regiao":{"id":3,"sigla":"SE","nome":"Sudeste"}},{"id":33,"sigla":"RJ","nome":"Rio de Janeiro","regiao":{"id":3,"sigla":"SE","nome":"Sudeste"}},{"id":35,"sigla":"SP","nome":"São Paulo","regiao":{"id":3,"sigla":"SE","nome":"Sudeste"}},{"id":41,"sigla":"PR","nome":"Paraná","regiao":{"id":4,"sigla":"S","nome":"Sul"}},{"id":42,"sigla":"SC","nome":"Santa Catarina","regiao":{"id":4,"sigla":"S","nome":"Sul"}},{"id":43,"sigla":"RS","nome":"Rio Grande do Sul","regiao":{"id":4,"sigla":"S","nome":"Sul"}},{"id":50,"sigla":"MS","nome":"Mato Grosso do Sul","regiao":{"id":5,"sigla":"CO","nome":"Centro-Oeste"}},{"id":51,"sigla":"MT","nome":"Mato Grosso","regiao":{"id":5,"sigla":"CO","nome":"Centro-Oeste"}},{"id":52,"sigla":"GO","nome":"Goiás","regiao":{"id":5,"sigla":"CO","nome":"Centro-Oeste"}},{"id":53,"sigla":"DF","nome":"Distrito Federal","regiao":{"id":5,"sigla":"CO","nome":"Centro-Oeste"}}]');
    ordenarArrayObjetos($_estados, 'sigla');
    return $_estados;
}

function consultaCep($str_cep){
    try{
        $cep = preg_replace('/[^0-9]/', '', $str_cep);
        $url = "http://viacep.com.br/ws/$cep/json/";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $viacep = curl_exec($curl);
        $viacep_data = json_decode($viacep, TRUE);
        return $viacep_data;
    } 
    catch (Exception $ex) {
        return $ex->getMessage();
    }
}

function getEtnias(){
    $_saida = array(
        '0' => 'Branca',
        '1' => 'Preta',
        '2' => 'Parda',
        '3' => 'Amarela',
        '4' => 'Ind&iacute;gena'
    );
    return $_saida;
}

function formatarTelefone($entrada){
    if(empty($entrada)){
        return NULL;
    }
    $telefone = removerMascara($entrada);
    if(strlen($telefone) == 11){
        $saida = mascarar($telefone, '(##) #####-####');
    }
    else if(strlen($telefone) == 10){
        $saida = mascarar($telefone, '(##) ####-####');
    }
    else{
        $saida = $entrada;
    }
    return $saida;
}

function corrigirCodificacaoCaracteres($string){
    $_search = array(
        'Ã©', 'Ã§', 'Ã£', 'Ãµ', 'Ã¡', 'Ã“', 'Ã‡', 'Ãƒ', 'Ã‰', 'Ã•', 'Ã³', '&#730;', 'Ãº', 'Ãª', 'Ã¡', 'Ã©', 'Âª', 'Ãª', 'â€“', 'Ã‚', 'Ãª', 'ãÂ', 'í³', 'í©', 'í§', 'í£', 'ÃÂ§', 'ÃÂ£', 'ÃÂ³'
    );
    $_replace = array(
        'é', 'ç', 'ã', 'õ', 'á', 'ó', 'ç', 'ã', 'é', 'õ', 'ó', 'º', 'ú', 'ê', 'á', 'é', 'ª', 'ê', '-', 'â', 'ê', 'í', 'ó', 'é', 'ç', 'ã', 'ç', 'ã', 'ó'
    );
    return str_replace($_search, $_replace, $string);
}
//estas duas funções de criptografia e descriptografia aparentemente são as unicas que estão funcionando
function criptografarDados($string, $chave = 'j>5i18KN1wS4'){
    $algoritmo = "AES-256-CBC";
    $iv = "wNYtCnelXfOa6uiJ";
    $mensagem = openssl_encrypt($string, $algoritmo, $chave, OPENSSL_RAW_DATA, $iv);
    return base64_encode($mensagem);
}
function descriptografarDados($string, $chave = 'j>5i18KN1wS4'){
    $algoritimo = "AES-256-CBC";
    $iv = "wNYtCnelXfOa6uiJ";
    $mensagem = base64_decode($string);
    return openssl_decrypt($mensagem, $algoritimo, $chave, OPENSSL_RAW_DATA, $iv);
}
/**
 * Função que converte caracteres ISO-8859-1 para UTF-8, mantendo os caracteres UTF-8 intactos.
 * @param string $texto
 * @return string
 */
function sanitizar_utf8($texto) {
    $saida = '';

    $i = 0;
    $len = strlen($texto);
    while ($i < $len) {
        $char = $texto[$i++];
        $ord  = ord($char);

        // Primeiro byte 0xxxxxxx: simbolo ascii possui 1 byte
        if (($ord & 0x80) == 0x00) {

            // Se e' um caractere de controle
            if (($ord >= 0 && $ord <= 31) || $ord == 127) {

                // Incluir se for: tab, retorno de carro ou quebra de linha
                if ($ord == 9 || $ord == 10 || $ord == 13) {
                    $saida .= $char;
                }

            // Simbolo ASCII
            } else {
                $saida .= $char;
            }

        // Primeiro byte 110xxxxx ou 1110xxxx ou 11110xxx: simbolo possui 2, 3 ou 4 bytes
        } else {

            // Determinar quantidade de bytes analisando os bits da esquerda para direita
            $bytes = 0;
            for ($b = 7; $b >= 0; $b--) {
                $bit = $ord & (1 << $b);
                if ($bit) {
                    $bytes += 1;
                } else {
                    break;
                }
            }

            switch ($bytes) {
            case 2: // 110xxxxx 10xxxxxx
            case 3: // 1110xxxx 10xxxxxx 10xxxxxx
            case 4: // 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
                $valido = true;
                $saida_padrao = $char;
                $i_inicial = $i;
                for ($b = 1; $b < $bytes; $b++) {
                    if (!isset($texto[$i])) {
                        $valido = false;
                        break;
                    }
                    $char_extra = $texto[$i++];
                    $ord_extra  = ord($char_extra);

                    if (($ord_extra & 0xC0) == 0x80) {
                        $saida_padrao .= $char_extra;
                    } else {
                        $valido = false;
                        break;
                    }
                }
                if ($valido) {
                    $saida .= $saida_padrao;
                } else {
                    $saida .= ($ord < 0x7F || $ord > 0x9F) ? utf8_encode($char) : '';
                    $i = $i_inicial;
                }
                break;
            case 1:  // 10xxxxxx: ISO-8859-1
            default: // 11111xxx: ISO-8859-1
                $saida .= ($ord < 0x7F || $ord > 0x9F) ? utf8_encode($char) : '';
                break;
            }
        }
    }
    return $saida;
}

function cpfValido($cpf) {
    // Extrai somente os números
    $cpf = preg_replace( '/[^0-9]/is', '', $cpf );
    // Verifica se foi informado todos os digitos corretamente
    if (strlen($cpf) != 11) {
        return false;
    }
    // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    // Faz o calculo para validar o CPF
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

function getImageBase64($path){
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    return 'data:image'. $type .';base64,'. base64_encode($data);
}
